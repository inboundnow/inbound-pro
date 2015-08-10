<?php 

$I = new AcceptanceTester($scenario);
$I->wantTo('Make sure the default WordPress homepage loads.');
$I->amOnPage( site_url() );
$I->see('Hello world!');

