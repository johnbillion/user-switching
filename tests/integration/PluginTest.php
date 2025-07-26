<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

final class PluginTest extends Test {
	/**
	 * @var ?array<string, string>
	 */
	private $readme_data;

	public function testStableTagMatchesVersion(): void {
		$readme_data = $this->get_readme();
		if ( null === $readme_data ) {
			self::fail( 'There is no readme file' );
		}

		$plugin_data = get_plugin_data( dirname( dirname( __DIR__ ) ) . '/user-switching.php' );

		self::assertEquals( $readme_data['stable_tag'], $plugin_data['Version'] );
	}

	/**
	 * @return ?array<string, string>
	 */
	private function get_readme(): ?array {
		if ( ! isset( $this->readme_data ) ) {
			$file = dirname( dirname( __DIR__ ) ) . '/readme.txt';

			if ( ! is_file( $file ) ) {
				return null;
			}

			$file_array = file( $file );

			if ( false === $file_array ) {
				return null;
			}

			$file_contents = implode( '', $file_array );

			if ( preg_match( '|Stable tag:(.*)|i', $file_contents, $_stable_tag ) !== 1 ) {
				return null;
			}

			$this->readme_data = array(
				'stable_tag' => trim( trim( $_stable_tag[1], '*' ) )
			);
		}

		return $this->readme_data;
	}
}
