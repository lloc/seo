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
 * @covers \lloc\SEO\Meta\Information
 * @uses   \lloc\SEO\Helper\Plugins
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
			'wp_head'                                   => array( 'has_action', 'wp_head', 'render', Title::RENDER_PRIORITY ),
			'pre_get_document_title'                    => array( 'has_filter', 'pre_get_document_title', 'document_title', 10 ),
			'wpseo_title'                               => array( 'has_filter', 'wpseo_title', 'fill', 10 ),
			'wpseo_opengraph_title'                     => array( 'has_filter', 'wpseo_opengraph_title', 'fill', 10 ),
			'rank_math/frontend/title'                  => array( 'has_filter', 'rank_math/frontend/title', 'fill', 10 ),
			'rank_math/opengraph/facebook/og_title'     => array( 'has_filter', 'rank_math/opengraph/facebook/og_title', 'fill', 10 ),
			'rank_math/opengraph/twitter/twitter_title' => array( 'has_filter', 'rank_math/opengraph/twitter/twitter_title', 'fill', 10 ),
			'aioseo_title'                              => array( 'has_filter', 'aioseo_title', 'fill', 10 ),
			'seopress_titles_title'                     => array( 'has_filter', 'seopress_titles_title', 'fill', 10 ),
			'the_seo_framework_title_from_custom_field' => array( 'has_filter', 'the_seo_framework_title_from_custom_field', 'fill_current', 10 ),
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
		$test = new Title();
		$test->register();

		$this->assertSame( $priority, $has( $hook, array( $test, $method ) ) );
	}

	public function test_fill_current(): void {
		$this->filtered( 'Ours' );

		$this->assertSame( 'Ours', ( new Title() )->fill_current( '', null ) );
	}

	public function test_fill_current_skips_other_objects(): void {
		$this->assertSame( '', ( new Title() )->fill_current( '', array( 'id' => 7 ) ) );
	}

	public function test_document_title(): void {
		$this->filtered( 'Ours' );

		$this->assertSame( 'Ours', ( new Title() )->document_title( '' ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function test_document_title_untouched_with_seo_plugin(): void {
		define( 'WPSEO_VERSION', '1.0' );

		$this->assertSame( '', ( new Title() )->document_title( '' ) );
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

	public static function render_provider(): array {
		return array(
			'filtered' => array( 'Impressum', '<meta property="og:title" content="Impressum" />' . PHP_EOL ),
			'empty'    => array( '', '' ),
		);
	}

	/**
	 * @dataProvider render_provider
	 *
	 * @param string $filtered
	 * @param string $expected
	 */
	public function test_render( string $filtered, string $expected ): void {
		$this->filtered( $filtered );

		$this->expectOutputString( $expected );
		( new Title() )->render();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function test_render_prints_nothing_with_seo_plugin(): void {
		define( 'WPSEO_VERSION', '1.0' );

		$this->expectOutputString( '' );
		( new Title() )->render();
	}
}
