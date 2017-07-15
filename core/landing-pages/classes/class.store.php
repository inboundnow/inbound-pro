<?php

/**
 * Class adds prompts to upgrade to Inbound Pro when user is using GPL Landing Pages. Also will be foundation for template installation engine once we phase out all free templates from core.
 * @package LandingPages
 * @subpackage NeedsAttention
 */
class Inbound_Now_Store {

    /**
     *
     */
    public static function store_display() {

        /* normal display here */
        self::store_redirect();

    }

    /**
     *
     */
    public static function store_redirect() { ?>
        <script>

            window.location = "https://www.inboundnow.com/market";

        </script>
        <?php
    }
}


