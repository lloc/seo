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

	/**
	 * @inheritdoc
	 * @return void
	 */
	public function register(): void {
		add_filter( 'wpseo_title', array( $this, 'fill' ) );
		add_filter( 'wpseo_opengraph_title', array( $this, 'fill' ) );
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
	 * Title for the current page.
	 *
	 * @return string
	 */
	public function value(): string {
		$queried_object_id = get_queried_object_id();

		/**
		 * Filter the meta title for the current page.
		 *
		 * @param string $title        The meta title for the current page.
		 * @param int    $queried_object_id  The ID of the queried object.
		 * @return string
		 */
		$title = apply_filters(
			self::META_TITLE_FILTER,
			'',
			$queried_object_id
		);

		return esc_attr( (string) $title );
	}
}
