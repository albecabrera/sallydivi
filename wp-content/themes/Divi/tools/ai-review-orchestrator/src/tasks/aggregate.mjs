import path from "node:path";

import { task } from "@langchain/langgraph";

import { readJson, writeJson, writeText } from "../core/io.mjs";
import { log } from "../core/logging.mjs";
import { loadReviewerDefinitions } from "../reviewers/loaders.mjs";
import {
  buildFindingKey,
  buildInlineComments,
} from "../comments/inline-comments.mjs";
import {
  buildConventionalHeaderFromFinding,
  resolveConventionalMeta,
} from "../comments/formatting.mjs";
import { applyRetroDupeFilter } from "../comments/retro-dupe-filter.mjs";
import { applyRetroActions } from "../comments/retro-actions.mjs";

const severityOrder = {
  Blocker: 3,
  Concern: 2,
  Nit: 1,
};

const normalizeLocationPath = (locationPath) => {
  if (null === locationPath) {
    return null;
  }
  const normalized = path.normalize(locationPath);
  return normalized.replace(/^[.][\\/]/, "");
};

const filterFindingLocations = (finding, validPaths) => {
  const locations = Array.isArray(finding.locations) ? finding.locations : null;
  if (null === locations) {
    return finding;
  }
  const droppedPaths = new Set();
  const filteredLocations = locations
    .map((location) => {
      const locationPath = normalizeLocationPath(location?.path ?? null);
      if (null === locationPath) {
        return null;
      }
      if (true === validPaths.has(locationPath)) {
        return { ...location, path: locationPath };
      }
      droppedPaths.add(locationPath);
      return null;
    })
    .filter(Boolean);
  if (0 === filteredLocations.length) {
    if (0 < droppedPaths.size) {
      log(
        `[aggregate] dropped finding outside diff: ${finding?.title || "Finding"} -> ${[
          ...droppedPaths,
        ].join(", ")}`
      );
    }
    return null;
  }
  return { ...finding, locations: filteredLocations };
};

const applyConfidenceRules = (finding, thresholds) => {
  if (null == thresholds) {
    return finding;
  }
  const confidence = Number(finding.confidence ?? 0);
  if (confidence < thresholds.drop_below) {
    return null;
  }
  const updated = { ...finding };
  if (confidence < thresholds.concern_min && "Concern" === updated.severity) {
    updated.severity = "Nit";
  }
  if (confidence < thresholds.blocker_min && "Blocker" === updated.severity) {
    updated.severity = "Concern";
  }
  return updated;
};

const enforceCaps = (findings, config, sizeKey) => {
  const caps = config?.severity_caps || {};
  const budget = config?.comment_budget_by_size?.[sizeKey] ?? Infinity;
  const grouped = {
    Blocker: [],
    Concern: [],
    Nit: [],
  };
  findings.forEach((finding) => {
    const severity = grouped[finding.severity] ? finding.severity : "Nit";
    grouped[severity].push(finding);
  });
  const capped = [];
  const overflow = [];
  const capFor = (severity) => caps[`${severity.toLowerCase()}_max`] ?? Infinity;
  ["Blocker", "Concern", "Nit"].forEach((severity) => {
    const list = grouped[severity].sort(
      (a, b) => (b.confidence || 0) - (a.confidence || 0)
    );
    const keep = list.slice(0, capFor(severity));
    const drop = list.slice(capFor(severity));
    capped.push(...keep);
    overflow.push(...drop);
  });
  const sorted = capped.sort((a, b) => {
    const severityDiff =
      (severityOrder[b.severity] || 0) - (severityOrder[a.severity] || 0);
    if (0 !== severityDiff) {
      return severityDiff;
    }
    return (b.confidence || 0) - (a.confidence || 0);
  });
  const budgeted = sorted.slice(0, budget);
  const budgetOverflow = sorted.slice(budget);
  return {
    budgeted,
    overflow: [...overflow, ...budgetOverflow],
  };
};

export const aggregateResults = task(
  { name: "aggregateResults" },
  async ({ facts, results }) => {
    log("aggregate: start");
    const thresholds  = facts.config?.confidence_thresholds;
    const validPaths  = new Set(facts.changedFiles || []);
    const allFindings = [];
    const reviewerStats = {};
    results.forEach((result) => {
      const parsed = result.parsed;
      if (null == parsed || false === Array.isArray(parsed.findings)) {
        return;
      }
      reviewerStats[result.reviewer] = parsed.findings.length;
      parsed.findings.forEach((finding) => {
        const filtered = filterFindingLocations(finding, validPaths);
        if (null === filtered) {
          return;
        }
        const updated = applyConfidenceRules(filtered, thresholds);
        if (updated) {
          allFindings.push({ ...updated, reviewer: result.reviewer });
        }
      });
    });
    const retroFiltered = await applyRetroDupeFilter({
      facts,
      findings: allFindings,
    });
    const filteredFindings =
      Array.isArray(retroFiltered?.filtered) && 0 < retroFiltered.filtered.length
        ? retroFiltered.filtered
        : allFindings;
    const retroDroppedCount = Array.isArray(retroFiltered?.dropped)
      ? retroFiltered.dropped.length
      : 0;
    const { budgeted, overflow } = enforceCaps(
      filteredFindings,
      facts.config,
      facts.sizeKey
    );
    const prFindings = budgeted.filter((finding) => {
      const meta = resolveConventionalMeta(finding);
      if ("suggestion" === meta.label) {
        const confidenceMin =
          facts.config?.confidence_thresholds?.concern_min ?? 0.75;
        return Number(finding?.confidence ?? 0) >= confidenceMin;
      }
      return meta.decorations.includes("blocking");
    });
    const summaryForInline = { pr_comment: { findings: prFindings } };
    const inlineResult = facts.outputPaths
      ? await buildInlineComments(summaryForInline, facts)
      : { comments: [], inlinedKeys: new Set() };
    const inlinedKeys = inlineResult?.inlinedKeys || new Set();
    const prFindingsForComment = prFindings.map((finding) => ({
      ...finding,
      posted_inline: inlinedKeys.has(
        buildFindingKey(finding, finding.locations?.[0])
      ),
    }));
    const summaryCounts = (() => {
      const counts = new Map();
      prFindingsForComment.forEach((finding) => {
        const meta = resolveConventionalMeta(finding);
        const label = meta.label || "note";
        counts.set(label, (counts.get(label) || 0) + 1);
      });
      const orderedLabels = ["issue", "suggestion", "question", "note", "nitpick"];
      const summaryParts = orderedLabels
        .filter((label) => counts.has(label))
        .map((label) => `${counts.get(label)} ${label}${1 === counts.get(label) ? "" : "s"}`);
      const remaining = [...counts.entries()]
        .filter(([label]) => false === orderedLabels.includes(label))
        .map(([label, count]) => `${count} ${label}${1 === count ? "" : "s"}`);
      const allParts = [...summaryParts, ...remaining];
      return allParts.length ? allParts.join(", ") + "." : "No findings.";
    })();
    const summary = {
      pr_comment: {
        summary: summaryCounts,
        findings: prFindingsForComment,
      },
      private_summary: {
        summary: `Total findings: ${allFindings.length}.`,
        findings: [...budgeted, ...overflow],
        trends: [],
        reviewer_stats: reviewerStats,
      },
    };
    log(
      `aggregate: pr_findings=${prFindings.length} total_findings=${allFindings.length}`
    );
    if (facts.outputPaths) {
      writeJson(facts.outputPaths.aggregateFindings, summary);
      const inlineComments = inlineResult?.comments || [];
      writeJson(
        path.join(facts.outputPaths.outputRoot, "aggregate/inline-comments.json"),
        inlineComments
      );
      const reviewersDecision = facts.outputPaths?.reviewersDecision
        ? readJson(facts.outputPaths.reviewersDecision)
        : null;
      const selectedReviewers = reviewersDecision?.selectedReviewers || [];
      const reviewerDefinitions = loadReviewerDefinitions(facts.repoRoot);
      const overallSummary = facts.outputPaths?.summariesOverall
        ? readJson(facts.outputPaths.summariesOverall)
        : null;
      const dynamicGroups = facts.outputPaths?.summariesDynamicGroups
        ? readJson(facts.outputPaths.summariesDynamicGroups)
        : null;
      const overallConfidence = Number(overallSummary?.confidence);
      const overallLines =
        overallSummary && false === overallSummary.skipped && overallSummary.summary
          ? [
              "## Overall Summary",
              overallSummary.summary,
              ...(Number.isFinite(overallConfidence)
                ? ["", `Confidence: ${Math.round(overallConfidence * 100)}%`]
                : []),
            ]
          : [];
      const reviewerLines = selectedReviewers.length
        ? (() => {
            const normalizedNames = selectedReviewers.map((name) =>
              name.replace(/^review-/, "")
            );
            const summaryLine = `(${normalizedNames.length}/${reviewerDefinitions.length}) ${normalizedNames.join(", ")}`;
            return [
              ...(reviewersDecision?.rationale ? [reviewersDecision.rationale, ""] : []),
              summaryLine,
            ];
          })()
        : [];
      const reviewerDetailsLines = reviewerLines.length
        ? [
            "<details>",
            "<summary>Reviewers</summary>",
            "",
            ...reviewerLines,
            "</details>",
          ]
        : [];
      const sizeLines = (() => {
        if (null == facts.sizeKey) {
          return [];
        }
        const sizeLabel = `${facts.sizeKey[0].toUpperCase()}${facts.sizeKey.slice(1)}`;
        const budget = facts.config?.comment_budget_by_size?.[facts.sizeKey];
        const reviewerRuns = facts.config?.reviewer_runs_by_size?.[facts.sizeKey];
        const parts = [`Size: ${sizeLabel}`];
        if (null != budget) {
          parts.push(`Comment Budget: ${budget}`);
        }
        if (null != reviewerRuns) {
          parts.push(`Reviewer Runs: ${reviewerRuns}`);
        }
        return parts.length ? [parts.join(", ") + "."] : [];
      })();
      const groupedChangesLines = (() => {
        if (
          null == dynamicGroups ||
          true === dynamicGroups.skipped ||
          false === Array.isArray(dynamicGroups.groups) ||
          0 === dynamicGroups.groups.length
        ) {
          return [];
        }
        const lines = ["## Grouped Changes"];
        dynamicGroups.groups.forEach((group, index) => {
          if (0 < index) {
            lines.push("");
          }
          const label = group.label || group.key || "Group";
          lines.push(`**${label}**`);
          lines.push(group.summary || "(no summary)");
          const filePaths = Array.isArray(group.file_paths) ? group.file_paths : [];
          filePaths.forEach((filePath) => {
            lines.push(`- \`${filePath}\``);
          });
        });
        return lines;
      })();
      const reviewFindings = prFindingsForComment.filter(
        (finding) => true !== finding.posted_inline
      );
      const retroDropLine =
        retroDroppedCount > 0
          ? `Retro dupe filter: Dropped ${retroDroppedCount} duplicate finding${
              retroDroppedCount === 1 ? "" : "s"
            }.`
          : null;
      const summaryCommentLines = [
        "<!-- dh:review-summary -->",
        "## DeepHive Summary",
        summary.pr_comment.summary,
        ...sizeLines,
        ...(retroDropLine ? [retroDropLine] : []),
        ...(0 < overallLines.length ? ["", ...overallLines] : []),
        ...(0 < groupedChangesLines.length ? ["", ...groupedChangesLines] : []),
        ...(0 < reviewerDetailsLines.length ? ["", ...reviewerDetailsLines] : []),
      ];
      const summaryCommentBody = `${summaryCommentLines.join("\n")}\n`;
      writeText(facts.outputPaths.aggregateSummaryComment, summaryCommentBody);
      const reviewCommentLines = [
        "## Summary",
        summary.pr_comment.summary,
        "",
        "## Findings",
        reviewFindings.length
          ? reviewFindings
              .map((finding, index) => {
                const header = buildConventionalHeaderFromFinding(finding);
                const titleLine = `${index + 1}. ${header}`;
                const detailLines = [];
                if (finding.reviewer) {
                  detailLines.push(
                    `   Reviewer: ${finding.reviewer.replace(/^review-/, "")}`
                  );
                }
                const confidenceValue = Number(finding.confidence);
                if (Number.isFinite(confidenceValue)) {
                  detailLines.push(`   Confidence: ${Math.round(confidenceValue * 100)}%`);
                }
                if (finding.rationale) {
                  detailLines.push(`   Rationale: ${finding.rationale}`);
                }
                if (finding.suggested_fix) {
                  detailLines.push(`   Suggestion: ${finding.suggested_fix}`);
                }
                const locations = Array.isArray(finding.locations)
                  ? finding.locations
                  : [];
                if (0 < locations.length) {
                  detailLines.push("   Locations:");
                  locations.forEach((location) => {
                    const pathLine = location?.path
                      ? `- \`${location.path}\`${location.lines ? ` (${location.lines})` : ""}`
                      : null;
                    if (pathLine) {
                      detailLines.push(`     ${pathLine}`);
                    }
                    if (location?.snippet) {
                      detailLines.push(`     ${location.snippet}`);
                    }
                  });
                }
                return [titleLine, ...detailLines].join("\n");
              })
              .join("\n")
          : prFindingsForComment.length
            ? "No additional feedback beyond inline comments."
            : "No findings.",
      ];
      const reviewCommentBody = `${reviewCommentLines.join("\n")}\n`;
      writeText(facts.outputPaths.aggregateReviewComment, reviewCommentBody);
      if (facts.outputPaths.aggregateReviewPayload) {
        const hasBlocking = prFindingsForComment.some((finding) => {
          const meta = resolveConventionalMeta(finding);
          return meta.decorations.includes("blocking");
        });
        const reviewEvent = hasBlocking
          ? "REQUEST_CHANGES"
          : prFindingsForComment.length
            ? "COMMENT"
            : "APPROVE";
        const inlineComments = inlineResult?.comments || [];
        writeJson(facts.outputPaths.aggregateReviewPayload, {
          event: reviewEvent,
          body: reviewCommentBody,
          comments: inlineComments,
        });
      }
      const privateLines = [
        "## Summary",
        summary.private_summary.summary,
        "",
        "## Findings",
        summary.private_summary.findings
          .map(
            (finding, index) =>
              `${index + 1}. [${finding.severity}] ${finding.title || "Finding"}`
          )
          .join("\n") || "No findings.",
        "",
        "## Reviewer Stats",
        JSON.stringify(summary.private_summary.reviewer_stats, null, 2),
      ];
      writeText(
        facts.outputPaths.aggregatePrivateSummary,
        `${privateLines.join("\n")}\n`
      );
    }
    const retroReviewer = results.find(
      (result) => "review-retro-feedback" === result?.reviewer
    );
    if (null != retroReviewer?.parsed) {
      applyRetroActions({ facts, retroResult: retroReviewer.parsed });
    }
    return summary;
  }
);
