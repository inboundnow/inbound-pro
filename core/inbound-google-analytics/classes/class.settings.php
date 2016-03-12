<?php


class Inbound_Analytics_Settings {
	
	public static function get_settings() {
		return get_option('inbound_ga' , false);
	}
	
}