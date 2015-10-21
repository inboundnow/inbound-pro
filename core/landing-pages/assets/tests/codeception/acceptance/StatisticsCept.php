<?php
/**
*  This test is desnged to test the impressions/conversions systems of landing pages.
*  Systems tested:
*   [x] Login to WordPress
*   [x] Navigate to Landing Pages
*   [x] Open example landing page
*   [x] Check if impression/conversion UI display on landing page edit screen
*	[ ] Reset impressions/conversions and refresh page
*   [ ] Make sure stats read 0
*   [ ] Open landing page and make sure it does not 404
*   [ ] Refresh landing page and make sure variation 2 loads
*   [ ] Submit test conversion on variation 2
*   [ ] Navigate back to edit page and make sure stats read correctly
*/

$I = new AcceptanceTester($scenario);

$I->wantTo('login to wp-admin');
$I->amOnPage( site_url().'/wp-login.php' );
$I->fillField('Username', 'admin');
$I->fillField('Password','admin');
$I->click('Log In');
$I->see('Dashboard');

$I->wantTo('Navigate to landing pages list');
$I->click('Landing Pages');
$I->amOnPage( admin_url( 'edit.php?post_type=landing-page') );
$I->see( 'Landing Pages');

$I->wantTo('Open example landing page');
$I->click( [ 'link' => 'A/B Testing Landing Page Example']);
$I->wantTo('check if impressions are correct for variation a');
$imp = $I->grabTextFrom('#lp-variation-A .bab-stat-span-impressions');
$I->assertContains( '30' , $imp );

$I->wantTo('check check impressions for variation b');
$imp = $I->grabTextFrom('#lp-variation-B .bab-stat-span-impressions');
$I->assertContains( '35' , $imp , ''  );

$I->wantTo('check conversions for variation a');
$con = $I->grabTextFrom('#lp-variation-A .bab-stat-span-conversions');
$I->assertContains( '10' , $con , '' );

$I->wantTo('check conversions for variation b');
$con = $I->grabTextFrom('#lp-variation-B .bab-stat-span-conversions');
$I->assertContains( '15' , $con  );

$I->wantTo('check the conversion rate of variation a');
$per = $I->grabTextFrom('#lp-variation-A .bab-stat-span-conversion_rate');
$I->assertContains( '33' , $per  );

$I->wantTo('check the conversion rate of variation b');
$per = $I->grabTextFrom('#lp-variation-B .bab-stat-span-conversion_rate');
$I->assertContains( '42.86' , $per  );
