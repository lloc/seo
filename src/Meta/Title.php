<?php
/**
 * Meta title for post types.
 *
 * @package SEO
 */

declare(strict_types=1);

namespace lloc\SEO\Meta;

use lloc\SEO\Helper\Plugins;

final class Title extends Information implements Registerable {


	public const META_TITLE_FILTER = 'lloc_seo_meta_title';

	public const RENDER_PRIORITY = 1;

	public const SEO_PLUGIN_FILTER = array(
		'wpseo_title',                                      // Yoast SEO.
		'wpseo_opengraph_title',                            // Yoast SEO.
		'rank_math/frontend/title',                         // Rank Math.
		'rank_math/opengraph/facebook/og_title',            // Rank Math.
		'rank_math/opengraph/twitter/twitter_title',        // Rank Math.
		'aioseo_title',                                     // All in One SEO.
		'seopress_titles_title',                            // SEOPress.
	);

	/**
	 * @inheritdoc
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'render' ), self::RENDER_PRIORITY );

		foreach ( self::SEO_PLUGIN_FILTER as $hook ) {
			add_filter( $hook, array( $this, 'fill' ) );
		}

		// The SEO Framework passes $args when it asks for another object.
		add_filter( 'the_seo_framework_title_from_custom_field', array( $this, 'fill_current' ), 10, 2 );

		add_filter( 'pre_get_document_title', array( $this, 'document_title' ) );
	}

	/**
	 * Document title when no SEO plugin sets it.
	 *
	 * @param mixed $title Title so far, '' lets WordPress build it.
	 *
	 * @return mixed
	 */
	public function document_title( $title ) {
		return Plugins::active() ? $title : $this->fill( $title );
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
			'<meta property="og:title" content="%s" />' . PHP_EOL,
			esc_attr( $content )
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function filter(): string {
		return self::META_TITLE_FILTER;
	}
}
