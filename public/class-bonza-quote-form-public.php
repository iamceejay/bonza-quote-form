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

		wp_localize_script(
			$this->plugin_name,
			'bonza_quote_ajax',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('bonza_quote_form_nonce'),
				'messages' => array(
					'success'       => __('Thank you! Your quote request has been submitted successfully.', 'bonza-quote-form'),
					'error'         => __('Sorry, there was an error submitting your request. Please try again.', 'bonza-quote-form'),
					'validation'    => __('Please check the form for errors and try again.', 'bonza-quote-form'),
					'processing'    => __('Processing...', 'bonza-quote-form'),
					'submit'        => __('Submit Quote Request', 'bonza-quote-form')
				)
			)
		);
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
		$service_types = $this->parse_service_types($attributes['service_types']);
		
		$form_id = 'bonza-quote-form-' . uniqid();
		$form_submitted = false;
		$form_message = '';
		$form_errors = array();
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['bonza_quote_submit'])) {
			$result = $this->handle_form_submission();

			if(is_wp_error($result)) {
				$form_errors = $result->get_error_messages();
			} else {
				$form_submitted = true;
				$form_message = __('Thank you! Your quote request has been submitted successfully.', 'bonza-quote-form');
			}
		}

		/**
		 * Filter to modify variables before template inclusion
		 *
		 * @since 1.0.0
		 * @param array $template_vars Variables to pass to template
		 */
		$template_vars = apply_filters(
			'bonza_quote_form_template_vars',
			array(
				'attributes'     => $attributes,
				'service_types'  => $service_types,
				'form_id'        => $form_id,
				'form_submitted' => $form_submitted,
				'form_message'   => $form_message,
				'form_errors'    => $form_errors,
			)
		);

		extract($template_vars);

		/**
		 * Action hook before form template inclusion
		 *
		 * @since 1.0.0
		 * @param array $template_vars Template variables
		 */
		do_action(
			'bonza_quote_form_before_template',
			$template_vars
		);

		include plugin_dir_path(__FILE__) . 'partials/bonza-quote-form-public-display.php';

		/**
		 * Action hook after form template inclusion
		 *
		 * @since 1.0.0
		 * @param array $template_vars Template variables
		 */
		do_action(
			'bonza_quote_form_after_template',
			$template_vars
		);
	}

	/**
	 * Parse service types from shortcode attribute
	 *
	 * @since    1.0.0
	 * @param    string    $service_types_string    Comma-separated service types
	 * @return   array                              Array of service types
	 */
	private function parse_service_types($service_types_string) {
		if(empty($service_types_string)) {
			return array();
		}

		$service_types = explode(',', $service_types_string);
		$service_types = array_map('trim', $service_types);
		$service_types = array_filter($service_types);

		return apply_filters(
			'bonza_quote_form_service_types',
			$service_types
		);
	}

	/**
	 * Handle AJAX form submission
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_quote_submission() {
		if(!wp_verify_nonce($_POST['bonza_quote_nonce'], 'bonza_quote_form_nonce')) {
			wp_send_json_error(array(
				'message' => __('Security check failed. Please refresh the page and try again.', 'bonza-quote-form')
			));
		}

		$result = $this->handle_form_submission();

		if(is_wp_error($result)) {
			wp_send_json_error(array(
				'message' => __('Please correct the following errors:', 'bonza-quote-form'),
				'errors'  => $result->get_error_messages()
			));
		} else {
			$response = array(
				'message' => __('Thank you! Your quote request has been submitted successfully.', 'bonza-quote-form'),
				'quote_id' => $result
			);

			if (!empty($_POST['bonza_quote_redirect'])) {
				$response['redirect'] = esc_url_raw($_POST['bonza_quote_redirect']);
			}

			wp_send_json_success($response);
		}
	}

	/**
	 * Handle form submission (both AJAX and regular)
	 *
	 * @since    1.0.0
	 * @return   int|WP_Error    Quote ID on success, WP_Error on failure
	 */
	private function handle_form_submission() {
		$quote_data = array(
			'name'         => sanitize_text_field($_POST['bonza_quote_name']),
			'email'        => sanitize_email($_POST['bonza_quote_email']),
			'service_type' => sanitize_text_field($_POST['bonza_quote_service_type']),
			'notes'        => sanitize_textarea_field($_POST['bonza_quote_notes']),
			'status'       => 'pending'
		);

		$quote_data = apply_filters(
			'bonza_quote_form_submission_data',
			$quote_data
		);

		$quote = new Bonza_Quote_Form_Quote($quote_data);
		
		$result = $quote->save();

		if(!is_wp_error($result)) {
			do_action(
				'bonza_quote_form_submitted',
				$result,
				$quote
			);
		}

		return $result;
	}

	/**
	 * Register AJAX handlers
	 *
	 * @since    1.0.0
	 */
	public function register_ajax_handlers() {
		add_action(
			'wp_ajax_bonza_quote_submit',
			array(
				$this,
				'handle_ajax_quote_submission'
			)
		);

		add_action(
			'wp_ajax_nopriv_bonza_quote_submit',
			array(
				$this,
				'handle_ajax_quote_submission'
			)
		);
	}

	/**
	 * Conditionally enqueue scripts only when shortcode is present
	 *
	 * @since    1.0.0
	 */
	public function maybe_enqueue_scripts() {
		global $post;
		
		if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'bonza_quote_form')) {
			$this->enqueue_styles();
			$this->enqueue_scripts();
		}
	}
}
