# DMG Read More

A WordPress plugin providing:

- A block that inserts a "Read More" link to another published post, selected via a search panel in the block sidebar.
- A WP-CLI command that lists the IDs of published posts containing that block within a date range.

Requires WordPress 6.7+ and PHP 8.2+.

## Installation

`vendor/` and `build/` are not committed. Run all three steps before activating the plugin — the bootstrap requires Composer's autoloader, so activating without `vendor/` is a fatal error.

```bash
composer install
npm install
npm run build
```

## The block

Add the **DMG Read More** block, then choose a post in the block settings sidebar. Until one is chosen the block shows a placeholder and outputs nothing.

The search panel:

- Searches posts by keyword, or by ID if the term is entirely numeric.
- Lists recent published posts when the box is empty.
- Paginates ten at a time.
- Debounces input by 250ms.

Output:

<!-- prettier-ignore -->
```html
<p class="dmg-read-more"><a href="https://example.com/target-post/">Read More: The target post's title</a></p>
```

## The WP-CLI command

```
wp dmg-read-more search [--date-after=<date>] [--date-before=<date>]
```

| Option          | Default     |
| --------------- | ----------- |
| `--date-after`  | 30 days ago |
| `--date-before` | now         |

Both accept any string `strtotime()` can parse. Post IDs are written to STDOUT, one per line. If no posts match, a warning is written to STDERR and the command exits successfully. Unparseable or reversed dates are errors.

```bash
wp dmg-read-more search
wp dmg-read-more search --date-after=2026-01-01 --date-before=2026-02-01
wp dmg-read-more search --date-after="6 months ago"
```

Use ISO dates (`Y-m-d`). `strtotime()` reads slash-separated dates as American `m/d/y`, so `01/02/2026` is 2 January, not 1 February. Dates are interpreted in the site's timezone.

## Implementation notes

### Static block

The block serialises its markup into `post_content` rather than rendering in PHP on each request, avoiding a render and a `get_post()` per view.

The cost is that the stored title and URL can go stale. A changed slug self-heals via `_wp_old_slug`; a deleted post gives a 404 that a link checker will catch; a retitled post keeps its old anchor text. Storing only the post ID and resolving at render time would fix this, at the cost of making the block dynamic.

The choice does not affect the CLI command: a dynamic block still serialises its delimiter comment into `post_content`.

### Query performance

The command searches for the block name `wp:dmg/read-more`, not the CSS class. The block name is present under both static and dynamic rendering, cannot be produced accidentally by hand-written HTML, and is not subject to cosmetic renaming.

A leading-wildcard `LIKE` cannot use an index, so the date range is what makes the query viable. `wp_posts` has a composite index on `( post_type, post_status, post_date, ID )`; the query supplies all three leading columns, so the content match runs only against rows the index has already narrowed.

| `WP_Query` argument                   | Purpose                                                        |
| ------------------------------------- | -------------------------------------------------------------- |
| `'fields' => 'ids'`                   | Selects `ID` only, rather than hydrating a `WP_Post` per row   |
| `'no_found_rows' => true`             | Skips the `SQL_CALC_FOUND_ROWS` companion query                |
| `'posts_per_page' => 500`             | Bounded rather than `-1`, keeping memory flat                  |
| `'ignore_sticky_posts' => true`       | Prevents sticky posts being prepended and breaking ID ordering |
| `'orderby' => 'ID', 'order' => 'ASC'` | Stable, index-backed ordering for batching                     |
| `'update_post_meta_cache' => false`   | Redundant under `'fields' => 'ids'`; retained as intent        |
| `'update_post_term_cache' => false`   | As above                                                       |

Results are fetched in batches until a batch returns fewer rows than requested, and each batch is written to STDOUT as it arrives. A single capped query would truncate silently; accumulating all results would defeat the bounded page size.

At the scale described in the brief, a search index such as ElasticPress is the appropriate tool rather than any `LIKE` query.

## Limitations

- **Batching uses offset pagination.** Offset requires the database to walk and discard skipped rows, so cost grows with page depth. Keyset pagination — tracking the highest ID returned and filtering on `ID >` it — avoids this, but requires a `posts_where` filter as `WP_Query` has no native `post__gt`.
- **The editor's Next button can overshoot by one page.** `getEntityRecords` does not expose `X-WP-TotalPages`, so the last page is inferred from a short result count. When the total divides exactly by the page size, Next remains enabled on the final page. Previous still works from the empty page.
- **`Read More: ` is not translatable.** A static block's `save()` output is serialised at save time, so a translation would resolve in the editor's locale, never the visitor's, and would break block validation if the site locale changed. The editor interface is fully translated.

## Development

```bash
npm start          # watch build
npm run build      # production build
npm run lint:js
npm run lint:css
composer lint      # PHPCS: WordPress-Extra + WordPress-Docs
composer lint:fix  # PHPCBF
composer test      # PHPUnit
```

Tests are plain PHPUnit with no WordPress bootstrap, covering `DateRange` — the class that parses and validates the date options. It is unit-testable because it contains no WordPress or WP-CLI calls. The remainder of the command is a thin layer over `WP_Query` and would require integration tests.

CI runs on every pull request: `composer validate --strict` and PHPCS; PHPUnit on PHP 8.2 and 8.4; ESLint, stylelint and a production build from a clean checkout. Composer's `config.platform.php` is pinned to 8.2 so the lock file cannot contain packages the declared floor could not install.

## Notes

Scaffolded with `@wordpress/create-block` and then rewritten. PHP lives in `inc/` under PSR-4 rather than `src/`, which `@wordpress/scripts` reserves for block source.

Not included, as outside the brief: settings page, block styles or variations, caching.

Developed against WordPress 7.1, WP-CLI 2.12 and Node 24, on PHP 8.2 and 8.5.
