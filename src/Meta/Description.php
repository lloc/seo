<?php
/**
 * Meta description for post types.
 *
 * @package SEO
 */

declare(strict_types=1);

namespace lloc\SEO\Meta;

use lloc\SEO\Helper\Plugins;

/**
 * Class Description
 *
 * @package SEO
 */
class Description extends Information implements Registerable {

	public const META_DESCRIPTION_FILTER = 'lloc_seo_meta_description';

	public const RENDER_PRIORITY = 2;

	public const SEO_PLUGIN_FILTER = array(
		'wpseo_metadesc',                          // Yoast SEO.
		'wpseo_opengraph_desc',                    // Yoast SEO.
		'wpseo_twitter_description',               // Yoast SEO.
		'rank_math/frontend/description',          // Rank Math.
		'rank_math/opengraph/facebook/og_description', // Rank Math.
		'rank_math/opengraph/twitter/twitter_description', // Rank Math.
		'aioseo_description',                      // All in One SEO.
		'seopress_titles_desc',                    // SEOPress.
	);

	/**
	 * @inheritdoc
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'render' ), self::RENDER_PRIORITY );

		array_map(
			function ( $filter ) {
				add_filter( $filter, array( $this, 'fill' ) );
			},
			self::SEO_PLUGIN_FILTER
		);

		// The SEO Framework passes $args when it asks for another object.
		add_filter( 'the_seo_framework_custom_field_description', array( $this, 'fill_current' ), 10, 2 );
	}

	/**
	 * Print the tag when applicable.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( Plugins::active() ) {
			return;
		}

		$content = $this->value();
		if ( '' === $content ) {
			return;
		}

		printf(
			'<meta name="description" content="%s" />' . PHP_EOL,
			esc_attr( $content )
		);
		printf(
			'<meta property="og:description" content="%s" />' . PHP_EOL,
			esc_attr( $content )
		);
	}

	/**
	 * Description for the current page.
	 *
	 * @return string
	 */
	public function value(): string {
		$queried_object_id = get_queried_object_id();

		/**
		 * Filter the meta description for the current page.
		 *
		 * @param string $description        The meta description for the current page.
		 * @param int    $queried_object_id  The ID of the queried object.
		 * @return string
		 */
		$description = apply_filters(
			self::META_DESCRIPTION_FILTER,
			'',
			$queried_object_id
		);

		return esc_attr( (string) $description );
	}
}
