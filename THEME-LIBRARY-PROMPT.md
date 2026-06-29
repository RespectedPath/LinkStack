# Theme Library — kickoff prompt for a fresh chat

When you're ready to start the theme-library project as its own focused
conversation, paste the prompt below into a new Claude Code session.
Everything between the `---` lines is self-contained — the new chat won't
have any context from this one.

---

I'd like to build a library of profession-targeted starter themes for Mail
Minted (a LinkStack fork). Goal: ~15-24 in-house themes named for common
use cases so a new customer can pick one in a single click and be visually
"done" without touching the appearance editor.

## Context (please read these first)

- Working directory: `/Users/jameskoch/dev/LinkStack` — the LinkStack fork
  that powers Mail Minted's customer bio pages.
- `PLANNING.md` at the repo root is the authoritative spec for the broader
  Mail Minted project. Read it for product context.
- `UI-IMPROVEMENTS.md` item **9** lays out the theme-library goal in
  detail, including the profession buckets to cover (service trades,
  beauty & wellness, food & drink, creative, professional services,
  lifestyle, special interest) and design-system thoughts.
- `themes/` at the repo root is where themes live. Look at the two that
  already exist — `themes/PolySleek/` and `themes/galaxy/` — to understand
  the file structure LinkStack expects. Each theme needs:
  - `config.php` — metadata
  - `preview.png` — thumbnail for the picker
  - `readme.md` — short blurb
  - `skeleton-auto.css`, `brands.css`, `animations.css`,
    `share.button.css` — the actual styling overrides

## How I'd like to work

I'm the sole operator of Mail Minted, not a developer. Walk me through
decisions and flag tradeoffs before making them. I want to give meaningful
input on the design system (palette schema, font choices, button shapes)
since the catalog needs to feel cohesive — not 24 hand-drawn one-offs.

## Suggested first steps

1. Read `PLANNING.md`, `UI-IMPROVEMENTS.md` item 9, and the two existing
   themes in `themes/` so you understand the codebase + the goal.
2. Propose a small design system: 3-5 palette archetypes, 3-4 font pairings,
   3 button-shape options. Show me before writing any CSS.
3. Suggest a way to generate `preview.png` thumbnails programmatically
   (headless Chrome / Puppeteer / Playwright?) — hand-mocking 24 previews
   isn't worth the time.
4. Plan a build order — start with 4-5 representative themes across
   different professions to validate the system before scaling to all 24.
5. Update the `/studio/theme` picker UI (`resources/views/studio/theme.blade.php`)
   to group themes by category and make profession-named ones discoverable.

## Out of scope

- Other items in `UI-IMPROVEMENTS.md` — those are being handled in a
  separate UX pass. Stay focused on themes.
- Theme-editor / theme-customization UI for end users — that's a future
  project. Just build the library + the picker for now.

Don't start coding yet. Read the context first, then come back with the
design-system proposal and the build order so we can align before any
files get created.

---

(End of prompt. Anything below this line stays in this file, not in the
new chat.)

## Why a separate chat

The main Mail Minted UX pass needs the chat context to span across many
small studio fixes (button editor unification, block widths, preview
extraction, etc.). Theme work is multi-day CSS design work that would
clog that context with palette swatches, font samples, and preview-
generation scripts. Splitting them keeps both chats sharp.
