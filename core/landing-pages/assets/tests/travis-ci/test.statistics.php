<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class Tests_Statistics extends WP_UnitTestCase {

    /**
     * Test to see if get_post works.
     *
     * Compares a post ID ($org_post_id) with post ID
     * taken out of get_post ($new_post_id).
     * If they don't match, get_post() doesn't work, and it will
     * return an error.
     */
    function test_get_post() {
        //Create new post using method provided by WP
        $org_post_id = $this->factory->post->create();

        //get post object using the new post's ID
        $post_obj = get_post( $org_post_id );

        //Get the post ID as given to us by get_post
        $new_post_id = $post_obj->ID;

        //Use pre-defined method to test if the two ID's match
        $this->assertEquals( $org_post_id, $new_post_id );

    }

    /**
     * creates a dummy landing page for testing
     */
    function test_create_demo_lander() {
        /* load the class used to create the dummy landing page */
        include_once LANDINGPAGES_PATH . 'modules/module.install.php';
        $lp_id = inbound_create_default_post_type();
        $this->assertEquals(  $lp_id  , 4 );
        echo 1;
    }



    /**
     * Check if landing-page post type exists
     */
    function test_check_if_landing_page_post_type_exist() {
        $this->assertTrue( post_type_exists( 'landing-page' ) );
    }



    /**
     * Set landing page stats to zero for testing
     */
    function test_reset_landing_page_stats() {
        echo 2;
        print_r( get_option( 'lp_settings_general' ) );
        $landing_page = get_post( 4 );
        var_dump($landing_page);

    }

}


