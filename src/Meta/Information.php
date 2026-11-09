<?php
/**
 * Abstract class for meta information.
 *
 * @package SEO
 */

declare(strict_types=1);

namespace lloc\SEO\Meta;

abstract class Information {

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

	/**
	 * Fill values for the current page only.
	 *
	 * The SEO Framework passes $args when it asks for another object, e.g. in admin lists.
	 *
	 * @param mixed                     $value Value coming from a SEO plugin.
	 * @param array<string, mixed>|null $args  Query arguments, null for the current page.
	 *
	 * @return mixed
	 */
	public function fill_current( $value, $args = null ) {
		return null === $args ? $this->fill( $value ) : $value;
	}
}
