<?php
/**
 * Plugin Name: WPPT UTM Handler
 * Plugin URI: https://wpperformancetuner.com/
 * Description: Extracts query string params & embeds into html forms.
 * Version: 0.9.0
 * Author: Steven Waskey
 * Author URI: https://www.stevenawaskey.com/
 * Text Domain: wppt-utm-handler
 * @package  wppt-utm-handler
 */
/**
 * The main WPPT UTM Handler class
 *
 * @since 0.1
 */
class WPPT_UTM_Handler 
{

	// ?leadsource=NSIFacebook®ion=Local&location=Clearwater (php)
	// ?leadsource=NSIFacebook&region=Local&location=Clearwater (js)

	private $formId = false;

	public function __construct( $attributes ) 
	{
		$this->formId = $attributes['id'];
	}

	private function fetchParams()
	{
		// ---------------------------------------------------
		// Pull params.
		$params = array_filter([
			"leadsource" => (array_key_exists('leadsource', $_GET) ? $_GET['leadsource'] : FALSE), 
			"location" => (array_key_exists('location', $_GET) ? $_GET['location'] : FALSE), 
			"region" => array_key_exists('region', $_GET) ? $_GET['region'] : FALSE,
			"utm" => $_SERVER['QUERY_STRING'] ? "?".htmlspecialchars($_SERVER['QUERY_STRING']) : FALSE,
			"ip" => $_SERVER['REMOTE_ADDR']
		],function($r){ return $r !== FALSE; });




		// ---------------------------------------------------
		// Validate And/Or Set Custom Region.
		$cities = ["springhill", "orlando", "brandon", "clearwater"];
		if(array_key_exists("region", $params)) {
			if(!in_array(strtolower(preg_replace('/[^A-Za-z]/', "", $params["region"])), ["local", "notlocal"]))
				unset($params["region"]);
		}
		if(!array_key_exists("region", $params) && array_key_exists("location", $params))
			$params["region"] = in_array(strtolower(preg_replace('/[^A-Za-z]/', "", $params["location"])), $cities) ? "Local" : "Not Local";



		return $params;
	}

	public function run()
	{
		return 		"<div id='".$this->formId."_tracking'>"
			. 			json_encode($this->fetchParams())
			.		"</div>";
	}
}

/**
 * Handle the wppt-utm-handler shortcode.
 *
 * @param  array 		$attributes Provided by WordPress core. Contains the shortcode attributes.
 * @return null
 */
function wppt_utm_handler_shortcode_handler($attributes) 
{

	$attributes = shortcode_atts(
		array(
			'id'					=> false
		), $attributes, 'wppt-utm-handler'
	);

	return (new WPPT_UTM_Handler( $attributes ))->run();
}

add_shortcode( 'wppt-utm-handler', 'wppt_utm_handler_shortcode_handler' );