<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/iamceejay
 * @since      1.0.0
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/includes
 * @author     Jasper Cairoane Poly <jaspercairoane.poly@gmail.com>
 */
class Bonza_Quote_Form_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'bonza-quote-form',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
