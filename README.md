# Trip Itinerary

Gutenberg blocks for building day-by-day travel itineraries in WordPress.

An **Itinerary** holds **Days**, and each Day holds its own content. On the page they render as a vertical timeline that takes its colours from the active theme.

## Blocks

| Block | Purpose |
|---|---|
| `trip-itinerary/itinerary` | The trip. Has a title and contains Day blocks only. |
| `trip-itinerary/day` | One day. Has a title and contains paragraphs, headings, lists, images, quotes and buttons. Can only be placed inside an Itinerary. |

## Development

Requires Node.js, Yarn and Docker.

```bash
yarn install
yarn build
yarn wp-env start
```

WordPress runs at http://localhost:8888 (log in at `/wp-admin` with `admin` / `password`), with this plugin active.

```bash
yarn start
```

Rebuilds the blocks whenever a file in `src/` changes.

## Roadmap

- [x] Itinerary and Day blocks with nested content
- [ ] Stop block (flight, hotel, activity) with sidebar settings
- [ ] Block context: day numbers and dates from the trip's start date
- [ ] Dynamic "Trip at a glance" summary rendered in PHP
- [ ] Collapsible days and a checklist with the Interactivity API
- [ ] Patterns, variations and transforms
- [ ] Deprecations, end-to-end tests and CI
