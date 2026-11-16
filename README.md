# Trip Itinerary

Gutenberg blocks for building day-by-day travel itineraries in WordPress.

An **Itinerary** holds a sticky **Trip header**, a **Trip overview**, **Days** and a **Trip price**, with a **Trip footer** below. Each day gets a numbered circle on the timeline and a date counted from the trip's start, can have a location, and shows a stacked photo carousel below its content. Colours come from the active theme, with an accent colour themes can change through `--trip-accent`.

## Blocks

| Block | Purpose |
|---|---|
| `trip-itinerary/itinerary` | The trip: a title, a start date and the number of travellers (in the block sidebar). Holds the blocks below, except the footer. |
| `trip-itinerary/header` | A bar that stays at the top while scrolling, with a pill for each place the trip visits that jumps to the first day there, and a button that scrolls to the price unless it has its own link. |
| `trip-itinerary/overview` | The trip at a glance: its dates, length, travellers and starting place over an optional photo of the country, then each stop, one below the other, beside a map numbering the same stops. Calculated from the days, not typed. |
| `trip-itinerary/day` | One day: a title, a location with a map position ("Find on map" looks it up), any content (paragraphs, headings, lists, images, quotes, buttons) and photos from the media library. |
| `trip-itinerary/price` | The price per person, a note, what's included and excluded (core List blocks with ✓ and ✗ block styles), and a request button. Highlighted when the header's button scrolls to it. |
| `trip-itinerary/footer` | A logo linking home (or the site's logo, or its name), a row of links and a credit line. Can go anywhere. |

### Generic components

| Component | Purpose |
|---|---|
| Button styles | Primary, Primary outline, Secondary, Secondary dark, Tertiary and Quaternary, for the core Button block, in its Styles panel. |
| `trip-itinerary/field` | A labelled input for text, email, phone or a password, which gets a button to show what was typed (Interactivity API). |
| `trip-itinerary/card` | A card for the trip on another page: photo, route, dates, travellers and buttons, read from that page's itinerary. The editor previews it with `ServerSideRender`. |
| `trip-itinerary/page-header` | A logo linking home, links and a button on a translucent bar that can stay at the top, with room for the notifications bell and user menu. On phones the links fold into a menu; without JavaScript they stay visible. |
| `trip-itinerary/notifications` | A bell with an unread count that opens a panel of notifications, each a `trip-itinerary/notification` block. Each visitor's browser remembers what they've read (localStorage); click outside or press Escape to close. |
| `trip-itinerary/user-menu` | For logged-in visitors, their initial, opening a menu with their name, email, links and WordPress's log-out link. Logged-out visitors get a Log in button. Personal, so pages with it shouldn't be cached for logged-in visitors. |

The Itinerary also has a **Summary only** style, which keeps the days' data for the header, overview and price but doesn't show the days: for showing a trip's summary on another page.

The demo's **Components** page (`/components/`) shows each of these on its own.

## Design

The colours, buttons, inputs and font follow the Offtryp design. The font is [Montserrat](https://github.com/JulietaUla/Montserrat) (SIL Open Font License), served with the plugin rather than from Google, in Latin and Latin Extended subsets (č, ć, š, ž, đ). The plugin's blocks always use it; the rest of the site only when the `trip_itinerary_site_font` option is on, which the demo turns on. Design tokens are CSS custom properties in `assets/ui.css`.

## How it works

- **Server rendering.** The blocks save only their content; `render.php` templates produce the HTML. The Itinerary renders its children itself, so it can give each Day its position and anchor, the overview every day's location, and the header the stops and the price's anchor.
- **No JavaScript where links will do.** The header's stops and button are plain anchor links. CSS adds smooth scrolling, keeps days clear of the sticky header, and highlights the price with `:target`.
- **Block context.** The start date and travellers are shared with child blocks through block context, in the editor and on the server.
- **Same logic on both sides.** Dates and the overview are calculated in JS for the editor (`src/shared`) and in PHP for the site (`includes`). Tests check both against the same cases. Dates are handled in UTC so a time zone never shifts a day.
- **Map.** The overview's map uses [Leaflet](https://leafletjs.com/) with OpenStreetMap tiles, so it needs no API key. That suits a demo or a small site; for a busy one, point the `trip_itinerary_map_tiles` filter at your own tile server or a provider. Markers that would overlap are nudged apart. "Find on map" uses OpenStreetMap's Nominatim search, only in the editor.
- **Interactivity API.** The photo carousel is rendered in PHP and made interactive with a small store (`src/day/view.js`): arrows, dots, clicking a side photo and arrow keys.
- **Deprecations.** Content saved by version 0.1 (static markup) still works: the editor upgrades it through the blocks' `deprecated` entries, and the site shows it as saved until then.

## Development

Requires Node.js, Yarn and Docker.

```bash
yarn install
yarn build
yarn wp-env start
```

WordPress runs at http://localhost:8888 (log in at `/wp-admin` with `admin` / `password`), with this plugin active. On start, `bin/setup-demo.php` imports the demo photos and creates a demo trip from `demo/trip.json` as the homepage, and a Components page at `/components/`. `yarn demo:reset` rebuilds both.

```bash
yarn start
```

Rebuilds the blocks whenever a file in `src/` changes.

The demo photos and logo are generated placeholders.

## Tests

```bash
yarn test:php:setup   # once: installs PHPUnit inside wp-env
yarn test
```

| Command | What it runs |
|---|---|
| `yarn test:unit` | Jest tests for the shared date, overview, stops and carousel logic, with the clock and site set to different time zones. |
| `yarn test:php` | PHPUnit tests inside wp-env that render real blocks through WordPress: labels, overview, carousel, header links, price, footer fallbacks, escaping, and 0.1 content. |
| `yarn test:e2e` | Playwright tests on the wp-env test site: building a trip in the editor, the header's links and button, the carousel, and upgrading 0.1 content. |

PHPUnit uses its own `phpunit_` database tables, so it doesn't reset the site the end-to-end tests use.

## Roadmap

- [x] Itinerary and Day blocks with nested content
- [x] Block context: day numbers and dates from the trip's start date
- [x] Trip overview rendered in PHP
- [x] Photo carousel with the Interactivity API
- [x] Trip header, price and footer
- [x] Map of the stops, country photo
- [x] Generic components: buttons, fields, itinerary card, page header, notifications, user menu
- [x] Deprecations and tests (unit, PHP, end-to-end)
- [ ] Stop block (flight, hotel, activity) with sidebar settings
- [ ] Patterns, variations and transforms
- [ ] CI on GitHub Actions
