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
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.', 'bonza-quote-form'));
		}

		$this->handle_single_quote_actions();

		require_once plugin_dir_path(__FILE__) . 'class-bonza-quote-form-list-table.php';
		
		$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

		if($action === 'view' && isset($_GET['quote'])) {
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
		
		if(!$quote) {
			echo '<div class="wrap"><h1>' . __('Quote Not Found', 'bonza-quote-form') . '</h1>';
			echo '<p>' . __('The requested quote could not be found.', 'bonza-quote-form') . '</p></div>';

			return;
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e('Quote Details', 'bonza-quote-form'); ?></h1>
			
			<a href="<?php echo esc_url(admin_url('admin.php?page=bonza-quotes')); ?>" class="page-title-action">
				<?php esc_html_e('← Back to Quotes', 'bonza-quote-form'); ?>
			</a>

			<?php $this->display_admin_notices(); ?>

			<div class="bonza-quote-details-container">
				<div class="bonza-quote-details-card">
					<h2><?php esc_html_e('Quote Information', 'bonza-quote-form'); ?></h2>
					
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e('ID', 'bonza-quote-form'); ?></th>
							<td><?php echo esc_html($quote->id); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Name', 'bonza-quote-form'); ?></th>
							<td><?php echo esc_html($quote->name); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Email', 'bonza-quote-form'); ?></th>
							<td><a href="mailto:<?php echo esc_attr($quote->email); ?>"><?php echo esc_html($quote->email); ?></a></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Service Type', 'bonza-quote-form'); ?></th>
							<td><?php echo esc_html($quote->service_type); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Status', 'bonza-quote-form'); ?></th>
							<td>
								<form method="post" style="display: inline;">
									<?php wp_nonce_field('update_quote_status_' . $quote->id, '_wpnonce'); ?>
									<select name="quote_status" onchange="this.form.submit()">
										<option value="pending" <?php selected($quote->status, 'pending'); ?>><?php esc_html_e( 'Pending', 'bonza-quote-form' ); ?></option>
										<option value="approved" <?php selected($quote->status, 'approved'); ?>><?php esc_html_e( 'Approved', 'bonza-quote-form' ); ?></option>
										<option value="rejected" <?php selected($quote->status, 'rejected'); ?>><?php esc_html_e( 'Rejected', 'bonza-quote-form' ); ?></option>
									</select>
									<input type="hidden" name="action" value="update_status">
									<input type="hidden" name="quote_id" value="<?php echo esc_attr($quote->id); ?>">
								</form>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Date Submitted', 'bonza-quote-form'); ?></th>
							<td>
								<?php
									echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $quote->created_at));
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Last Updated', 'bonza-quote-form'); ?></th>
							<td>
								<?php 
									echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $quote->updated_at));
								?>
							</td>
						</tr>
					</table>

					<?php if(!empty($quote->notes)) : ?>
						<h3><?php esc_html_e('Additional Notes', 'bonza-quote-form'); ?></h3>
						<div class="bonza-quote-notes">
							<?php echo wp_kses_post(nl2br(esc_html($quote->notes))); ?>
						</div>
					<?php endif; ?>

					<div class="bonza-quote-actions">
						<h3><?php esc_html_e('Actions', 'bonza-quote-form'); ?></h3>
						
						<?php if($quote->status !== 'approved') : ?>
							<a href="<?php echo esc_url(
									wp_nonce_url(
										add_query_arg(
											array(
												'action' => 'approve',
												'quote' => $quote->id
											)
										),
										'approve_quote_' . $quote->id
									)
								); ?>" 
							   class="button button-primary">
								<?php esc_html_e('Approve Quote', 'bonza-quote-form'); ?>
							</a>
						<?php endif; ?>

						<?php if($quote->status !== 'rejected') : ?>
							<a href="<?php echo esc_url(
									wp_nonce_url(
										add_query_arg(
											array(
												'action' => 'reject',
												'quote' => $quote->id
											)
										),
									'reject_quote_' . $quote->id
									)
								); ?>" 
							   class="button">
								<?php esc_html_e('Reject Quote', 'bonza-quote-form'); ?>
							</a>
						<?php endif; ?>

						<a href="<?php echo esc_url(
									wp_nonce_url(
										add_query_arg(
											array(
												'action' => 'delete',
												'quote' => $quote->id
											)
										),
										'delete_quote_' . $quote->id
									)
								); ?>" 
						   class="button button-link-delete" 
						   onclick="return confirm('<?php echo esc_js(__( 'Are you sure you want to delete this quote?', 'bonza-quote-form')); ?>')">
							<?php esc_html_e('Delete Quote', 'bonza-quote-form'); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle single quote actions
	 *
	 * @since    1.0.0
	 */
	private function handle_single_quote_actions() {
		if(!isset($_GET['action']) || !isset($_GET['quote'])) {
			return;
		}

		$action = sanitize_text_field($_GET['action']);
		$quote_id = intval($_GET['quote']);

		if(isset($_POST['action']) && $_POST['action'] === 'update_status' && isset($_POST['quote_id'])) {
			if(!wp_verify_nonce($_POST['_wpnonce'], 'update_quote_status_' . intval($_POST['quote_id']))) {
				wp_die(__('Security check failed.', 'bonza-quote-form'));
			}

			$quote_id = intval($_POST['quote_id']);
			$new_status = sanitize_text_field($_POST['quote_status']);
			$result = Bonza_Quote_Form_Quote::update_status($quote_id, $new_status);
			
			if (!is_wp_error($result)) {
				set_transient(
					'bonza_quote_admin_notice',
					array( 
						'type' => 'success', 
						'message' => __('Quote status updated successfully.', 'bonza-quote-form')
					),
				30);
			} else {
				set_transient(
					'bonza_quote_admin_notice',
					array( 
						'type' => 'error', 
						'message' => $result->get_error_message() 
					),
				30);
			}

			wp_redirect(remove_query_arg(array('action', '_wpnonce')));

			exit;
		}

		switch ($action) {
			case 'approve':
				if(!wp_verify_nonce($_GET['_wpnonce'], 'approve_quote_' . $quote_id)) {
					wp_die(__('Security check failed.', 'bonza-quote-form'));
				}

				$result = Bonza_Quote_Form_Quote::update_status($quote_id, 'approved');

				$this->set_action_notice($result, __('Quote approved successfully.', 'bonza-quote-form'));

				break;
			case 'reject':
				if(!wp_verify_nonce($_GET['_wpnonce'], 'reject_quote_' . $quote_id)) {
					wp_die(__('Security check failed.', 'bonza-quote-form'));
				}

				$result = Bonza_Quote_Form_Quote::update_status($quote_id, 'rejected');

				$this->set_action_notice($result, __('Quote rejected successfully.', 'bonza-quote-form'));

				break;
			case 'delete':
				if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_quote_' . $quote_id)) {
					wp_die(__('Security check failed.', 'bonza-quote-form'));
				}

				$result = Bonza_Quote_Form_Quote::delete($quote_id);
				$this->set_action_notice($result, __('Quote deleted successfully.', 'bonza-quote-form'));
				
				wp_redirect(admin_url('admin.php?page=bonza-quotes'));

				exit;

				break;
		}
	}

	/**
	 * Set action notice based on result
	 *
	 * @since    1.0.0
	 * @param    mixed     $result     Action result
	 * @param    string    $success_message    Success message
	 */
	private function set_action_notice( $result, $success_message ) {
		if ( ! is_wp_error( $result ) && $result !== false ) {
			set_transient( 'bonza_quote_admin_notice', array( 
				'type' => 'success', 
				'message' => $success_message 
			), 30 );
		} else {
			$error_message = is_wp_error( $result ) ? $result->get_error_message() : __( 'Action failed.', 'bonza-quote-form' );
			set_transient( 'bonza_quote_admin_notice', array( 
				'type' => 'error', 
				'message' => $error_message 
			), 30 );
		}
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
