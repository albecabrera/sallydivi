import OpenAI from "openai";

import { RETRO_DUPE_MODEL } from "../core/constants.mjs";
import { log } from "../core/logging.mjs";
import { parseJsonSafe } from "../core/utils.mjs";

const buildRetroDupePrompt = ({ retroReview, findings }) => [
  {
    role: "system",
    content: [
      "You are a dedupe filter for automated PR review findings.",
      "Your job is to remove findings that are true duplicates of prior feedback",
      "that has already been resolved or rebutted by the author.",
      "Only drop findings when the duplication is clear.",
      "When unsure, keep the finding.",
    ].join(" "),
  },
  {
    role: "user",
    content: [
      "Return JSON only with:",
      "{",
      '  "drop_finding_ids": ["finding_1", "..."],',
      '  "notes": "short explanation"',
      "}",
      "",
      "Rules:",
      "- drop_finding_ids should only include findings that are already addressed",
      "  in prior feedback and do not have new evidence in the diff since last run.",
      "- Use Prior Review Feedback to compare against resolved threads and rebuttals.",
      "- Use diff_since_last_run to decide whether new evidence exists.",
      "- Prefer keeping a finding when it contains new nuance or new evidence.",
      "",
      "Prior Review Feedback (facts.retroReview):",
      JSON.stringify(retroReview || {}, null, 2),
      "",
      "New Findings:",
      JSON.stringify(findings || [], null, 2),
    ].join("\n"),
  },
];

const getResponseText = (response) =>
  response.output_text ||
  response.output?.map((item) => item.content?.[0]?.text ?? "").join("\n") ||
  "";

export const applyRetroDupeFilter = async ({ facts, findings }) => {
  const retroReview = facts?.retroReview || null;
  if (null == retroReview || true !== retroReview.enabled) {
    return { filtered: findings || [], dropped: [] };
  }
  if (false === Array.isArray(findings) || 0 === findings.length) {
    return { filtered: findings || [], dropped: [] };
  }
  const apiKey = process.env.OPENAI_API_KEY;
  if (null == apiKey || "" === String(apiKey).trim()) {
    log("[retro-dupe] warning: OPENAI_API_KEY missing; skipping retro dupe filter.");
    return { filtered: findings || [], dropped: [] };
  }
  const client = new OpenAI({ apiKey });
  const indexed = findings.map((finding, index) => ({
    id: `finding_${index + 1}`,
    finding,
  }));
  const payload = indexed.map(({ id, finding }) => ({
    id,
    title: finding?.title || "Finding",
    severity: finding?.severity || null,
    confidence: finding?.confidence ?? null,
    reviewer: finding?.reviewer || null,
    rationale: finding?.rationale || null,
    suggested_fix: finding?.suggested_fix || null,
    locations: Array.isArray(finding?.locations)
      ? finding.locations.map((location) => ({
          path: location?.path ?? null,
          lines: location?.lines ?? null,
          snippet: location?.snippet ?? null,
        }))
      : [],
  }));
  try {
    const response = await client.responses.create({
      model: RETRO_DUPE_MODEL,
      input: buildRetroDupePrompt({ retroReview, findings: payload }),
      text: {
        format: {
          type: "json_schema",
          name: "retro_dupe_filter",
          strict: true,
          schema: {
            type: "object",
            properties: {
              drop_finding_ids: {
                type: "array",
                items: { type: "string" },
              },
              notes: { type: "string" },
            },
            required: ["drop_finding_ids", "notes"],
            additionalProperties: false,
          },
        },
      },
    });
    const outputText = getResponseText(response);
    const parsed = parseJsonSafe(outputText);
    const drops = Array.isArray(parsed?.drop_finding_ids)
      ? parsed.drop_finding_ids
      : [];
    if (0 === drops.length) {
      return { filtered: findings || [], dropped: [] };
    }
    const dropSet = new Set(drops);
    const filtered = indexed
      .filter(({ id }) => false === dropSet.has(id))
      .map(({ finding }) => finding);
    const dropped = indexed
      .filter(({ id }) => true === dropSet.has(id))
      .map(({ finding }) => finding);
    log(`[retro-dupe] dropped ${dropped.length} duplicate findings.`);
    return { filtered, dropped };
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    log(`[retro-dupe] warning: failed to apply retro dupe filter. ${message}`);
    return { filtered: findings || [], dropped: [] };
  }
};
