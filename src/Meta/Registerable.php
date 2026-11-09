<?php
/**
 * Interface for registerable meta information.
 *
 * @package SEO
 */

declare(strict_types=1);

namespace lloc\SEO\Meta;

interface Registerable {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void;
}
