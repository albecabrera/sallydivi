const SEVERITY_TO_CONVENTIONAL = {
  Blocker: { label: "issue", decorations: ["blocking"] },
  Concern: { label: "issue", decorations: [] },
  Nit: { label: "nitpick", decorations: ["non-blocking"] },
};

const normalizeLabel = (value) =>
  null == value || "" === String(value).trim()
    ? null
    : String(value).trim().toLowerCase();

const normalizeDecorations = (value) =>
  Array.isArray(value)
    ? value
        .map((entry) => normalizeLabel(entry))
        .filter(Boolean)
    : null;

export const resolveConventionalMeta = (finding) => {
  const fallback = { label: "note", decorations: [] };
  const severityConfig = SEVERITY_TO_CONVENTIONAL[finding?.severity] || fallback;
  const label = normalizeLabel(finding?.comment_label) || severityConfig.label;
  const decorations =
    normalizeDecorations(finding?.comment_decorations) ??
    severityConfig.decorations;
  return {
    label: label || fallback.label,
    decorations: Array.isArray(decorations) ? decorations : [],
  };
};

export const buildConventionalHeaderFromFinding = (finding) => {
  const meta = resolveConventionalMeta(finding);
  const decorationText = meta.decorations.length
    ? ` (${meta.decorations.join(", ")})`
    : "";
  const header = `${meta.label}${decorationText}:`;
  return `**${header}** ${finding?.title || "Finding"}`;
};
