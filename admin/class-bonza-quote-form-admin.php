<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/iamceejay
 * @since      1.0.0
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/admin
 * @author     Jasper Cairoane Poly <jaspercairoane.poly@gmail.com>
 */
class Bonza_Quote_Form_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bonza-quote-form-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bonza-quote-form-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script(
			$this->plugin_name,
			'bonza_quote_admin_ajax',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('bonza_quote_admin_nonce'),
				'messages' => array(
					'confirm_delete' => __('Are you sure you want to delete this quote?', 'bonza-quote-form'),
					'success'        => __('Action completed successfully.', 'bonza-quote-form'),
					'error'          => __('An error occurred. Please try again.', 'bonza-quote-form'),
				)
			)
		);
	}

	public function init_admin_hooks() {
		add_action(
			'admin_init',
			array(
				$this,
				'handle_quote_actions_early'
			)
		);
	}

	public function handle_quote_actions_early() {
		if(!isset($_GET['page']) || $_GET['page'] !== 'bonza-quotes') {
			return;
		}

		if(!isset($_GET['action']) || !isset($_GET['quote']) || !isset($_GET['_wpnonce'])) {
			return;
		}
	
		$action = sanitize_text_field($_GET['action']);
		$quote_id = intval($_GET['quote']);
		$nonce = sanitize_text_field($_GET['_wpnonce']);

		if($action === 'view') {
			return;
		}

		if(!current_user_can('manage_options')) {
			wp_die(__('You do not have permission to perform this action.', 'bonza-quote-form'));
		}
	
		switch ($action) {
			case 'approve':
				if(wp_verify_nonce($nonce, 'approve_quote_' . $quote_id)) {
					$result = Bonza_Quote_Form_Quote::update_status($quote_id, 'approved');
					
					if(!is_wp_error($result)) {
						set_transient(
							'bonza_quote_admin_notice',
							array(
								'type' => 'success',
								'message' => __('Quote approved successfully.', 'bonza-quote-form')
							),
						30);
					}
					
					wp_redirect(admin_url('admin.php?page=bonza-quotes&action=view&quote=' . $quote_id));
					
					exit;
				}
				break;
	
			case 'reject':
				if(wp_verify_nonce($nonce, 'reject_quote_' . $quote_id)) {
					$result = Bonza_Quote_Form_Quote::update_status($quote_id, 'rejected');

					if (!is_wp_error($result)) {
						set_transient(
							'bonza_quote_admin_notice',
							array(
								'type' => 'success',
								'message' => __('Quote rejected successfully.', 'bonza-quote-form')
							),
						30);
					}

					wp_redirect(admin_url('admin.php?page=bonza-quotes&action=view&quote=' . $quote_id));

					exit;
				}
				break;
	
			case 'delete':
				if(wp_verify_nonce($nonce, 'delete_quote_' . $quote_id)) {
					$result = Bonza_Quote_Form_Quote::delete($quote_id);

					if (!is_wp_error($result)) {
						set_transient(
							'bonza_quote_admin_notice',
							array(
								'type' => 'success',
								'message' => __('Quote deleted successfully.', 'bonza-quote-form')
							),
						30);
					}

					wp_redirect( admin_url('admin.php?page=bonza-quotes'));

					exit;
				}
				break;
		}
	}

	/**
	 * Add admin menu items
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			__('Bonza Quotes', 'bonza-quote-form'),
			__('Bonza Quotes', 'bonza-quote-form'),
			'manage_options',
			'bonza-quotes',
			array(
				$this,
				'display_quotes_page'
			),
			'dashicons-testimonial',
			25
		);
	}

	/**
	 * Display the quotes management page
	 *
	 * @since    1.0.0
	 */
	public function display_quotes_page() {
		if(!current_user_can( 'manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.', 'bonza-quote-form'));
		}

		require_once plugin_dir_path(__FILE__) . 'class-bonza-quote-form-list-table.php';
		
		$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

		if ($action === 'view' && isset($_GET['quote'])) {
			$this->display_quote_details(intval($_GET['quote']));
		} else {
			$this->display_quotes_list();
		}
	}

	/**
	 * Display the quotes list
	 *
	 * @since    1.0.0
	 */
	private function display_quotes_list() {
		$quotes_table = new Bonza_Quote_Form_List_Table();
		$quotes_table->prepare_items();

		$status_counts = Bonza_Quote_Form_Quote::get_status_counts();

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e('Bonza Quotes', 'bonza-quote-form'); ?></h1>
			
			<?php $this->display_admin_notices(); ?>
			<div class="bonza-quote-stats-container" style="margin: 20px 0;">
				<div class="bonza-quote-stat-card" style="background: #f0ad4e;">
					<h3><?php echo esc_html($status_counts['pending']); ?></h3>
					<p><?php esc_html_e('Pending', 'bonza-quote-form'); ?></p>
				</div>
				<div class="bonza-quote-stat-card" style="background: #5cb85c;">
					<h3><?php echo esc_html($status_counts['approved']); ?></h3>
					<p><?php esc_html_e('Approved', 'bonza-quote-form'); ?></p>
				</div>
				<div class="bonza-quote-stat-card" style="background: #d9534f;">
					<h3><?php echo esc_html($status_counts['rejected']); ?></h3>
					<p><?php esc_html_e('Rejected', 'bonza-quote-form'); ?></p>
				</div>
				<div class="bonza-quote-stat-card" style="background: #5bc0de;">
					<h3><?php echo esc_html($status_counts['total']); ?></h3>
					<p><?php esc_html_e('Total', 'bonza-quote-form'); ?></p>
				</div>
			</div>

			<form id="quotes-filter" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
				<?php 
					$quotes_table->search_box(__('Search quotes', 'bonza-quote-form'), 'quote-search-input');
					$quotes_table->display(); 
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display quote details
	 *
	 * @since    1.0.0
	 * @param    int    $quote_id    Quote ID
	 */
	private function display_quote_details($quote_id) {
		$quote = Bonza_Quote_Form_Quote::get_by_id($quote_id);
		
		if (!$quote) {
			echo '<div class="wrap"><h1>' . __('Quote Not Found', 'bonza-quote-form') . '</h1>';
			echo '<p>' . __('The requested quote could not be found.', 'bonza-quote-form') . '</p></div>';

			return;
		}

		$status_labels = array(
			'pending'  => array(
				'label' => __('Pending', 'bonza-quote-form'),
				'color' => '#f0ad4e'
			),
			'approved' => array(
				'label' => __('Approved', 'bonza-quote-form'),
				'color' => '#5cb85c'
			),
			'rejected' => array(
				'label' => __('Rejected', 'bonza-quote-form'),
				'color' => '#d9534f'
			),
		);

		/**
		 * Filter to modify status labels and colors
		 *
		 * @since 1.0.0
		 * @param array $status_labels Status labels array
		 * @param object $quote Quote object
		 */
		$status_labels = apply_filters('bonza_quote_form_admin_status_labels', $status_labels, $quote);

		/**
		 * Filter to modify variables before template inclusion
		 *
		 * @since 1.0.0
		 * @param array $template_vars Variables to pass to template
		 */
		$template_vars = apply_filters(
			'bonza_quote_form_admin_details_template_vars',
			array(
				'quote'         => $quote,
				'status_labels' => $status_labels,
			)
		);

		extract($template_vars);

		/**
		 * Action hook before template inclusion
		 *
		 * @since 1.0.0
		 * @param array $template_vars Template variables
		 */
		do_action('bonza_quote_form_admin_details_before_template', $template_vars);

		$this->display_admin_notices();

		include plugin_dir_path(__FILE__) . 'partials/bonza-quote-form-admin-display.php';

		/**
		 * Action hook after template inclusion
		 *
		 * @since 1.0.0
		 * @param array $template_vars Template variables
		 */
		do_action('bonza_quote_form_admin_details_after_template', $template_vars);
	}

	/**
	 * Display admin notices
	 *
	 * @since    1.0.0
	 */
	private function display_admin_notices() {
		$notice = get_transient( 'bonza_quote_admin_notice' );
		
		if ( $notice ) {
			$class = $notice['type'] === 'success' ? 'notice-success' : 'notice-error';
			printf( '<div class="notice %s is-dismissible"><p>%s</p></div>', 
				esc_attr( $class ), 
				esc_html( $notice['message'] ) 
			);
			delete_transient( 'bonza_quote_admin_notice' );
		}
	}

	/**
	 * Add dashboard widget
	 *
	 * @since    1.0.0
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'bonza_quote_dashboard_widget',
			__('Recent Quote Requests', 'bonza-quote-form'),
			array(
				$this,
				'dashboard_widget_content'
			)
		);
	}

	/**
	 * Dashboard widget content
	 *
	 * @since    1.0.0
	 */
	public function dashboard_widget_content() {
		$recent_quotes = Bonza_Quote_Form_Quote::get_all(array('per_page' => 5));
		$status_counts = Bonza_Quote_Form_Quote::get_status_counts();

		?>
		<div class="bonza-dashboard-widget">
			<div class="bonza-quote-summary">
				<p><strong><?php printf(__('Pending: %d', 'bonza-quote-form'), $status_counts['pending']); ?></strong></p>
				<p><?php printf(__('Total: %d', 'bonza-quote-form'), $status_counts['total']); ?></p>
			</div>

			<?php if(!empty($recent_quotes['quotes'])) : ?>
				<h4><?php esc_html_e('Recent Submissions', 'bonza-quote-form'); ?></h4>
				<ul>
					<?php foreach ($recent_quotes['quotes'] as $quote) : ?>
						<li>
							<a href="<?php echo esc_url(admin_url('admin.php?page=bonza-quotes&action=view&quote=' . $quote->id)); ?>">
								<?php echo esc_html($quote->name); ?> - <?php echo esc_html( $quote->service_type); ?>
							</a>
							<span class="quote-date"><?php echo esc_html(mysql2date('M j', $quote->created_at)); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e('No quote requests yet.', 'bonza-quote-form'); ?></p>
			<?php endif; ?>

			<p>
				<a href="<?php echo esc_url(admin_url('admin.php?page=bonza-quotes')); ?>" class="button">
					<?php esc_html_e('View All Quotes', 'bonza-quote-form'); ?>
				</a>
			</p>
		</div>
		<?php
	}

}
