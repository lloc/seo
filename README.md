# SEO library

Small library for SEO-related functionality in WordPress themes and plugins.

## What problem does it solve?

Themes and plugins often know the best title, description and share image for a page, but they can't know
whether the site runs an SEO plugin. Printing your own `<meta>` tags then leads to duplicates; printing none
leaves sites without an SEO plugin with empty tags.

This library handles both cases. You provide the values through filters, and the library decides where they go:

- **No SEO plugin active:** it prints the tags itself (`<title>`, `og:title`, `meta description`, `og:description`, `og:image`).
- **SEO plugin active:** it prints nothing. Instead it hands your values to the plugin where it has none,
  and keeps values an editor has set in the plugin. How much is covered differs per plugin, see below.

Detected SEO plugins: Yoast SEO, Rank Math, All in One SEO, SEOPress, The SEO Framework.

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

- Without an SEO plugin, the value replaces the document title (`pre_get_document_title`)
  and is printed as `<meta property="og:title">` in `wp_head`.
- With Yoast, it is used for `wpseo_title` and `wpseo_opengraph_title` when Yoast has no value.
- With Rank Math, it is used for `rank_math/frontend/title`, `rank_math/opengraph/facebook/og_title`
  and `rank_math/opengraph/twitter/twitter_title` when Rank Math has no value.
- With All in One SEO or SEOPress, it is used for `aioseo_title` or `seopress_titles_title`
  when the plugin has no value (title tag only, not Open Graph).
- With The SEO Framework, it is used for `the_seo_framework_title_from_custom_field` when no custom title is set.
  The SEO Framework also uses it for `og:title` and `twitter:title` unless those are set separately.

### Description

Filter: `lloc_seo_meta_description`

```php
add_filter( 'lloc_seo_meta_description', function ( string $description, int $object_id ): string {
	return is_singular() ? wp_strip_all_tags( get_the_excerpt( $object_id ) ) : $description;
}, 10, 2 );
```

- Without an SEO plugin, it prints `<meta name="description">` and `<meta property="og:description">` in `wp_head`.
- With Yoast, it is used for `wpseo_metadesc`, `wpseo_opengraph_desc` and `wpseo_twitter_description`
  when Yoast has no value.
- With Rank Math, it is used for `rank_math/frontend/description`, `rank_math/opengraph/facebook/og_description`
  and `rank_math/opengraph/twitter/twitter_description` when Rank Math has no value.
- With All in One SEO or SEOPress, it is used for `aioseo_description` or `seopress_titles_desc`
  when the plugin has no value (meta description only, not Open Graph).
- With The SEO Framework, it is used for `the_seo_framework_custom_field_description` when no custom description
  is set. The SEO Framework also uses it for `og:description` and `twitter:description` unless those are set separately.

### Share image

Filter: `lloc_seo_meta_image`

The filter receives and returns an array with the keys `url`, `width`, `height` and `type`.
Missing keys are filled with defaults; an empty `url` means no image.

```php
add_filter( 'lloc_seo_meta_image', function ( array $image, int $object_id ): array {
	$attachment_id = get_post_thumbnail_id( $object_id );
	if ( ! $attachment_id ) {
		return $image;
	}

	$src = wp_get_attachment_image_src( $attachment_id, 'large' );

	return array(
		'url'    => $src[0],
		'width'  => $src[1],
		'height' => $src[2],
		'type'   => get_post_mime_type( $attachment_id ),
	);
}, 10, 2 );
```

- Without an SEO plugin, it prints `og:image`, `og:image:width` and `og:image:height` in `wp_head`.
- With Yoast, the image is added via `wpseo_add_opengraph_images` when Yoast has no image for the page.
- With Rank Math, the image is added via `rank_math/opengraph/{facebook,twitter}/add_additional_images`
  when Rank Math has no image for the page.

### Clean up the head

`CleanUp::head()` removes:

- the RSD link (points to `xmlrpc.php`),
- the Windows Live Writer manifest link,
- the WordPress version (generator tag and feeds),
- the emoji detection script and styles (front end and admin).

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
