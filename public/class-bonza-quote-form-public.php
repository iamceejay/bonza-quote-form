<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/iamceejay
 * @since      1.0.0
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/public
 * @author     Jasper Cairoane Poly <jaspercairoane.poly@gmail.com>
 */
class Bonza_Quote_Form_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bonza_Quote_Form_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bonza_Quote_Form_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bonza-quote-form-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bonza_Quote_Form_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bonza_Quote_Form_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bonza-quote-form-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode(
			'bonza_quote_form',
			array(
				$this,
				'render_quote_form_shortcode'
			)
		);
	}

	/**
	 * Render the quote form shortcode
	 *
	 * @since    1.0.0
	 * @param    array     $atts    Shortcode attributes
	 * @param    string    $content Shortcode content
	 * @return   string             Form HTML
	 */
	public function render_quote_form_shortcode($atts, $content = null) {
		$attributes = shortcode_atts(array(
			'title'          => __('Get a Quote', 'bonza-quote-form'),
			'submit_text'    => __('Submit Quote Request', 'bonza-quote-form'),
			'show_title'     => 'true',
			'ajax'           => 'true',
			'redirect_url'   => '',
			'service_types'  => '',
		), $atts);

		$attributes = apply_filters(
			'bonza_quote_form_shortcode_attributes',
			$attributes
		);

		ob_start();

		$this->render_quote_form($attributes);

		return ob_get_clean();
	}

	/**
	 * Render the quote form
	 *
	 * @since    1.0.0
	 * @param    array    $attributes    Form attributes
	 */
	private function render_quote_form($attributes) {
		include plugin_dir_path( __FILE__ ) . 'partials/bonza-quote-form-public-display.php';
	}

}
