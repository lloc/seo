<?php

declare(strict_types=1);

namespace lloc\SEOTests;

use Brain\Monkey;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use lloc\SEO\Meta\Description;
use PHPUnit\Framework\TestCase;

/**
 * @covers \lloc\SEO\Meta\Description
 * @uses   \lloc\SEO\Helper\Plugins
 */
final class DescriptionTest extends TestCase {


	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\stubEscapeFunctions();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	private function front_page( string $tagline ): void {
		Functions\when( 'is_front_page' )->justReturn( true );
		Functions\when( 'get_bloginfo' )->justReturn( $tagline );
		Functions\when( 'wp_strip_all_tags' )->alias( 'strip_tags' );
	}

	private function other_page( $filtered ): void {
		Functions\when( 'is_front_page' )->justReturn( false );
		Functions\when( 'get_queried_object_id' )->justReturn( 42 );
		Filters\expectApplied( Description::META_DESCRIPTION_FILTER )
			->with( '', 42 )
			->andReturn( $filtered );
	}

	public static function hook_provider(): array {
		return array(
			'wp_head'                   => array( 'has_action', 'wp_head', 'render', 1 ),
			'wpseo_metadesc'            => array( 'has_filter', 'wpseo_metadesc', 'fill', 10 ),
			'wpseo_opengraph_desc'      => array( 'has_filter', 'wpseo_opengraph_desc', 'fill', 10 ),
			'wpseo_twitter_description' => array( 'has_filter', 'wpseo_twitter_description', 'fill', 10 ),
		);
	}

	/**
	 * @dataProvider hook_provider
	 *
	 * @param callable $has
	 * @param string   $hook
	 * @param string   $method
	 * @param int      $priority
	 */
	public function test_register_adds_hook( callable $has, string $hook, string $method, int $priority ): void {
		$test = new Description();
		$test->register();

		$this->assertSame( $priority, $has( $hook, array( $test, $method ) ) );
	}

	public static function tagline_provider(): array {
		return array(
			'plain'   => array( 'Just another site', 'Just another site' ),
			'trimmed' => array( '  Just another site ', 'Just another site' ),
			'tags'    => array( '<b>Just another site</b>', 'Just another site' ),
			'empty'   => array( '', '' ),
		);
	}

	/**
	 * @dataProvider tagline_provider
	 *
	 * @param string $tagline
	 * @param string $expected
	 */
	public function test_description_front_page( string $tagline, string $expected ): void {
		$this->front_page( $tagline );

		$this->assertSame( $expected, ( new Description() )->description() );
	}

	public static function filtered_provider(): array {
		return array(
			'string' => array( 'Legal notice', 'Legal notice' ),
			'empty'  => array( '', '' ),
			'array'  => array( array( 'nope' ), '' ),
			'null'   => array( null, '' ),
			'int'    => array( 42, '' ),
		);
	}

	/**
	 * @dataProvider filtered_provider
	 *
	 * @param mixed  $filtered
	 * @param string $expected
	 */
	public function test_description_other_page( $filtered, string $expected ): void {
		$this->other_page( $filtered );

		$this->assertSame( $expected, ( new Description() )->description() );
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
		$this->other_page( $ours );

		$this->assertSame( $expected, ( new Description() )->fill( $yoast ) );
	}

	public static function render_provider(): array {
		return array(
			'tagline' => array( 'Tagline', '<meta name="description" content="Tagline" />' . PHP_EOL ),
			'empty'   => array( '', '' ),
		);
	}

	/**
	 * @dataProvider render_provider
	 *
	 * @param string $tagline
	 * @param string $expected
	 */
	public function test_render( string $tagline, string $expected ): void {
		$this->front_page( $tagline );

		$this->expectOutputString( $expected );
		( new Description() )->render();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function test_render_prints_nothing_with_seo_plugin(): void {
		define( 'WPSEO_VERSION', '1.0' );

		$this->expectOutputString( '' );
		( new Description() )->render();
	}
}
