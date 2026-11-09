<?php
/**
 * Cleanup for better SEO.
 *
 * @package SEO
 */

declare(strict_types=1);

namespace lloc\SEO\Utils;

final class CleanUp {

	/**
	 * Remove unnecessary tags from the head:
	 *
	 * - RSD link (points at xmlrpc.php),
	 * - Windows Live Writer manifest link,
	 * - WordPress version in the generator tag and in feeds,
	 * - emoji detection script and styles.
	 *
	 * @return void
	 */
	public static function head(): void {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		add_filter( 'the_generator', '__return_empty_string' );
	}
}
