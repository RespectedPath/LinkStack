# UI / editing pass — proposed plan (v2)

A plan to address items 1–8 in `UI-IMPROVEMENTS.md`. Item 9 (theme library)
is split into its own project per `THEME-LIBRARY-PROMPT.md`.

Four passes. Each ships independently. We can pause, review, and adjust
between passes. Mail Minted is pre-launch with no real customer data, so
breaking changes during a pass are recoverable — no migrations needed
just to keep existing users' configurations alive.

---

## Pass 1 — Public-page visual consistency

Fixes how the bio page **looks** for visitors. Touches the rendering
layer and a handful of block templates. Goes first because everything
else (preview partial, edit-flow redesign) becomes easier once blocks
render consistently.

**Items addressed:** 5 (block widths), 7 (spacer units), 8 (text whitespace)

**What changes:**

- **Block widths (item 5).** Define a CSS variable `--block-max-width:
  clamp(280px, 90vw, 480px)` so every block fits a consistent column
  width that scales with screen size. Apply it via one wrapper class
  added in `resources/views/linkstack/elements/buttons.blade.php`. For
  iframe blocks (YouTube, Twitch, Spotify), add `aspect-ratio: 16/9` +
  `width: 100%` so they scale proportionally instead of locking to
  fixed pixel heights.

- **Spacer block (item 7).** Replace the unit-less slider with a number
  input + visible "px" suffix (min 8, max 200, step 4). Drop the secret
  ×5 multiplier in the display blade — the saved value becomes actual
  pixels. On the My Blocks list, show "Spacer (32 px)" instead of just
  "5". Any existing spacer blocks in the local dev DB will visually
  shrink (since 5 means 5 px now instead of 25 px); not a concern — no
  real customers, easy to retype the value.

- **Text block (item 8).** Audit and zero the extra whitespace below
  text. Wrap text in a container with symmetric vertical padding so it
  sits centered. Add a small alignment control (left / center / right)
  while we're in there.

**Validation:**
- Public bio page with mix of block types (link, vcard, video, BMC,
  Stripe) all render at the same width
- Resize browser from 320 px to 1440 px — all blocks scale fluidly
- Spacer values entered as "32" actually produce 32 px gaps
- Text blocks have no asymmetric padding below

**Estimated scope:** ~half a day. Mostly CSS + small template edits.

---

## Pass 2 — Live preview partial

Quick win, low risk. Best after Pass 1 so the preview shows the new
visual consistency.

**Items addressed:** 6 (My Blocks preview is uglier than Appearance)

**What changes:**

- Extract `/studio/appearance.blade.php`'s `.appearance-preview` aside
  into a reusable partial at
  `resources/views/studio/partials/live-preview.blade.php`.
- Replace the raw iframe on `/studio/links` (My Blocks page) with this
  partial.
- Reserve the partial for use in Pass 3's new unified block edit page
  too — same polished phone-mockup preview everywhere.

**Validation:**
- `/studio/links`, `/studio/appearance`, and the future edit page all
  show the same phone-frame preview
- Editing a block reflects in the preview on its next reload

**Estimated scope:** ~1–2 hours. Pure extraction.

---

## Pass 3 — Block edit/create flow consolidation

The biggest pass. Unifies Edit + Customize into one page, cleans up
navigation, and **expands** customization power rather than reducing it.
The current button-editor's problem isn't "too much power" — it's that
the power is hidden behind a clunky standalone page with a raw CSS
textbox. We're keeping all that capability and adding more, just laid
out so it's discoverable.

**Items addressed:** 2 (button editor monstrosity), 3 (no back nav),
4 (edit + customize separate)

**What changes:**

- **Unified edit page.** `/studio/edit-link/{id}` becomes a single page
  with three labelled sections:
  - **Content** — the current type-specific form (title, URL, etc.)
  - **Appearance** — three-layer structure described below
  - **Settings** — collapsed toggle, enabled flag, etc.
  Single Save button covers all three sections.

- **Appearance section — three-layer customization** (the heart of
  this pass):

  | Layer | What it gives the user |
  |---|---|
  | **Style presets** | Gallery of pre-designed looks — Filled, Outlined, Gradient, Glass, Neon, Embossed, Ghost, 3D, Soft Shadow. Click one to start from a vibe. Each preset is just a saved CSS bundle — the user can tweak it after picking. |
  | **Visual controls (depth)** | Background (solid color, gradient builder with 2-3 stops, image upload), text (color, font from Google Fonts list, size, weight, transform, letter-spacing), border (width, color, radius — each corner individually), shadow (blur, color, offset), padding, hover effects (color shift, scale, slide), icon position (left/right/none) and size |
  | **Advanced custom CSS** | The current raw-CSS editor's full power, but inside the unified edit page as an expander rather than its own URL. Same syntax-highlighted textarea, same gradient picker, same color picker — just integrated. Power users keep everything they have today. |

  So flow is: pick a preset to start → tweak common stuff with visual
  controls → drop into raw CSS for fine-grained tweaks. Each layer adds
  to the one above; nothing is lost compared to today.

- **Show Appearance during create.** `/studio/add-link` gets the same
  three sections, pre-filled with sensible defaults (a default style
  preset, brand-friendly colors). Operator can style as they go
  instead of saving first then navigating to Customize.

- **Back to Blocks nav.** Visible "← Back to Blocks" link at the top of
  both create and edit pages.

- **Block list cleanup.** The green Customize icon goes away — Edit is
  now the only action that opens the configurator (which itself covers
  what Customize used to do).

- **Legacy route handling.** `/studio/button-editor/{id}` either
  redirects to `/studio/edit-link/{id}#appearance` (jumps to the
  Appearance section) or 404s with a friendly message. Recommend
  redirect for muscle memory.

**Decisions / open questions before coding:**

- _Style preset list — how many to ship in v1?_ Recommend 8–10 named
  presets. Glass and Neon especially are eye-catching for influencer
  bio pages.
- _Font list scope._ Top 20–30 Google Fonts is enough; we can expand
  later. Stick to common pairings (Inter, Poppins, Montserrat, etc.).
- _Hover effects palette._ Recommend: None / Lift (subtle Y translate)
  / Glow (box-shadow expand) / Color shift / Scale up. Five options
  covers the common patterns without overwhelming.
- _How to store the layered settings._ Easiest: one JSON column on the
  link row holding the style-preset id + the visual-control overrides;
  the raw CSS gets stored as it does today. Render-time merges preset
  CSS → visual-control overrides → raw CSS for final output.

**Validation:**
- Create a new block → Content, Appearance, Settings all populate with
  defaults; pick a preset → live preview updates
- Save with all three sections filled → block appears on bio page with
  chosen styling
- Edit an existing block → all three sections show current values,
  including any raw CSS overrides
- "Back to Blocks" link works from every state
- Old button-editor URL redirects to new edit page's Appearance section

**Estimated scope:** 2–3 days. Building out the visual controls and the
preset gallery is where most of the time goes; the unification itself
is mechanical.

---

## Pass 4 — Social icon customization

More than just colors. The social icons row is currently fixed at 32 px
glyphs with auto-contrast color and no spacing/background/hover control.
Expanding the surface so it actually feels customizable.

**Items addressed:** 1 (social icon colors) — expanded to cover more
than just color

**What changes:**

Add a "Social icons" panel to `/studio/appearance` with five controls:

| Control | Options |
|---|---|
| **Color** | Auto contrast (current default — black on light bg, white on dark) / Brand colors (each icon in its own real brand hex — Instagram pink, FB blue, YouTube red, etc.) / Custom color (single picker; all icons take the chosen hex) |
| **Size** | Small (24 px) / Medium (32 px — current) / Large (40 px) / XL (48 px) |
| **Spacing** | Tight (4 px gap) / Normal (8 px gap — current) / Loose (16 px gap) |
| **Background style** | None (flat icons — current) / Circle (each icon inside a circular chip) / Rounded square / Solid circle (filled brand color background, white glyph) |
| **Hover effect** | None / Lift / Glow / Color shift / Scale up |

Each setting saves to the user's appearance JSON (same column as the
existing color/typography settings). Rendered as CSS variables on the
public bio page so changes are live without touching individual icon
markup.

**Decisions to confirm:**
- _Default color mode for new users._ Recommend Brand colors as the
  default — they look more polished and instantly recognizable
  compared to monochrome.
- _Background style "Solid circle" interaction with custom colors._
  If user picks Solid circle, the circle background comes from each
  icon's brand color regardless of the Color setting (otherwise you'd
  get e.g. a black-background-with-black-glyph). Need to think
  through this combination cleanly.

**Validation:**
- Toggle through each color mode; public page reflects each
- Resize Size/Spacing controls and see proportional changes
- Background style Circle shows colored chips around each glyph
- Hover effects work on touch devices too (or degrade gracefully)

**Estimated scope:** ~half a day. Five controls + CSS variable render
logic.

---

## Recommended order

1. **Pass 1** (visual consistency) — foundational; everything else looks
   right because of this
2. **Pass 2** (preview partial) — quick win; reusable in Pass 3
3. **Pass 3** (edit flow + expanded customization) — biggest impact,
   biggest scope; do it when blocks look right and preview is unified
4. **Pass 4** (social icon customization) — light add-on; could even go
   before Pass 3 if you want a visible win while Pass 3 is being planned

**Total estimated scope:** 3.5–4 days end-to-end. Each pass commits
independently so we can pause anywhere.

---

## What I'd like you to review

- The overall grouping and order — does it match how you want to receive
  the changes?
- **Pass 3 — the three-layer Appearance structure.** Does Style Presets
  → Visual Controls → Advanced CSS feel right? Anything to add or cut
  per layer?
- **Pass 3 — preset list.** Filled, Outlined, Gradient, Glass, Neon,
  Embossed, Ghost, 3D, Soft Shadow. Which to include in v1, which to
  skip, anything missing?
- **Pass 4 — five controls.** Color / Size / Spacing / Background style /
  Hover. Anything you want added or removed?
- **Pass 4 default** — should new users start in Brand colors or Auto
  contrast?

Once you've signed off, I'll start with Pass 1.
