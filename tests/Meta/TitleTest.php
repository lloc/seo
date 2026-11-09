<?php

declare(strict_types=1);

namespace lloc\SEOTests;

use Brain\Monkey;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use lloc\SEO\Meta\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers \lloc\SEO\Meta\Title
 * @covers \lloc\SEO\Meta\Meta
 */
final class TitleTest extends TestCase {


	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\stubEscapeFunctions();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	private function filtered( $filtered ): void {
		Functions\when( 'get_queried_object_id' )->justReturn( 42 );
		Filters\expectApplied( Title::META_TITLE_FILTER )
			->with( '', 42 )
			->andReturn( $filtered );
	}

	public static function hook_provider(): array {
		return array(
			'wpseo_title'           => array( 'wpseo_title' ),
			'wpseo_opengraph_title' => array( 'wpseo_opengraph_title' ),
		);
	}

	/**
	 * @dataProvider hook_provider
	 *
	 * @param string $hook
	 */
	public function test_register_adds_hook( string $hook ): void {
		$test = new Title();
		$test->register();

		$this->assertSame( 10, has_filter( $hook, array( $test, 'fill' ) ) );
	}

	public static function filtered_provider(): array {
		return array(
			'string' => array( 'Impressum', 'Impressum' ),
			'empty'  => array( '', '' ),
			'null'   => array( null, '' ),
			'int'    => array( 42, '42' ),
		);
	}

	/**
	 * @dataProvider filtered_provider
	 *
	 * @param mixed  $filtered
	 * @param string $expected
	 */
	public function test_value( $filtered, string $expected ): void {
		$this->filtered( $filtered );

		$this->assertSame( $expected, ( new Title() )->value() );
	}

	public static function fill_provider(): array {
		return array(
			'keeps existing'         => array( 'From Yoast', 'Ours', 'From Yoast' ),
			'replaces whitespace'    => array( '  ', 'Ours', 'Ours' ),
			'replaces empty'         => array( '', 'Ours', 'Ours' ),
			'replaces non-string'    => array( false, 'Ours', 'Ours' ),
			'falls back, ours empty' => array( false, '', false ),
			'falls back, both empty' => array( '', '', '' ),
		);
	}

	/**
	 * @dataProvider fill_provider
	 *
	 * @param mixed  $yoast
	 * @param string $ours
	 * @param mixed  $expected
	 */
	public function test_fill( $yoast, string $ours, $expected ): void {
		$this->filtered( $ours );

		$this->assertSame( $expected, ( new Title() )->fill( $yoast ) );
	}
}
