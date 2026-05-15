const formatRelatedPrs = (relatedPrs) => {
  if (!Array.isArray(relatedPrs) || 0 === relatedPrs.length) {
    return "(none)";
  }
  return relatedPrs
    .map((pr) => {
      const repo = pr.repoSlug || "(unknown-repo)";
      const number = pr.prNumber || "(unknown-pr)";
      const source = pr.source ? ` source=${pr.source}` : "";
      const issue = pr.issue?.repoSlug && pr.issue?.issueNumber
        ? ` issue=${pr.issue.repoSlug}#${pr.issue.issueNumber}`
        : "";
      return `- ${repo}#${number}${source}${issue}`;
    })
    .join("\n");
};

export const decisionPrompt = ({
  reviewers,
  changedFiles,
  codeFiles,
  taskFiles,
  sizeKey,
  config,
  taskContext,
  mode,
  baseRef,
  headRef,
  relatedPrs,
}) => {
  const reviewerList = reviewers
    .map((reviewer) => `- ${reviewer.name}: ${reviewer.description}`)
    .join("\n");
  return [
    "You are operating in a GitHub Actions runner performing automated code review.",
    "The gh CLI is available and authenticated via GH_TOKEN. You may comment on pull requests.",
    "",
    "Decide which reviewers should run for this change.",
    "Return JSON only, with: selected_reviewers (array), rationale (string).",
    "Only select from the provided reviewer list.",
    "",
    `Mode: ${mode}`,
    baseRef ? `Base ref: ${baseRef}` : "Base ref: (none)",
    headRef ? `Head ref: ${headRef}` : "Head ref: (none)",
    `Review size: ${sizeKey}`,
    "",
    "Related PRs:",
    formatRelatedPrs(relatedPrs),
    "",
    "Changed files:",
    changedFiles.map((file) => `- ${file}`).join("\n") || "(none)",
    "",
    "Task files:",
    taskFiles.map((file) => `- ${file}`).join("\n") || "(none)",
    "",
    "Task context:",
    JSON.stringify(taskContext, null, 2),
    "",
    "Reviewer list:",
    reviewerList,
    "",
    "Constraints:",
    JSON.stringify(
      {
        comment_budget_by_size: config?.comment_budget_by_size || {},
        comment_label_caps: config?.comment_label_caps || {},
        confidence_thresholds: config?.confidence_thresholds || {},
      },
      null,
      2
    ),
  ].join("\n");
};

export const buildReviewerSummaryContext = (
  summaries,
  outputPaths,
  focusedFiles
) => {
  if (null == summaries) {
    return "Summaries: (not available)";
  }
  const groupLines = (summaries.groups || [])
    .slice(0, 15)
    .map(
      (group) =>
        `- ${group.key}: ${group.summary || "(no summary)"} (${group.confidence ?? 0})`
    );
  const dynamicGroupLines = (summaries.dynamic_groups || [])
    .slice(0, 10)
    .map((group) => {
      const label = group.label || "Group";
      const summary = group.summary || "(no summary)";
      const fileList = Array.isArray(group.file_paths)
        ? group.file_paths.slice(0, 8).join(", ")
        : "";
      const suffix = fileList ? ` Files: ${fileList}` : "";
      return `- ${label}: ${summary} (${group.confidence ?? 0}).${suffix}`;
    });
  const focusedLines = (focusedFiles || [])
    .map((file) => {
      const size = (file.additions ?? 0) + (file.deletions ?? 0);
      const summary = file.summary || "(no summary)";
      return `- ${file.path} (${size} lines): ${summary}`;
    })
    .slice(0, 12);
  return [
    "Summaries (use these before re-diffing):",
    summaries.overall?.summary
      ? `Overall: ${summaries.overall.summary}`
      : "Overall: (not available)",
    "",
    "Group summaries:",
    groupLines.length ? groupLines.join("\n") : "(none)",
    "",
    "Thematic group summaries:",
    dynamicGroupLines.length ? dynamicGroupLines.join("\n") : "(none)",
    "",
    "Focused file summaries:",
    focusedLines.length ? focusedLines.join("\n") : "(none)",
    "",
    outputPaths?.summariesFiles
      ? `Full file summaries: ${outputPaths.summariesFiles}`
      : "Full file summaries: (not available)",
  ].join("\n");
};

export const buildReviewerMergePrompt = ({
  reviewer,
  outputs,
  outputContract,
  sizeKey,
}) => [
  "You are merging multiple AI reviewer outputs into a single response.",
  "Deduplicate by theme. Merge locations when findings overlap.",
  "Keep the most severe classification when conflicts exist, but avoid escalation.",
  "If findings are equivalent, keep the higher confidence or clearer rationale.",
  "Preserve suggested fixes only when they are consistent across outputs.",
  "Return JSON only and follow the reviewer output contract below.",
  "",
  `Reviewer: ${reviewer?.name || "reviewer"}`,
  `Review size: ${sizeKey || "unknown"}`,
  "",
  "Reviewer output contract:",
  outputContract || "(output contract unavailable)",
  "",
  "Reviewer outputs to merge (JSON):",
  outputs.map((output, index) => `--- Output ${index + 1} ---\n${output}`).join("\n\n"),
].join("\n");

export const reviewerPrompt = ({
  reviewer,
  changedFiles,
  taskFiles,
  taskContext,
  retroReview,
  mode,
  baseRef,
  headRef,
  prNumber,
  repoSlug,
  repoRoot,
  summaries,
  outputPaths,
  focusedFiles,
  outputContract,
  relatedPrs,
}) => [
  "You are a senior software engineer performing a pull request review.",
  "Your goal is to identify high-impact issues, not stylistic preferences.",
  "Only comment on changed files or immediate context. Silence is acceptable.",
  "You are operating in a GitHub Actions runner performing automated code review.",
  "The gh CLI is available and authenticated via GH_TOKEN. You may comment on pull requests.",
  "You are read-only: do not modify files or apply patches.",
  "Do not re-raise prior feedback unless diff_since_last_run shows new evidence or context.",
  "Prior feedback is provided in the 'Prior review feedback' section as facts.retroReview JSON,",
  "including threads, recent_comments, and diff_since_last_run.",
  "",
  `Reviewer: ${reviewer.name}`,
  "",
  reviewer.body,
  "",
  "Context:",
  `Repo root: ${repoRoot}`,
  `Mode: ${mode}`,
  baseRef ? `Base ref: ${baseRef}` : "Base ref: (none)",
  headRef ? `Head ref: ${headRef}` : "Head ref: (none)",
  prNumber ? `PR number: ${prNumber}` : "PR number: (none)",
  repoSlug ? `Repo slug: ${repoSlug}` : "Repo slug: (none)",
  "",
  "Related PRs:",
  formatRelatedPrs(relatedPrs),
  "",
  "Changed files (filtered):",
  (focusedFiles || []).map((file) => `- ${file.path}`).join("\n") || "(none)",
  `Filtered file count: ${(focusedFiles || []).length}`,
  `Total changed files: ${changedFiles.length}`,
  "",
  "Task files:",
  taskFiles.map((file) => `- ${file}`).join("\n") || "(none)",
  "",
  "Task context:",
  JSON.stringify(taskContext, null, 2),
  "",
  "Prior review feedback:",
  retroReview ? JSON.stringify(retroReview, null, 2) : "(none)",
  "",
  buildReviewerSummaryContext(summaries, outputPaths, focusedFiles),
  "",
  "Use the summaries to guide your review and pique your interest. Only inspect diffs on-demand for files you choose after reviewing the summaries and finding something of interest.",
  "Make sure you review and base your comments on the diffs, not the summaries.",
  "",
  "Conventional Comments guidance: follow the output contract for labels and decorations.",
  "",
  "Return JSON only and follow the output contract below.",
  "",
  outputContract || "(output contract unavailable)",
].join("\n");
