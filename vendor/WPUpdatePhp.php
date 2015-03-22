<?php

/**
 * WPUpdatePHP
 *
 * @package   WPUpdatePhp
 * @author    Coen Jacobs
 * @license   GPLv3
 * @link      https://github.com/WPupdatePHP/wp-update-php
 */

if ( class_exists( 'WPUpdatePhp' ) ) {
	return;
}

class WPUpdatePhp {
	/** @var String */
	private $minimum_version;

	/**
	 * @var string
	 */
	private $plugin_name = 'this plugin';

	/**
	 * @param $minimum_version
	 */
	public function __construct( $minimum_version ) {
		$this->minimum_version = $minimum_version;
	}

	/**
	 * @param $version
	 *
	 * @return bool
	 */
	public function does_it_meet_required_php_version( $version = PHP_VERSION ) {
		if ( $this->is_minimum_php_version( $version ) ) {
			return true;
		}

		$this->load_minimum_required_version_notice();
		return false;
	}

	/**
	 * @param $version
	 *
	 * @return boolean
	 */
	private function is_minimum_php_version( $version ) {
		return version_compare( $this->minimum_version, $version, '<=' );
	}

	/**
	 * @param $plugin_name
	 *
	 * @return bool
	 */
	public function does_my_plugin_meet_required_php_version( $plugin_name ) {
		$this->plugin_name = $plugin_name;
		return $this->does_it_meet_required_php_version();
	}

	/**
	 * @return void
	 */
	private function load_minimum_required_version_notice() {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			if ( ! is_main_network() ) {
				add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			} else {
				add_action( 'admin_head', array( $this, 'admin_notice' ) );
			}
		}
	}

	/**
	 * Generate admin notice
	 */
	public function admin_notice() {
		?>
		<div class="error">
			<p>
				<?php printf( __( 'Unfortunately, %1$s can not run on PHP versions older than %2$s. Read more information about <a href="http://www.wpupdatephp.com/update/">how you can update</a>.', 'the-events-calendar-category-colors' ), $this->plugin_name, $this->minimum_version ); ?>
			</p>
		</div>
	<?php
	}

}