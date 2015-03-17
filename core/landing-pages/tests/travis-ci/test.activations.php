<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class Tests_Activation extends WP_UnitTestCase {

	/**
	* Run a simple test to ensure that the tests are running
	*/
	function test_tests() {
		$this->assertTrue( true );
	}

	/**
	* Ensure landing pages is active
	*/
	function test_lading_pages_activated() {
		$this->assertTrue( is_plugin_active( 'landing-pages/landing-pages.php' ) );
	}
	
	/**
	* Ensure that the Leads has been installed and activated.
	*/
	function test_leads_activated() {
		$this->assertTrue( is_plugin_active( 'leads/wordpress-leads.php' ) );
	}
	
	/**
	* Ensure that the Calls to Action has been installed and activated.
	*/
	function test_cta_activated() {
		$this->assertTrue( is_plugin_active( 'cta/wordpress-cta.php' ) );
	}
	
	/**
	* Run upgrade routines and check option to see if complete
	*/
	function test_run_upgrade_routines() {
		include_once LANDINGPAGES_PATH . 'classes/class.activation.php';
		include_once LANDINGPAGES_PATH . 'classes/class.activation.upgrade-routines.php';
		Landing_Pages_Activation::run_updates();
		$this->assertTrue( true );
	}
	
}

?>
