<?php

declare(strict_types=1);

namespace lloc\SEOTests\Helper;

use lloc\SEO\Helper\Plugins;
use PHPUnit\Framework\TestCase;

/**
 * @covers \lloc\SEO\Helper\Plugins
 */
final class PluginsTest extends TestCase {


	public function test_inactive_without_known_constant(): void {
		$this->assertFalse( Plugins::active() );
	}

	public static function constant_provider(): array {
		return array(
			'Yoast SEO'         => array( 'WPSEO_VERSION' ),
			'Rank Math'         => array( 'RANK_MATH_VERSION' ),
			'All in One SEO'    => array( 'AIOSEO_VERSION' ),
			'SEOPress'          => array( 'SEOPRESS_VERSION' ),
			'The SEO Framework' => array( 'THE_SEO_FRAMEWORK_VERSION' ),
		);
	}

	/**
	 * Constants can't be undefined, so keep this out of the main process.
	 *
	 * @dataProvider         constant_provider
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 * @param string $constant
	 */
	public function test_active_with_known_constant( string $constant ): void {
		define( $constant, '1.0' );

		$this->assertTrue( Plugins::active() );
	}
}
