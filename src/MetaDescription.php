<?php
/**
 * Meta description for the front page.
 *
 * @package SEO
 */

declare(strict_types=1);

namespace lloc\SEO;

/**
 * Meta description for the front page (the site tagline, Settings > General,
 * so every language site of the network gets its own) and the legal page.
 *
 * Without an SEO plugin it prints the tag itself. With Yoast SEO it only fills
 * in the description (and og:description) where Yoast has none.
 */
class MetaDescription {

	const META_DESCRIPTION_FILTER = 'lloc_seo_meta_description';

	/**
	 * Hook into wp_head.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'render' ), 1 );
		add_filter( 'wpseo_metadesc', array( $this, 'fill' ) );
		add_filter( 'wpseo_opengraph_desc', array( $this, 'fill' ) );
	}

	/**
	 * Yoast filter: keep a description set in Yoast, otherwise use ours.
	 *
	 * @param mixed $description Description from Yoast.
	 *
	 * @return mixed
	 */
	public function fill( $description ) {
		if ( is_string( $description ) && '' !== trim( $description ) ) {
			return $description;
		}

		$ours = $this->description();

		return '' === $ours ? $description : $ours;
	}

	/**
	 * Print the tag when applicable.
	 *
	 * @return void
	 */
	public function render(): void {
		$content = $this->content();
		if ( '' === $content ) {
			return;
		}

		printf( '<meta name="description" content="%s" />' . PHP_EOL, esc_attr( $content ) );
	}

	/**
	 * The description to print, or an empty string.
	 *
	 * @return string
	 */
	public function content(): string {
		return ! SEOPlugins::active() ? $this->description() : '';
	}

	/**
	 * Description for the current page.
	 *
	 * @return string
	 */
	public function description(): string {
		if ( is_front_page() ) {
			return trim( wp_strip_all_tags( (string) get_bloginfo( 'description' ) ) );
		}

		$description = apply_filters( self::META_DESCRIPTION_FILTER, '' );

		return is_string( $description ) ? trim( $description ) : '';
	}

}
