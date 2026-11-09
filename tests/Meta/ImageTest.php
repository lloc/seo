<?php

declare(strict_types=1);

namespace lloc\SEOTests;

use Brain\Monkey;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use lloc\SEO\Meta\Image;
use PHPUnit\Framework\TestCase;

/**
 * @covers \lloc\SEO\Meta\Image
 * @uses   \lloc\SEO\Helper\Plugins
 */
final class ImageTest extends TestCase {


	private const IMAGE = array(
		'url'    => 'https://example.com/share.png',
		'width'  => 1200,
		'height' => 630,
		'type'   => 'image/png',
	);

	private const NONE = array(
		'url'    => '',
		'width'  => 0,
		'height' => 0,
		'type'   => '',
	);

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
		Functions\when( 'wp_parse_args' )->alias( fn( $args, $defaults ) => array_merge( $defaults, (array) $args ) );
		Filters\expectApplied( Image::META_IMAGE_FILTER )
			->with( self::NONE, 42 )
			->andReturn( $filtered );
	}

	public static function hook_provider(): array {
		return array(
			'wp_head'                    => array( 'has_action', 'wp_head', 'render', Image::RENDER_PRIORITY ),
			'wpseo_add_opengraph_images' => array( 'has_action', 'wpseo_add_opengraph_images', 'add_image', 10 ),
			'rank_math/opengraph/facebook/add_additional_images' => array( 'has_action', 'rank_math/opengraph/facebook/add_additional_images', 'add_image', 10 ),
			'rank_math/opengraph/twitter/add_additional_images' => array( 'has_action', 'rank_math/opengraph/twitter/add_additional_images', 'add_image', 10 ),
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
		$test = new Image();
		$test->register();

		$this->assertSame( $priority, $has( $hook, array( $test, $method ) ) );
	}

	public static function filtered_provider(): array {
		$none = self::NONE;

		return array(
			'full'    => array( self::IMAGE, self::IMAGE ),
			'partial' => array(
				array(
					'url'   => 'https://example.com/a.jpg',
					'width' => '800',
				),
				array_merge(
					$none,
					array(
						'url'   => 'https://example.com/a.jpg',
						'width' => '800',
					)
				),
			),
			'empty'   => array( array(), $none ),
			'null'    => array( null, $none ),
		);
	}

	/**
	 * @dataProvider filtered_provider
	 *
	 * @param mixed $filtered
	 * @param array $expected
	 */
	public function test_value( $filtered, array $expected ): void {
		$this->filtered( $filtered );

		$this->assertSame( $expected, ( new Image() )->value() );
	}

	public static function render_provider(): array {
		return array(
			'filtered' => array( self::IMAGE, '<meta property="og:image" content="https://example.com/share.png" />' . PHP_EOL . '<meta property="og:image:width" content="1200" />' . PHP_EOL . '<meta property="og:image:height" content="630" />' . PHP_EOL ),
			'url only' => array( array( 'url' => 'https://example.com/a.jpg' ), '<meta property="og:image" content="https://example.com/a.jpg" />' . PHP_EOL ),
			'empty'    => array( array(), '' ),
		);
	}

	/**
	 * @dataProvider render_provider
	 *
	 * @param array  $filtered
	 * @param string $expected
	 */
	public function test_render( array $filtered, string $expected ): void {
		$this->filtered( $filtered );

		$this->expectOutputString( $expected );
		( new Image() )->render();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function test_render_prints_nothing_with_seo_plugin(): void {
		define( 'WPSEO_VERSION', '1.0' );

		$this->expectOutputString( '' );
		( new Image() )->render();
	}

	private function yoast_images( bool $has_images ): object {
		return new class( $has_images ) {
			/** @var array<int, array<string, mixed>> */
			public array $added = array();

			public function __construct( private bool $has_images ) {}

			public function has_images(): bool {
				return $this->has_images;
			}

			public function add_image( array $image ): void {
				$this->added[] = $image;
			}
		};
	}

	public function test_add_image(): void {
		$this->filtered( self::IMAGE );

		$images = $this->yoast_images( false );
		( new Image() )->add_image( $images );

		$this->assertSame( array( self::IMAGE ), $images->added );
	}

	public function test_add_image_keeps_existing(): void {
		$images = $this->yoast_images( true );
		( new Image() )->add_image( $images );

		$this->assertSame( array(), $images->added );
	}

	public function test_add_image_skips_empty(): void {
		$this->filtered( array() );

		$images = $this->yoast_images( false );
		( new Image() )->add_image( $images );

		$this->assertSame( array(), $images->added );
	}
}
