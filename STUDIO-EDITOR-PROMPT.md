# Studio Editor — kickoff prompt for a fresh chat

When you're ready to start the unified-editor project as its own focused
conversation, paste the prompt below into a new Claude Code session.
Everything between the `---` lines is self-contained — the new chat won't
have any context from this one.

---

I'd like to unify Mail Minted's fragmented studio editing into a single
cohesive page. Right now, editing what visitors see as ONE bio page
requires navigating between four destinations:

- `/studio/page` — display name, description, profile photo
- `/studio/appearance` — colors, fonts, background, button shape,
  avatar shape, social icon styling
- `/studio/social-icons` — the brand icon row
- `/studio/links` — blocks list + per-block edit at `/studio/edit-link/{id}`

Operator has to hold a mental map of "for X I go here, for Y I go there."
Block creation is also a two-step (add → edit) which compounds the
friction.

Goal: a single `/studio/edit` page that consolidates all of this with a
live preview always visible alongside.

## Context (please read these first)

- Working directory: `/Users/jameskoch/dev/LinkStack` — the LinkStack
  fork that powers Mail Minted's customer bio pages.
- `PLANNING.md` at the repo root is the authoritative spec for the
  broader Mail Minted project. Read it for product context.
- `UI-IMPROVEMENTS.md` item **10** lays out the goal in more detail
  and proposes two options. Read both options before deciding.
- Look at each of the four existing pages above so you understand what
  fields and controls they carry today.
- `resources/views/studio/partials/live-preview.blade.php` is the
  shared live-preview partial. Already used on `/studio/appearance`
  and `/studio/links`. Drop it into the new page too.

## Recommended approach (Option A from the spec)

A single page `/studio/edit` with tabs along the top:

1. **Basics** — port `/studio/page` content (display name,
   description, profile photo upload)
2. **Appearance** — port `/studio/appearance` content (the existing
   Profile / Colors / Background / Type / Buttons / Social icons
   sub-tabs collapse into this single tab — or stay as nested tabs)
3. **Social icons** — port `/studio/social-icons` content (the drag-
   to-reorder chip row + URL editor)
4. **Blocks** — port `/studio/links` content (blocks list with drag-
   to-reorder + Add Block + inline edit)

Live preview pinned on the right (use the existing
`studio/partials/live-preview.blade.php` partial), same way the
appearance editor already does it.

Key UX improvements within Option A:

- **Blocks tab: inline create.** Today, clicking "Add Block" → picking
  a type takes you through a modal then back to a list (the modal IS
  already inline, but the block then sits as a row that you have to
  click to fully configure). Within the new Blocks tab, picking a
  type from the Add picker should open that block's full edit form
  RIGHT THERE inline — no `/studio/edit-link/{id}` redirect.
- **Old URLs redirect into tabs.** `/studio/page` → `/studio/edit#basics`,
  `/studio/appearance` → `/studio/edit#appearance`, etc. So existing
  bookmarks / sidebar nav still works.
- **Sidebar nav can collapse too** — replace the four separate sidebar
  items with one "Edit page" item that opens the unified editor.

## How I'd like to work

I'm the sole operator of Mail Minted, not a developer. Walk me through
decisions and flag tradeoffs before making them. The existing pages have
forms wired to controller methods; the right approach is to PORT
content into tabs, not rewrite the forms. Reuse the existing form
fields, controllers, validation, and storage — just put them inside
tabs of one page.

## Suggested first steps

1. Read `PLANNING.md`, `UI-IMPROVEMENTS.md` item 10, and the four
   existing studio pages so you understand the surface area.
2. Sketch the proposed `/studio/edit` view structure (which tab gets
   which sections, where the live preview sits, how the save buttons
   work — one per tab or one global). Show me before implementing.
3. Decide how Block create-inline should work — likely a modal stays
   for picking the type, then the form renders into a slot inside the
   Blocks tab rather than redirecting to `/studio/edit-link/{id}`.
4. Plan the URL-redirect strategy for the old pages so no muscle
   memory gets broken.

## Out of scope

- Theme picker / theme library work — that shipped already (47 themes
  via `theme-toolkit/`); see the theme-related commits and
  `THEME-LIBRARY-PROMPT.md` for context if needed.
- Anything else in `UI-IMPROVEMENTS.md` — Items 1-9 are done. Stay
  focused on Item 10.
- Option B (Wix-style visual page builder) — if you find Option A
  has surface area you want to reconsider, flag it but don't pivot
  without my OK.

Don't start coding yet. Read the context first, then come back with
the proposed view structure + how block create-inline works + the
URL-redirect plan, so we can align before any files get changed.

---

(End of prompt. Anything below this line stays in this file, not in the
new chat.)

## Why a separate chat

The current Mail Minted chat has accumulated a lot of Pass 1-4 context
that's no longer load-bearing for this work. The unified editor is its
own ~1-2 day refactor that touches many files. Splitting keeps both
chats focused.
