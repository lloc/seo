<?php
/**
 * Meta image for post types.
 *
 * @package SEO
 */

declare(strict_types=1);

namespace lloc\SEO\Meta;

use lloc\SEO\Helper\Plugins;

/**
 * Class Image
 *
 * @package SEO
 */
class Image implements Registerable {

	public const META_IMAGE_FILTER = 'lloc_seo_meta_image';

	public const RENDER_PRIORITY = 3;

	public const SEO_PLUGIN_FILTER = array(
		'wpseo_add_opengraph_images',                           // Yoast SEO.
		'rank_math/opengraph/facebook/add_additional_images',   // Rank Math.
		'rank_math/opengraph/twitter/add_additional_images',    // Rank Math.
	);

	/**
	 * @inheritdoc
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'render' ), self::RENDER_PRIORITY );

		array_map(
			function ( $filter ) {
				add_action( $filter, array( $this, 'add_image' ) );
			},
			self::SEO_PLUGIN_FILTER
		);
	}

	/**
	 * Add the share image if Yoast or Rank Math has none for the page.
	 *
	 * @param object $images Yoast or Rank Math image container, both offer add_image() and has_images().
	 *
	 * @return void
	 */
	public function add_image( $images ): void {
		if ( ! method_exists( $images, 'add_image' ) ) {
			return;
		}

		if ( method_exists( $images, 'has_images' ) && $images->has_images() ) {
			return;
		}

		$image = $this->value();
		if ( '' === $image['url'] ) {
			return;
		}

		$images->add_image( $image );
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

		$image = $this->value();
		if ( '' === $image['url'] ) {
			return;
		}

		printf( '<meta property="og:image" content="%s" />' . PHP_EOL, esc_url( $image['url'] ) );
		printf( '<meta property="og:image:width" content="%d" />' . PHP_EOL, esc_attr( strval( $image['width'] ) ) );
		printf( '<meta property="og:image:height" content="%d" />' . PHP_EOL, esc_attr( strval( $image['height'] ) ) );
	}

	/**
	 * Image for the current page.
	 *
	 * @return array{url: string, width: int|string, height: int|string, type: string}
	 */
	public function value(): array {
		$defaults = array(
			'url'    => '',
			'width'  => 0,
			'height' => 0,
			'type'   => '',
		);

		/**
		 * Filter the meta image for the current page.
		 *
		 * @param array{url: string, width: int|string, height: int|string, type: string} $image Share image, an empty url means no image.
		 * @param int $queried_object_id The ID of the queried object.
		 * @return array{url: string, width: int|string, height: int|string, type: string}
		 */
		$image = apply_filters(
			self::META_IMAGE_FILTER,
			$defaults,
			get_queried_object_id()
		);

		return wp_parse_args( $image, $defaults ); // @phpstan-ignore return.type
	}
}
