<?php
/**
 * Abstract class for meta information.
 *
 * @package SEO
 */

declare(strict_types=1);

namespace lloc\SEO\Meta;

abstract class Meta {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	abstract public function register(): void;

	/**
	 * Get the value for the meta information.
	 *
	 * @return string
	 */
	abstract public function value(): string;

	/**
	 * Filter values, non empty values are preserved.
	 *
	 * @param mixed $value Value coming from a SEO plugin.
	 *
	 * @return mixed
	 */
	public function fill( $value ) {
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return $value;
		}

		$content = $this->value();

		return '' !== $content ? $content : $value;
	}
}
