# SEO library

Small library for SEO-related functionality in WordPress themes and plugins.

## What problem does it solve?

Themes and plugins often know the best title, description and share image for a page, but they can't know
whether the site runs an SEO plugin. Printing your own `<meta>` tags then leads to duplicates; printing none
leaves sites without an SEO plugin with empty tags.

This library handles both cases. You provide the values through filters, and the library decides where they go:

- **No SEO plugin active:** it sets the `<title>` and prints `og:title`, `meta description`, `og:description`
  and `og:image`.
- **SEO plugin active:** it prints nothing. Instead it hands your values to the plugin where it has none,
  and keeps values an editor has set in the plugin. See [SEO plugin support](#seo-plugin-support).

It also has a helper that removes tags WordPress puts in the `<head>` by default but that most sites don't need.

## Requirements

- PHP 8.1+
- WordPress

## Installation

```bash
composer require lloc/seo
```

## Usage

Register the features you want, e.g. in your plugin's main file or your theme's `functions.php`:

```php
use lloc\SEO\Meta\Description;
use lloc\SEO\Meta\Image;
use lloc\SEO\Meta\Title;
use lloc\SEO\Utils\CleanUp;

( new Title() )->register();
( new Description() )->register();
( new Image() )->register();

CleanUp::head();
```

Then provide the values through filters. Each filter receives the ID of the queried object
(`get_queried_object_id()`). An empty return value means "nothing to add", and no tag is printed.

### Title

Filter: `lloc_seo_meta_title`

```php
add_filter( 'lloc_seo_meta_title', function ( string $title, int $object_id ): string {
	return get_post_meta( $object_id, 'my_seo_title', true ) ?: $title;
}, 10, 2 );
```

Without an SEO plugin, the value replaces the document title (`pre_get_document_title`, needs
`add_theme_support( 'title-tag' )`) and is printed as `<meta property="og:title">` in `wp_head`.

### Description

Filter: `lloc_seo_meta_description`

```php
add_filter( 'lloc_seo_meta_description', function ( string $description, int $object_id ): string {
	return is_singular() ? wp_strip_all_tags( get_the_excerpt( $object_id ) ) : $description;
}, 10, 2 );
```

Without an SEO plugin, it prints `<meta name="description">` and `<meta property="og:description">` in `wp_head`.

### Share image

Filter: `lloc_seo_meta_image`

The filter receives and returns an array with the keys `url`, `width`, `height` and `type`.
Missing keys are filled with defaults; an empty `url` means no image.

```php
add_filter( 'lloc_seo_meta_image', function ( array $image, int $object_id ): array {
	$attachment_id = get_post_thumbnail_id( $object_id );
	$src           = $attachment_id ? wp_get_attachment_image_src( $attachment_id, 'large' ) : false;
	if ( ! $src ) {
		return $image;
	}

	return array(
		'url'    => $src[0],
		'width'  => $src[1],
		'height' => $src[2],
		'type'   => get_post_mime_type( $attachment_id ),
	);
}, 10, 2 );
```

Without an SEO plugin, it prints `og:image` in `wp_head`, plus `og:image:width` and `og:image:height` when they
are greater than 0.

### SEO plugin support

With an SEO plugin active, the library prints nothing and passes your values to the plugin's own filters
instead. A value the plugin already has (set by an editor or generated from the plugin's templates) is kept,
so your value only fills gaps.

| Plugin | Title | Description | Image |
|---|---|---|---|
| Yoast SEO | title, `og:title` | description, `og:description`, `twitter:description` | `og:image` |
| Rank Math | title, `og:title`, `twitter:title` | description, `og:description`, `twitter:description` | `og:image`, `twitter:image` |
| All in One SEO | title | description | – |
| SEOPress | title | description | – |
| The SEO Framework | title, `og:title`, `twitter:title` | description, `og:description`, `twitter:description` | – |

The hooks are listed in the `SEO_PLUGIN_FILTER` constants of `Title`, `Description` and `Image`, except for
The SEO Framework, which is registered separately in `register()`. For The SEO Framework the library uses its
custom-field filters, so your value replaces the generated one; its social tags fall back to it unless they are
set separately.

### Clean up the head

`CleanUp::head()` removes:

- the RSD link (points to `xmlrpc.php`),
- the Windows Live Writer manifest link,
- the WordPress version (generator tag and feeds),
- the emoji detection script and styles.

### Check for SEO plugins

Use the helper in your own code to check whether an SEO plugin is active:

```php
use lloc\SEO\Helper\Plugins;

if ( ! Plugins::active() ) {
	// Print your own tags.
}
```

## Development

```bash
composer install
composer qa          # phpcs, phpstan and phpunit
composer phpcs:fix   # fix coding standard violations
```

## License

GPL-2.0-or-later
