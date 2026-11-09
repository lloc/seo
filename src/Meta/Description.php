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

	public const RENDER_PRIORITY = 1;

	/**
	 * @inheritdoc
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'render' ), self::RENDER_PRIORITY );

		add_filter( 'wpseo_metadesc', array( $this, 'fill' ) );
		add_filter( 'wpseo_opengraph_desc', array( $this, 'fill' ) );
		add_filter( 'wpseo_twitter_description', array( $this, 'fill' ) );
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
