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

	public const RENDER_PRIORITY = 2;

	public function __construct(
		protected string $url,
		protected int $width,
		protected int $height,
		protected string $type
	) {}

	/**
	 * @inheritdoc
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'render' ), self::RENDER_PRIORITY );

		add_action( 'wpseo_add_opengraph_images', array( $this, 'add_yoast_image' ) );
	}

	/**
	 * Add the share image if Yoast has none for the page.
	 *
	 * @param object $images Yoast image container.
	 *
	 * @return void
	 */
	public function add_yoast_image( $images ): void {
		$post_object = get_queried_object();
		$post_name   = $post_object instanceof \WP_Post ? $post_object->post_name : '';

		if ( '' === $post_name || ! method_exists( $images, 'add_image' ) ) {
			return;
		}

		if ( method_exists( $images, 'has_images' ) && $images->has_images() ) {
			return;
		}

		$images->add_image(
			array(
				'url'    => $this->url,
				'width'  => $this->width,
				'height' => $this->height,
				'type'   => $this->type,
			)
		);
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

		printf( '<meta property="og:image" content="%s" />' . PHP_EOL, esc_url( $this->url ) );
		printf( '<meta property="og:image:width" content="%d" />' . PHP_EOL, esc_attr( strval( $this->width ) ) );
		printf( '<meta property="og:image:height" content="%d" />' . PHP_EOL, esc_attr( strval( $this->height ) ) );
	}
}
