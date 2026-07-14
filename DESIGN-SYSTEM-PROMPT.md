# Design System Prompt — "Event Puls" theme

> Paste this whole prompt to an AI/dev when starting a NEW system that must look **exactly** like Event Puls.
> The fastest 1:1 path: also copy `public/css/app.css`, `public/js/dropdown.js`, and the `<head>` includes verbatim — this prompt documents what they encode so the result is pixel-faithful.

---

You are building a web app. Reproduce **exactly** the following design system — colors, typography, spacing, radii, shadows, and component styles must match precisely. Do **not** invent new colors or restyle components; only use the tokens and patterns below. The aesthetic is **clean, minimal, professional SaaS**: near-white content background, white surfaces with hairline borders, a dark navy sidebar, indigo brand accent, soft rounded corners, tiny shadows, and restrained color used only for status.

## 1) Stack & setup
- **Bootstrap 5.3** — load the **RTL** build when the UI locale is Arabic, the LTR build otherwise. **Bootstrap Icons 1.11** for all icons (`bi bi-*`).
- **Fonts (Google Fonts):** `IBM Plex Sans` (Latin) + `IBM Plex Sans Arabic` (Arabic), weights 400;500;600;700.
- **Fully bilingual ar/en with real RTL** via `<html dir="rtl|ltr" lang>`. Use logical CSS properties everywhere (`margin-inline`, `inset-inline-start`, `padding-inline`, `text-align:start`).
- One global stylesheet that defines the CSS variables in §3. Base font-size **14px**, antialiased, `body { overflow:hidden }` (the app scrolls internally, not the page).

### Exact `<head>` includes
```html
<meta name="csrf-token" content="...">
<!-- Bootstrap (pick by locale) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet"><!-- RTL -->
<!-- or bootstrap.min.css for LTR -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/app.css">
<!-- before </body>: -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/dropdown.js"></script><!-- themed select enhancer, see §7 -->
```

## 2) Typography
- Body: `'IBM Plex Sans','IBM Plex Sans Arabic',system-ui,sans-serif`; in RTL swap order so the Arabic face leads. 14px base, color `--ink`.
- Page **H1**: 24px / 700 / letter-spacing −.02em. Subtitle: 14px `--muted`.
- Card/section titles: 14px / 600. KPI value: 25px / 700 / −.02em. Stat value: 22px / 700.
- Small/meta text: 12–13px `--muted`.

## 3) Design tokens — paste verbatim into `:root`
```css
:root {
    --brand: #6d5ae6;      /* indigo accent: focus, active, links, progress */
    --brand-soft: #f1eefc; /* tint for focus ring / soft badges */
    --ink: #1a1d23;        /* primary text AND primary buttons */
    --ink-2: #3d424d;
    --muted: #8a909d;
    --muted-2: #aeb3bd;
    --line: #e8eaee;       /* hairline borders */
    --line-2: #f1f2f5;     /* lighter row dividers */
    --bg: #f5f6f8;         /* app/content background */
    --panel: #ffffff;
    --card: #ffffff;       /* surfaces */
    --hover: #f4f5f8;      /* hover background */
    --radius: 12px;
    --radius-sm: 9px;
    --radius-lg: 16px;
    --shadow-card: 0 1px 2px rgba(20, 20, 40, .035);
    --sidebar-w: 252px;

    /* Dark sidebar (navy) + indigo brand header */
    --sidebar: #171a2d;
    --sidebar-ink: #dfe2ec;
    --sidebar-muted: #8a8fa6;
    --sidebar-hover: #222640;
    --sidebar-active: #2b3052;
    --sidebar-line: #272b44;
    --sidebar-field: #1f2338;
    --brand-header: #4f46e5;

    /* Status palette — each color has a soft -bg tint */
    --green: #16a34a;  --green-bg: #effaf1;
    --blue: #2563eb;   --blue-bg: #eef3ff;
    --amber: #d97706;  --amber-bg: #fff7ea;
    --red: #e1364b;    --red-bg: #fdf1f2;
    --gray: #6b7280;   --gray-bg: #f2f1ec;
    --purple: #8b5cf6; --purple-bg: #f5f2ff;
}
```

## 4) App shell / layout
- `.layout`: flex row, `height:100vh`, `overflow:hidden`.
- **Sidebar** fixed `252px`, full height, dark navy `--sidebar`.
- **Main**: flex column = **topbar 58px** + **`.content`** (flex:1, `overflow-y:auto`, side padding 18px). Content blocks are centered with `max-width:1320px`.
- **Full-bleed pattern**: a `.full-bleed` block cancels the 18px side padding to span edge-to-edge (used for toolbars and table frames); `.sheet-aligned` re-insets content by 13px to line up with table cells.

## 5) Sidebar (dark navy)
- **Brand header**: `linear-gradient(135deg, #4f46e5, #6d5ae6)`, white, 700/17px; 30px rounded "brand-mark" tile at `rgba(255,255,255,.18)`.
- **Search** on dark: bg `--sidebar-field`, border `--sidebar-line`, 9px radius, muted placeholder, optional `⌘K` kbd hint.
- **Nav groups**: uppercase label 11px/600 `--sidebar-muted`. **Nav link**: 14px/500, 9px radius, gap 11px, icon 17px muted; hover `--sidebar-hover`+white; **active** `--sidebar-active`+white+600.
- **Footer user chip**: bordered (`--sidebar-line`) rounded row, avatar on translucent white, name white 13px + role muted.

## 6) Topbar
- 58px, `--card` bg, bottom `1px --line`, padding-inline 30px, space-between.
- **Icon buttons** `.btn-icon`: 36px square, 9px radius, `--muted`, hover `--hover`+`--ink`; red notification dot `--red` top-inline-end. Breadcrumb: muted with `--ink`/600 current.

## 7) Components (match exactly)

**Buttons**
- Primary `.btn-brand`: **`--ink` (near-black) bg, white text**, 9px radius, 13px/600, padding 9×15, gap 7; hover `#000`; active `translateY(1px)`. (Brand indigo is an *accent*, not the button color.)
- Secondary `.chip`: white, `1px --line`, text `--ink-2`, icon `--muted`, 9px radius, 13px/500; hover `--hover`.

**Cards** `.card`: white, `1px --line`, 12px radius, `--shadow-card`. `.card-head` 15–18px padding + bottom border, title 14px/600. `.card-body` 18px.

**KPI card**: 40px soft-bg icon tile in a status color (`.green/.blue/.amber/.red/.brand` → `*-bg` bg + solid color), value 25px/700, label 13px `--muted`. 4-up grid, responsive to 2-up / 1-up.

**Stat card**: two metrics split by `border-inline-start`; each: label 12px muted, value 22px/700; trend `.delta.up` (green-bg) / `.delta.down` (red-bg), fully rounded.

**Tables** `table.data`: 13×16 padding, header 12px/500 `--muted` + bottom border, rows divided by `--line-2`, row hover `--hover`; `.cell-strong` 600 ink, `.cell-muted` muted. **Sheet variant** `table.data.sheet`: denser (9×13), header bg `#f6f7f9` 600 `--ink-2`, wrapped in `.sheet-frame` (white, `1px --line`, 12px radius, overflow hidden, `--shadow-card`). Checkbox: 16px, 5px radius, `accent-color:var(--brand)`.

**Status pills** `.pill`: fully rounded (999px), white, `1px --line`, 12px/500, **colored icon by status** — active/paid→green, upcoming/unpaid→blue, opened→amber, completed/ignored→gray, cancelled/overdue→red.

**Soft badges** `.badge-soft`: filled rounded pill, `*-bg` background + matching solid text, 11px/600 — variants blue/green/amber/red/gray/brand/purple. Text-only `.tag` = colored 12px/600 label.

**Forms**: `.form-label` 13px/500 `--ink-2`. `.form-control/.form-select`: 9px radius, `1px --line`, 14px; **focus = `border-color:var(--brand)` + `box-shadow:0 0 0 3px var(--brand-soft)`** (this 3px brand-soft ring is the signature focus state — use it on every focusable field, search input, and themed-select button).

**Themed select (REQUIRED — never show the OS-native dropdown):** progressively enhance every `<select>` into a custom dropdown. Keep the real `<select>` in the DOM but visually hidden so forms still submit; render a themed button (white, `1px --line`, 9px radius, chevron caret that flips when open) and a **menu appended to `<body>` with `position:fixed`** (so it never clips inside tables/modals): white card, 12px radius, `box-shadow:0 10px 32px rgba(15,18,30,.14)`, 6px padding; options 8×12, 8px radius, hover `--gray-bg`, **active = `--brand-soft` bg + `--brand` text + 600**. On pick, set the native value and dispatch `input`+`change`. Close on outside-click / Esc / scroll / resize. Opt out with `data-no-enhance`; inline auto-width via class `ep-w-auto`. (Behavior matches `public/js/dropdown.js` — reuse it directly.)

**Tabs** `.tabs/.tab`: underline style; inactive `--muted`, hover `--ink`; **active = `--brand` text + 2px `--brand` bottom-border + 600**.

**Toolbar** `.toolbar`: white bar, bottom border, space-between; contains `.search-input` (pill: white, `1px --line`, 9px radius, muted leading icon, focus brand ring, min-width 230px) and a segmented `.seg` control (inset track on `--bg`, active button white + shadow + brand icon).

**Modals**: Bootstrap modal, `modal-dialog-centered`; header with title + `btn-close`; body 18px; footer with `.page-btn` (cancel) + `.btn-brand` (save).

**Pagination** `.pagination-bar`: top border; `.page-btn` bordered; `.page-num` 32px square, **active = `--ink` bg + white**.

**Avatars** `.avatar`: 34px rounded-square (10px radius), `--brand-soft` bg + `--brand` initials. Overlapping `.avatar-stack` with `-8px` overlap, white ring, colored variants (`#6d5ae6/#2563eb/#16a34a/#d97706`, `more`=gray).

**Gallery cards** (e.g. entity tiles) `.ex-card`: white bordered card; **cover header = 135° gradient keyed to status** (active `--brand`→`#9d8bff`, upcoming `#2563eb`→`#60a5fa`, completed `#64748b`→`#94a3b8`, cancelled `#e1364b`→`#f87171`); hover = lift `translateY(-2px)` + brand glow `0 8px 24px rgba(109,90,230,.12)`.

**Empty states**: centered muted text + an outline icon inside the table/card body (use `@forelse`-style fallbacks, never leave a bare empty container).

**Flash messages**: top toast — success `flash-ok` (green check) / error `flash-err` (red triangle), dismissible.

## 8) Interaction & detail conventions
- Transitions: `.15s` on background/color/border. Hover surfaces shift to `--hover`. Press = `translateY(1px)`.
- Radii: **9px** controls/inputs/buttons, **12px** cards/menus, **16px** large, **999px** pills.
- Shadows: card `0 1px 2px rgba(20,20,40,.035)`; popovers/menus `0 10px 32px rgba(15,18,30,.14)`; brand glow `0 8px 24px rgba(109,90,230,.12)`.
- Spacing rhythm: **18px** for grid gaps & card padding; **12–13px** for dense rows; grids `.grid-2/.grid-3` collapse to 1 column under 992px.

## 9) Color usage rules (do not deviate)
- **Indigo `--brand` (#6d5ae6)** = accents only: focus ring, active tab/nav, links emphasis, progress bars, brand icon tiles. **Primary buttons are near-black `--ink`, not indigo.**
- **Status semantics**: green=success/paid/active · blue=info/upcoming · amber=warning/opened · red=danger/overdue · gray=neutral/done · purple=secondary accent. Each status shows as either a `.pill` (outline + colored icon) or a `.badge-soft` (filled tint).
- **Surfaces**: app background `#f5f6f8`, cards/topbar white, hover `#f4f5f8`, hairlines `--line`. Only the sidebar is dark.
- Keep it restrained: most of the UI is white + gray + hairlines; color appears only for status and the brand accent.
```
