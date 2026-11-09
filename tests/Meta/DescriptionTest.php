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

	private function filtered( $filtered ): void {
		Functions\when( 'get_queried_object_id' )->justReturn( 42 );
		Filters\expectApplied( Description::META_DESCRIPTION_FILTER )
			->with( '', 42 )
			->andReturn( $filtered );
	}

	public static function hook_provider(): array {
		return array(
			'wp_head'                                     => array( 'has_action', 'wp_head', 'render', Description::RENDER_PRIORITY ),
			'wpseo_metadesc'                              => array( 'has_filter', 'wpseo_metadesc', 'fill', 10 ),
			'wpseo_opengraph_desc'                        => array( 'has_filter', 'wpseo_opengraph_desc', 'fill', 10 ),
			'wpseo_twitter_description'                   => array( 'has_filter', 'wpseo_twitter_description', 'fill', 10 ),
			'rank_math/frontend/description'              => array( 'has_filter', 'rank_math/frontend/description', 'fill', 10 ),
			'rank_math/opengraph/facebook/og_description' => array( 'has_filter', 'rank_math/opengraph/facebook/og_description', 'fill', 10 ),
			'rank_math/opengraph/twitter/twitter_description' => array( 'has_filter', 'rank_math/opengraph/twitter/twitter_description', 'fill', 10 ),
			'aioseo_description'                          => array( 'has_filter', 'aioseo_description', 'fill', 10 ),
			'seopress_titles_desc'                        => array( 'has_filter', 'seopress_titles_desc', 'fill', 10 ),
			'the_seo_framework_custom_field_description'  => array( 'has_filter', 'the_seo_framework_custom_field_description', 'fill_current', 10 ),
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

	public static function filtered_provider(): array {
		return array(
			'string' => array( 'Legal notice', 'Legal notice' ),
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

		$this->assertSame( $expected, ( new Description() )->value() );
	}

	public static function render_provider(): array {
		return array(
			'filtered' => array( 'Legal notice', '<meta name="description" content="Legal notice" />' . PHP_EOL . '<meta property="og:description" content="Legal notice" />' . PHP_EOL ),
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
