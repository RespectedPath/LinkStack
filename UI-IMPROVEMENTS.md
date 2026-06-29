# UI / editing improvements — running list

A running backlog of studio UX issues to address as one cohesive pass rather
than piecemeal. Once the list feels complete, we'll group, prioritize, and
plan a single design-aligned implementation so everything flows together.

---

## Open

1. **Social icon colors — no editor control.**
   Currently hardcoded in `assets/linkstack/css/skeleton-auto.css`: white
   on dark page bg, dark grey on light bg (auto-contrast). No way for the
   operator to pick a custom color, switch to actual brand colors
   (Instagram pink / FB blue / etc.), or override the default behavior.

   _Candidate fix:_ add a control to `appearance.blade.php` with three
   modes — Auto contrast (current) / Brand colors / Custom color picker.

   _Added 2026-06-29._

2. **Button editor is a "monstrosity" — should not be a separate page.**
   `/studio/button-editor/{id}` is LinkStack's legacy per-block CSS
   editor: 288 lines, raw CSS code editor with syntax highlighting
   (rainbow.js), jQuery UI gradient picker, color picker, etc. Reached
   from the blocks list via a small green "Customize" icon, separate
   from the Edit button. Two pages for one block — content lives at
   `/studio/edit-link/{id}`, styling lives at button-editor.

   Pain points:
   - User doesn't know what they're looking at when they land there
   - Style options are inaccessible during block creation — you have
     to save the block first, navigate back, click Customize
   - Splitting "Edit" and "Customize" forces the operator to context-
     switch for what feels like one task: configuring a block
   - The raw-CSS surface is developer-grade, not user-friendly

   _Candidate fix (direction, not final):_
   - Unify `/studio/edit-link/{id}` and `/studio/button-editor/{id}`
     into a single page with clearly labelled sections — **Content**
     (current edit form) / **Appearance** (simple controls: bg color,
     text color, border radius, icon) / **Settings** (collapsed flag,
     enabled toggle, etc.). One Save button covers all sections.
   - Replace the raw-CSS editor with form controls; generate the CSS
     server-side from the picked values. Keep an "Advanced custom
     CSS" expander if the power-user surface needs to stay reachable.
   - Show the same Appearance section during create — pre-filled with
     sensible defaults — so the operator can style as they go.
   - Add a "← Back to Blocks" link at the top of both create and
     edit pages so navigation home is one click.

   _Added 2026-06-29._

3. **No "back to Blocks list" nav from the create/edit pages.**
   Once on `/studio/add-link` or `/studio/edit-link/{id}`, the only
   way back is the sidebar Blocks link. Should have a visible
   in-page link / breadcrumb.

   _Added 2026-06-29._

4. **Edit and Customize are separate pages — should be one.**
   See item 2. Calling out explicitly because this is its own
   navigational pain (two icons per block row, two destinations,
   two save buttons), separate from the button-editor's internal
   UX issues.

   _Added 2026-06-29._

5. **Block widths are inconsistent across types.**
   Different block types render at different widths on the public
   bio page. LinkStack's stock link/vcard buttons are hardcoded to
   ~300px (`assets/linkstack/css/brands.css:63 — width: 300px`),
   while custom blocks built for Mail Minted (Buy Me a Coffee,
   Stripe payment, contact form, newsletter signup, etc.) and
   embedded blocks (YouTube, Twitch, Spotify) each picked their
   own dimensions in their own `blocks/<typename>/display.blade.php`.

   Pain: the column of blocks looks misaligned — narrow links
   next to wider tip jars next to even-wider video embeds.

   _Candidate fix:_
   - Define one shared block-container width as a CSS variable
     (e.g. `--block-max-width: clamp(280px, 90vw, 480px)`) so it
     scales fluidly between mobile and desktop.
   - Standardize every block's outer wrapper on that width — either
     via a single wrapper class added in
     `resources/views/linkstack/elements/buttons.blade.php` (the
     loop that renders each block) or by retrofitting each
     `display.blade.php` to use it.
   - Embedded blocks (video/audio iframes) need width + aspect-
     ratio handling so they scale proportionally rather than
     locking to a fixed pixel height. CSS `aspect-ratio: 16/9` on
     the iframe wrapper with `width: 100%` solves this.

   _Added 2026-06-29._

6. **My Blocks page preview is uglier than the Appearance preview.**
   `/studio/links` shows a bare iframe — hardcoded inline styles,
   white background, a generic `bi-window-fullscreen` "Preview"
   heading. Two near-identical iframes for desktop/mobile.

   `/studio/appearance` shows a polished `appearance-preview` aside
   with a phone-icon header "Live preview" and an
   `appearance-preview-frame` that's styled like a device mock-up.

   _Candidate fix:_ extract the Appearance page's preview into a
   reusable partial (`studio/partials/live-preview.blade.php`) and
   drop the raw iframe on My Blocks. Use the partial anywhere the
   operator should see what visitors see — My Blocks, the unified
   block edit page from item 2, anywhere else useful.

   _Added 2026-06-29._

7. **Spacer block — value has no units and applies a hidden 5× multiplier.**
   `blocks/spacer/form.blade.php` is a `<input type="range">` slider
   with default value 5, no min/max, no unit label, and no live
   visual preview. `blocks/spacer/display.blade.php` then renders
   `height: {{$link->title * 5}}px` — so the saved value is NOT
   pixels; the renderer secretly multiplies by 5 (slider value 5 =
   25 px on the page, value 20 = 100 px).

   On the My Blocks list page, the spacer row just shows "5" with no
   context — operator has no way to remember what they set, and the
   number doesn't even correspond to the actual pixel height.

   _Candidate fix:_
   - Form: replace the unit-less slider with a labelled control —
     either a small preset selector (Small / Medium / Large / XL —
     mapped to e.g. 16/32/64/128 px), or a number input + visible
     "px" suffix with a sane min/max (8–200).
   - Store the actual pixel value (drop the ×5 multiplier in the
     display blade) so the saved number is meaningful.
   - On the block list, show "Spacer (32 px)" instead of just "5".

   _Added 2026-06-29._

8. **Text block — extra whitespace below the rendered text.**
   `blocks/text/display.blade.php` is just `<div class="fadein"><span>
   {{ $link->title }}</span></div>`, no margin in the block itself.
   The 10–15 px gap below the text comes from elsewhere — likely the
   parent `.button-spacer` wrapper, a `.fadein` margin rule, or
   default `<div>` block spacing.

   Result: the text doesn't sit centered in its visual slot — there's
   a noticeable buffer below it.

   _Candidate fix:_
   - Audit the parent wrappers `linkstack/elements/buttons.blade.php`
     adds around each block.
   - Either zero the extra margin specifically for text blocks (so the
     text is flush in its space), or wrap the text in a container with
     symmetric vertical padding so it's centered in whatever slot it
     ends up in.
   - Consider giving the text block its own optional alignment
     control (left / center / right) at the same time since we'll be
     in there.

   _Added 2026-06-29._

9. **Theme library — build profession-targeted starter themes.**
   LinkStack ships with a small theme library (`themes/` dir at the
   repo root — currently has just PolySleek + galaxy). Operators can
   download more from the LinkStack site but the catalog is thin and
   generic. Mail Minted's target users will likely want a near-zero-
   config starting point that matches their profession or vibe.

   Goal: ship ~15–24 in-house themes named for common use cases so a
   new customer can pick one in a single click and be visually
   "done" without touching the appearance editor at all.

   Profession / style buckets to cover:
   - Service trades: plumber, mechanic, electrician, contractor
   - Beauty & wellness: hairstylist, esthetician, nail tech, makeup
     artist, yoga instructor, massage therapist, personal trainer
   - Food & drink: chef / restaurant, baker, barista, food truck
   - Creative: photographer, musician, artist, tattoo artist,
     designer, writer
   - Professional services: lawyer, realtor, accountant, therapist /
     coach, consultant
   - Lifestyle / influencer: lifestyle blogger, fitness influencer,
     travel creator
   - Special interest: pet groomer / vet, florist, wedding planner,
     event planner

   Theme structure (per LinkStack convention — `themes/<name>/`):
   - `config.php` — metadata (display name, description, tags)
   - `preview.png` — thumbnail shown in the picker
   - `readme.md` — short blurb
   - `skeleton-auto.css`, `brands.css`, `animations.css`,
     `share.button.css` — the CSS overrides that give the theme its
     personality (palette, typography, button shape/treatment,
     subtle background pattern or gradient)

   _Candidate fix:_
   - Define a small "design system" — palette + font + button shape
     + background style — so every theme is built from the same
     variable schema. Keeps the catalog cohesive instead of looking
     like 24 random hand-drawn designs.
   - Maybe generate preview PNGs programmatically from the theme's
     palette/font (small Node or headless-browser script) rather
     than hand-mocking each one — keeps the work tractable.
   - Update `/studio/theme` picker UI to group themes by category
     (Trade / Beauty / Food / Creative / Professional / Lifestyle)
     and make profession-named themes searchable.
   - Bigger scope than other items — likely a multi-day project of
     its own. Worth carving out as a dedicated sprint rather than
     bundling into the broader UX pass.

   _Added 2026-06-29._

---

## In progress

- **Item 9 — Theme library** — split into its own focused chat session
  per `THEME-LIBRARY-PROMPT.md`. Started 2026-06-29.

---

## Done

_(items move here once shipped)_
