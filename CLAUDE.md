# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

A single-file WordPress plugin (`sa_related_pages.php`) that registers a sidebar widget, "St Andrews Related Pages", for the St Andrews website. The widget shows pages related to the current page in the page tree.

There is no build system, package manager, linter or test suite. To try a change, copy the file into a WordPress install's `wp-content/plugins/` directory, activate the plugin, and add the widget to a sidebar. `php -l sa_related_pages.php` gives a quick syntax check.

## How it works

- `plugins_loaded` → `sa_related_pages_loaded()` hooks `widgets_init` to register the `sa_related_pages_pages_widget` class.
- `widget()` calls `sa_related_pages_find_and_render_list()` and prints the widget wrapper, the optional title and intro text only when that list is non-empty.
- `sa_related_pages_find_and_render_list()` runs up to three `WP_Query` lookups against the global `$post` (published pages only):
  - **parent** (only if the page has one), class `sa_related_pages_parent`
  - **siblings** (same parent, ordered by `menu_order`, excluding the current page), class `sa_related_pages_sibling`
  - **children** (ordered by `menu_order`), class `sa_related_pages_child`
- Selection rule: if the page has children, show **only** the children; otherwise show the parent followed by the siblings. Everything is wrapped in a single `<ul>`, with one `<li class="...">` per entry so the theme can style each group.
- Because the queries loop with `the_post()`, the function calls `wp_reset_postdata()` / `wp_reset_query()` afterwards. Keep these calls if you add queries.

## PHP compatibility

The live site runs PHP 8.3. Avoid removed APIs such as `create_function()` and PHP4-style constructors. Use `?? ''` when reading widget `$instance` keys, because a newly added widget has no saved settings.
