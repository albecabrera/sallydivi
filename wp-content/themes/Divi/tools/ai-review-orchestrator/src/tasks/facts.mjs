import { task } from "@langchain/langgraph";

import { SUMMARY_MODEL } from "../core/constants.mjs";
import { getRepoRoot } from "../core/git.mjs";
import { run } from "../core/exec.mjs";
import { log } from "../core/logging.mjs";
import { resolveSummaryCacheDir } from "../summary/summary.mjs";
import { loadConfig } from "../core/config.mjs";
import { resolveLatestPrRun } from "../core/paths.mjs";
import { classifySize } from "../reviewers/selection.mjs";
import {
  buildTaskContext,
  applyPrBodyTaskFallback,
  countPatchLines,
  fetchCompareDiff,
  fetchPrByNumber,
  fetchPrCommits,
  fetchPrFiles,
  fetchReviewThreads,
  buildRelatedPath,
  getFilePatch,
  getNameStatusForMode,
  getNumstatForMode,
  getPrDiff,
  getPrMeta,
  isTaskFile,
  resolveRelatedPrs,
  resolveLocalRepoPath,
  resolveMode,
  resolveRepoArg,
  splitChangedFiles,
  filterPatchByPredicate,
} from "../facts/helpers.mjs";
import {
  resolvePreflightEnabled,
  resolvePreflightStrict,
} from "../preflight/preflight.mjs";
import { unique } from "../core/utils.mjs";

const isSnapshotFile = (filePath) =>
  null != filePath && /(?:^|\/)__snapshots__\//.test(filePath);

const isExcludedFromSizing = (filePath) =>
  true === isTaskFile(filePath) || true === isSnapshotFile(filePath);

const normalizeLogin = (value) =>
  null == value ? "" : String(value).trim().toLowerCase();

const toTimestamp = (value) => {
  if (null == value || "" === value) {
    return null;
  }
  const parsed = new Date(value).getTime();
  return Number.isNaN(parsed) ? null : parsed;
};

const truncateBody = (value, limit = 1200) => {
  if (null == value) {
    return "";
  }
  const trimmed = String(value).trim();
  if (trimmed.length <= limit) {
    return trimmed;
  }
  return `${trimmed.slice(0, limit)}\n... (truncated)`;
};

const buildRetroReviewContext = ({
  repoRoot,
  repoSlug,
  prNumber,
  runStartedAt,
  currentHeadSha,
  config,
}) => {
  if (null == repoRoot || null == repoSlug || null == prNumber) {
    return null;
  }
  const previousRun = resolveLatestPrRun(repoRoot, prNumber);
  if (null == previousRun?.started_at) {
    return null;
  }
  const sinceTimestamp = toTimestamp(previousRun.started_at);
  const untilTimestamp = toTimestamp(runStartedAt);
  if (null == sinceTimestamp || null == untilTimestamp) {
    return null;
  }
  const botLogin = normalizeLogin(config?.feedback_bot_login || "DeepHiveET");
  let threads = [];
  try {
    threads = fetchReviewThreads({ prNumber, repoSlug });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    log(`[retro-review] warning: failed to fetch review threads. ${message}`);
    return {
      enabled: false,
      error: message,
    };
  }
  const filteredThreads = threads.filter((thread) => {
    const comments = Array.isArray(thread?.comments?.nodes)
      ? thread.comments.nodes
      : [];
    return comments.some((comment) => {
      const createdAt = toTimestamp(comment?.createdAt);
      if (null == createdAt) {
        return false;
      }
      const author = normalizeLogin(comment?.author?.login);
      if (botLogin !== author) {
        return false;
      }
      return createdAt >= sinceTimestamp && createdAt <= untilTimestamp;
    });
  });
  const normalizedThreads = filteredThreads.map((thread) => {
    const comments = Array.isArray(thread?.comments?.nodes)
      ? thread.comments.nodes
      : [];
    const mapped = comments.map((comment) => ({
      id: comment?.databaseId ?? comment?.id ?? null,
      author: comment?.author?.login ?? null,
      created_at: comment?.createdAt ?? null,
      body: truncateBody(comment?.body ?? ""),
      path: comment?.path ?? null,
      line: comment?.line ?? comment?.originalLine ?? null,
      position: comment?.position ?? null,
      diff_hunk: comment?.diffHunk ?? null,
      url: comment?.url ?? null,
    }));
    const recentComments = mapped.filter((comment) => {
      const createdAt = toTimestamp(comment?.created_at);
      if (null == createdAt) {
        return false;
      }
      return createdAt >= sinceTimestamp;
    });
    const botComments = mapped.filter(
      (comment) => normalizeLogin(comment.author) === botLogin
    );
    const botCommentIds = botComments
      .map((comment) => comment.id)
      .filter(Boolean);
    if (0 === botCommentIds.length) {
      log(
        `[retro-review] warning: missing bot comment id for thread ${thread?.id || "unknown"}`
      );
    }
    return {
      thread_id: thread?.id ?? null,
      is_resolved: true === thread?.isResolved,
      resolved_at: thread?.resolvedAt ?? null,
      resolved_by: thread?.resolvedBy?.login ?? null,
      bot_comment_count: botComments.length,
      bot_comment_id: botCommentIds[0] ?? null,
      bot_comment_ids: botCommentIds,
      recent_comment_count: recentComments.length,
      recent_comments: recentComments.slice(0, 10),
      comments: mapped.slice(0, 25),
    };
  });
  const resolvedCount = normalizedThreads.filter(
    (thread) => true === thread.is_resolved
  ).length;
  if (0 === normalizedThreads.length) {
    log("[retro-review] warning: no prior DeepHive review threads found.");
  }
  let commits = [];
  try {
    commits = fetchPrCommits({ prNumber, repoSlug });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    log(`[retro-review] warning: failed to fetch PR commits. ${message}`);
    commits = [];
  }
  const commitEntries = commits
    .map((entry) => {
      const commit = entry?.commit || {};
      const message = commit?.message || "";
      const author = commit?.author?.name || null;
      const date = commit?.committer?.date || commit?.author?.date || null;
      return {
        sha: entry?.sha ?? null,
        message: message.split("\n")[0] || "",
        author,
        date,
        url: entry?.html_url ?? null,
      };
    })
    .filter((entry) => {
      const date = toTimestamp(entry?.date);
      if (null == date) {
        return false;
      }
      return date >= sinceTimestamp;
    });
  let diffSinceLastRun = null;
  const previousHeadSha = previousRun.run?.head_sha || null;
  if (
    null != previousHeadSha &&
    null != currentHeadSha &&
    previousHeadSha !== currentHeadSha
  ) {
    try {
      const rawDiff = fetchCompareDiff({
        repoSlug,
        baseSha: previousHeadSha,
        headSha: currentHeadSha,
      });
      diffSinceLastRun = truncateBody(rawDiff, 12000);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      log(`[retro-review] warning: failed to fetch compare diff. ${message}`);
    }
  } else if (null == previousHeadSha) {
    log("[retro-review] warning: previous run head sha missing.");
  }
  return {
    enabled: true,
    previous_run: {
      run_id: previousRun.run_id,
      started_at: previousRun.started_at,
    },
    diff_base_sha: previousHeadSha,
    diff_head_sha: currentHeadSha || null,
    diff_since_last_run: diffSinceLastRun,
    bot_login: botLogin,
    thread_count: normalizedThreads.length,
    resolved_threads: resolvedCount,
    unresolved_threads: normalizedThreads.length - resolvedCount,
    threads: normalizedThreads.slice(0, 40),
    commit_count: commitEntries.length,
    commits_since_last_run: commitEntries.slice(0, 40),
  };
};

export const collectFacts = task({ name: "collectFacts" }, async (input) => {
  log("facts: collect start");
  const repoRoot = getRepoRoot();
  const runStartedAt = new Date().toISOString();
  const mode = resolveMode(input);
  const preflightEnabled = resolvePreflightEnabled({
    mode,
    flag: input.preflightEnabled,
  });
  const preflightStrict = resolvePreflightStrict({
    mode,
    strictFlag: input.preflightStrict,
    warnFlag: input.preflightWarn,
  });
  const repoSlug = resolveRepoArg(input.repoArg, repoRoot);
  const localRepoPath = resolveLocalRepoPath(input.repoArg, repoRoot);
  const contextLines = null == input.contextLines ? 8 : input.contextLines;
  let baseRef = input.baseRef || null;
  let headRef = input.headRef || "HEAD";
  let prMeta = null;
  let changedFiles = [];
  let fileMetadata = [];
  let rawPatchForSizing = "";
  let patchForSizing = "";
  let relatedPrs = [];

  if ("working-tree" === mode) {
    log("facts: working-tree");
    const unstaged = run("git", ["diff", "--name-only"]);
    const staged = run("git", ["diff", "--cached", "--name-only"]);
    changedFiles = unique([
      ...unstaged.split("\n").filter(Boolean),
      ...staged.split("\n").filter(Boolean),
    ]);
    const unstagedPatch = run("git", [
      "diff",
      "--patch",
      `--unified=${contextLines}`,
      "--",
      ".",
      ":(exclude)includes/builder-5/et/tasks/**",
      ":(exclude)includes/builder-5/.et/tasks/**",
      ":(exclude)et/tasks/**",
      ":(exclude).cursor/tasks/**",
      ":(exclude)**/__snapshots__/**",
    ]);
    const stagedPatch = run("git", [
      "diff",
      "--cached",
      "--patch",
      `--unified=${contextLines}`,
      "--",
      ".",
      ":(exclude)includes/builder-5/et/tasks/**",
      ":(exclude)includes/builder-5/.et/tasks/**",
      ":(exclude)et/tasks/**",
      ":(exclude).cursor/tasks/**",
      ":(exclude)**/__snapshots__/**",
    ]);
    patchForSizing = [unstagedPatch, stagedPatch]
      .filter((patch) => patch && patch.trim())
      .join("\n\n");
    rawPatchForSizing = run("git", [
      "diff",
      "--patch",
      `--unified=${contextLines}`,
      "--",
      ".",
    ]);
    const rawStagedPatch = run("git", [
      "diff",
      "--cached",
      "--patch",
      `--unified=${contextLines}`,
      "--",
      ".",
    ]);
    rawPatchForSizing = [rawPatchForSizing, rawStagedPatch]
      .filter((patch) => patch && patch.trim())
      .join("\n\n");
    const numstat = getNumstatForMode({ mode, baseRef, headRef });
    const nameStatus = getNameStatusForMode({ mode, baseRef, headRef });
    const statusMap = new Map(nameStatus.map((entry) => [entry.path, entry]));
    const allPaths = unique([
      ...numstat.map((entry) => entry.path),
      ...nameStatus.map((entry) => entry.path),
      ...changedFiles,
    ]);
    fileMetadata = allPaths.map((filePath) => {
      const stats = numstat.find((entry) => entry.path === filePath) || {};
      const status = statusMap.get(filePath);
      return {
        path: filePath,
        additions: stats.additions ?? null,
        deletions: stats.deletions ?? null,
        status: status?.status ?? null,
        old_path: status?.oldPath ?? null,
      };
    });
  } else if ("branch-compare" === mode) {
    log("facts: branch-compare");
    if (null === baseRef) {
      throw new Error("Missing required --base <ref> argument.");
    }
    changedFiles = run("git", [
      "diff",
      "--name-only",
      `${baseRef}...${headRef}`,
    ])
      .split("\n")
      .filter(Boolean);
    patchForSizing = run("git", [
      "diff",
      "--patch",
      `--unified=${contextLines}`,
      `${baseRef}...${headRef}`,
      "--",
      ".",
      ":(exclude)includes/builder-5/et/tasks/**",
      ":(exclude)includes/builder-5/.et/tasks/**",
      ":(exclude)et/tasks/**",
      ":(exclude).cursor/tasks/**",
      ":(exclude)**/__snapshots__/**",
    ]);
    rawPatchForSizing = run("git", [
      "diff",
      "--patch",
      `--unified=${contextLines}`,
      `${baseRef}...${headRef}`,
      "--",
      ".",
    ]);
    const numstat = getNumstatForMode({ mode, baseRef, headRef });
    const nameStatus = getNameStatusForMode({ mode, baseRef, headRef });
    const statusMap = new Map(nameStatus.map((entry) => [entry.path, entry]));
    const allPaths = unique([
      ...numstat.map((entry) => entry.path),
      ...nameStatus.map((entry) => entry.path),
      ...changedFiles,
    ]);
    fileMetadata = allPaths.map((filePath) => {
      const stats = numstat.find((entry) => entry.path === filePath) || {};
      const status = statusMap.get(filePath);
      return {
        path: filePath,
        additions: stats.additions ?? null,
        deletions: stats.deletions ?? null,
        status: status?.status ?? null,
        old_path: status?.oldPath ?? null,
      };
    });
  } else if ("pr-compare" === mode) {
    log("facts: pr-compare");
    if (null === input.prNumber) {
      throw new Error("Missing required --pr <number> argument.");
    }
    prMeta = getPrMeta({ prNumber: input.prNumber, repoSlug });
    if (null === prMeta) {
      const fallback = fetchPrByNumber({
        prNumber: input.prNumber,
        repoSlug,
      });
      if (fallback) {
        prMeta = {
          number: fallback.number,
          url: fallback.html_url,
          title: fallback.title,
          body: fallback.body,
          baseRefName: fallback.base?.ref,
          headRefName: fallback.head?.ref,
          headRefOid: fallback.head?.sha,
        };
      }
    }
    if (null != prMeta?.baseRefName) {
      baseRef = prMeta.baseRefName;
    }
    if (null != prMeta?.headRefName) {
      headRef = prMeta.headRefName;
    }
    const files = fetchPrFiles({
      prNumber: input.prNumber,
      repoSlug,
    });
    changedFiles = files.map((file) => file.filename).filter(Boolean);
    fileMetadata = files.map((file) => ({
      path: file.filename,
      additions: file.additions ?? null,
      deletions: file.deletions ?? null,
      status: file.status ?? null,
      changes: file.changes ?? null,
      patch: file.patch ?? null,
      old_path: file.previous_filename ?? null,
    }));
    const filePatch = getPrDiff({
      prNumber: input.prNumber,
      repoSlug,
      contextLines,
    });
    rawPatchForSizing = filePatch;
    patchForSizing = filterPatchByPredicate(filePatch, (filePath) =>
      false === isExcludedFromSizing(filePath)
    );
    if ("" === patchForSizing && null != localRepoPath && null != baseRef) {
      try {
        const localPatch = run("git", [
          "-C",
          localRepoPath,
          "diff",
          "--patch",
          `--unified=${contextLines}`,
          `${baseRef}...${headRef}`,
          "--",
          ".",
          ":(exclude)includes/builder-5/et/tasks/**",
          ":(exclude)includes/builder-5/.et/tasks/**",
          ":(exclude)et/tasks/**",
          ":(exclude).cursor/tasks/**",
          ":(exclude)**/__snapshots__/**",
        ]);
        const localRawPatch = run("git", [
          "-C",
          localRepoPath,
          "diff",
          "--patch",
          `--unified=${contextLines}`,
          `${baseRef}...${headRef}`,
          "--",
          ".",
        ]);
        rawPatchForSizing = localRawPatch;
        patchForSizing = localPatch;
      } catch (error) {
        log(
          "[pr-compare] warning: local repo/base/head unavailable; using file patches"
        );
        patchForSizing = fileMetadata
          .filter((file) => false === isExcludedFromSizing(file.path))
          .map((file) => file.patch)
          .filter((patch) => patch && patch.trim())
          .join("\n\n");
        rawPatchForSizing = fileMetadata
          .map((file) => file.patch)
          .filter((patch) => patch && patch.trim())
          .join("\n\n");
      }
    }
    relatedPrs = resolveRelatedPrs({
      prMeta,
      repoSlug,
      explicit: input.relatedPrs || [],
      discover: false === input.discoverRelatedPrs ? false : true,
    });
    if (relatedPrs.length) {
      const relatedMetadata = [];
      const relatedPaths = [];
      const relatedPatches = [];
      relatedPrs.forEach((related) => {
        const relatedFiles = fetchPrFiles({
          prNumber: related.prNumber,
          repoSlug: related.repoSlug,
        });
        relatedFiles.forEach((file) => {
          if (!file.filename) {
            return;
          }
          const decoratedPath = buildRelatedPath(related.repoSlug, file.filename);
          relatedPaths.push(decoratedPath);
          relatedMetadata.push({
            path: decoratedPath,
            additions: file.additions ?? null,
            deletions: file.deletions ?? null,
            status: file.status ?? null,
            changes: file.changes ?? null,
            patch: file.patch ?? null,
            old_path: file.previous_filename ?? null,
            source_repo: related.repoSlug,
            source_pr: related.prNumber,
            original_path: file.filename ?? null,
          });
        });
        const relatedDiff = getPrDiff({
          prNumber: related.prNumber,
          repoSlug: related.repoSlug,
          contextLines,
        });
        if (relatedDiff && relatedDiff.trim()) {
          relatedPatches.push(relatedDiff);
        }
      });
      if (relatedPaths.length) {
        changedFiles = unique([...changedFiles, ...relatedPaths]);
      }
      if (relatedMetadata.length) {
        fileMetadata = [...fileMetadata, ...relatedMetadata];
      }
      if (relatedPatches.length) {
        rawPatchForSizing = [rawPatchForSizing, ...relatedPatches]
          .filter((patch) => patch && patch.trim())
          .join("\n\n");
        const relatedPatchForSizing = relatedPatches
          .map((patch) =>
            filterPatchByPredicate(patch, (filePath) =>
              false === isExcludedFromSizing(filePath)
            )
          )
          .filter((patch) => patch && patch.trim());
        patchForSizing = [patchForSizing, ...relatedPatchForSizing]
          .filter((patch) => patch && patch.trim())
          .join("\n\n");
      }
    }
  } else {
    throw new Error(`Unsupported mode: ${mode}`);
  }

  const { taskFiles, codeFiles } = splitChangedFiles(changedFiles);
  const taskContext = applyPrBodyTaskFallback(
    buildTaskContext(repoRoot, taskFiles),
    prMeta?.body
  );
  const config = loadConfig(repoRoot);
  const retroReview =
    "pr-compare" === mode && null != prMeta?.number
      ? buildRetroReviewContext({
          repoRoot,
          repoSlug,
          prNumber: prMeta.number,
          runStartedAt,
          currentHeadSha: prMeta?.headRefOid || null,
          config,
        })
      : null;
  const totalLineCount = countPatchLines(rawPatchForSizing);
  const effectiveLineCount = countPatchLines(patchForSizing);
  const sizeKey = classifySize(effectiveLineCount, config);
  log(
    `facts: files=${changedFiles.length} tasks=${taskFiles.length} size=${sizeKey} lines=${totalLineCount} effective_lines=${effectiveLineCount}`
  );
  const summaryModel =
    input.summaryModel || process.env.OPENAI_SUMMARY_MODEL || SUMMARY_MODEL;
  const summaryCacheDir = resolveSummaryCacheDir({
    repoRoot,
    summaryCacheDir: input.summaryCacheDir || process.env.SUMMARY_CACHE_DIR,
    disableSummaryCache: true === input.disableSummaryCache,
  });
  const reviewerConcurrency = Number.isNaN(input.reviewerConcurrency)
    ? null
    : input.reviewerConcurrency;

  return {
    repoRoot,
    mode,
    baseRef,
    headRef,
    localRepoPath,
    prMeta,
    repoSlug,
    changedFiles,
    fileMetadata,
    codeFiles,
    taskFiles,
    taskContext,
    relatedPrs,
    retroReview,
    config,
    runStartedAt,
    lineCount: totalLineCount,
    effectiveLineCount,
    sizeKey,
    model: input.model || null,
    summaryModel,
    summaryCacheDir,
    judgeModel: input.judgeModel || null,
    preflight: {
      enabled: preflightEnabled,
      strict: preflightStrict,
      allowMissingTasks: true === input.allowMissingTasks,
      allowMergeConflicts: true === input.allowMergeConflicts,
      allowMissingPrBody: true === input.allowMissingPrBody,
      allowFailingChecks: true === input.allowFailingChecks,
      allowUnresolvedThreads: true === input.allowUnresolvedThreads,
    },
    forcedReviewers: input.forcedReviewers || [],
    resumeRunId: input.resumeRunId || null,
    resumeLatest: true === input.resumeLatest,
    refreshSummaries: true === input.refreshSummaries,
    sequential: true === input.sequential,
    staggerMs: Number.isNaN(input.staggerMs) ? 0 : input.staggerMs,
    reviewerConcurrency,
    contextLines,
    timeoutMs: input.timeoutMs,
  };
});
