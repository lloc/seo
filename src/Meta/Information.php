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
	 * Name of the filter that provides the value.
	 *
	 * @return non-empty-string
	 */
	abstract protected function filter(): string;

	/**
	 * Value for the current page.
	 *
	 * @return string
	 */
	public function value(): string {
		/**
		 * Filter the meta title or description for the current page.
		 *
		 * @param string $value             The value for the current page.
		 * @param int    $queried_object_id The ID of the queried object.
		 * @return string
		 */
		$value = apply_filters( $this->filter(), '', get_queried_object_id() );

		return esc_attr( (string) $value );
	}

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
