<?php
/**
 * Find installed SEO plugins.
 *
 * @package SEO
 */

declare(strict_types=1);

namespace lloc\SEO\Helper;

final class Plugins {

	/**
	 * Constants that indicate an SEO plugin printing its own tag.
	 */
	private const SEO_PLUGINS = array(
		'WPSEO_VERSION',                // Yoast SEO.
		'RANK_MATH_VERSION',            // Rank Math.
		'AIOSEO_VERSION',               // All in One SEO.
		'SEOPRESS_VERSION',             // SEOPress.
		'THE_SEO_FRAMEWORK_VERSION',
	);

	/**
	 * Whether a known SEO plugin is active.
	 *
	 * @return bool
	 */
	public static function active(): bool {
		foreach ( self::SEO_PLUGINS as $constant ) {
			if ( defined( $constant ) ) {
				return true;
			}
		}

		return false;
	}
}
