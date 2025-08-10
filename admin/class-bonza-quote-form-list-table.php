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

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Bonza Quotes List Table Class
 *
 * @since      1.0.0
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/admin
 * @author     Jasper Cairoane Poly <jaspercairoane.poly@gmail.com>
 */
class Bonza_Quote_Form_List_Table extends WP_List_Table {

	/**
	 * Number of quotes per page
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int
	 */
	private $per_page = 20;

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		parent::__construct(array(
			'singular' => 'quote',
			'plural'   => 'quotes',
			'ajax'     => false,
		));
	}

	/**
	 * Get list of columns
	 *
	 * @since    1.0.0
	 * @return   array    Columns array
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'name'         => __('Name', 'bonza-quote-form'),
			'email'        => __('Email', 'bonza-quote-form'),
			'service_type' => __('Service Type', 'bonza-quote-form'),
			'notes'        => __('Notes', 'bonza-quote-form'),
			'status'       => __('Status', 'bonza-quote-form'),
			'created_at'   => __('Date Submitted', 'bonza-quote-form'),
		);

		return apply_filters('bonza_quote_form_admin_columns', $columns);
	}

	/**
	 * Get sortable columns
	 *
	 * @since    1.0.0
	 * @return   array    Sortable columns array
	 */
	public function get_sortable_columns() {
		$sortable = array(
			'name'       => array('name', false),
			'email'      => array('email', false),
			'status'     => array('status', false),
			'created_at' => array('created_at', true),
		);

		return apply_filters('bonza_quote_form_admin_sortable_columns', $sortable);
	}

	/**
	 * Get bulk actions
	 *
	 * @since    1.0.0
	 * @return   array    Bulk actions array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'approve' => __('Approve', 'bonza-quote-form'),
			'reject'  => __('Reject', 'bonza-quote-form'),
			'pending' => __('Mark as Pending', 'bonza-quote-form'),
			'delete'  => __('Delete', 'bonza-quote-form'),
		);

		return apply_filters('bonza_quote_form_admin_bulk_actions', $actions);
	}

	/**
	 * Process bulk actions
	 *
	 * @since    1.0.0
	 */
	public function process_bulk_action() {
		$action = $this->current_action();
		$quote_ids = isset($_REQUEST['quote']) ? (array) $_REQUEST['quote'] : array();

		if(empty($quote_ids) || !$action) {
			return;
		}

		if(!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
			wp_die(__('Security check failed.', 'bonza-quote-form'));
		}

		if(!current_user_can( 'manage_options')) {
			wp_die(__('You do not have sufficient permissions.', 'bonza-quote-form'));
		}

		$success_count = 0;
		$error_count = 0;

		foreach($quote_ids as $quote_id) {
			$quote_id = intval($quote_id);

			switch($action) {
				case 'approve':
					$result = Bonza_Quote_Form_Quote::update_status($quote_id, 'approved');
					break;
				case 'reject':
					$result = Bonza_Quote_Form_Quote::update_status($quote_id, 'rejected');
					break;
				case 'pending':
					$result = Bonza_Quote_Form_Quote::update_status($quote_id, 'pending');
					break;
				case 'delete':
					$result = Bonza_Quote_Form_Quote::delete( $quote_id);
					break;
				default:
					$result = false;
					break;
			}

			if(!is_wp_error($result) && $result !== false) {
				$success_count++;
			} else {
				$error_count++;
			}
		}

		if($success_count > 0) {
			$message = sprintf(
				_n('%d quote processed successfully.', '%d quotes processed successfully.', $success_count, 'bonza-quote-form'),
				$success_count
			);

			set_transient(
                'bonza_quote_admin_notice',
                array(
                    'type' => 'success',
                    'message' => $message
                ),
            30);
		}

		if($error_count > 0) {
			$message = sprintf(
				_n('%d quote failed to process.', '%d quotes failed to process.', $error_count, 'bonza-quote-form'),
				$error_count
			);

			set_transient(
                'bonza_quote_admin_notice',
                array(
                    'type' => 'error',
                    'message' => $message
                ),
            30 );
		}

		$redirect_url = remove_query_arg(
            array(
                'action',
                'action2',
                'quote',
                '_wpnonce'
            )
        );

		wp_redirect($redirect_url);

		exit;
	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @since    1.0.0
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$current_page = $this->get_pagenum();

		$search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
		$status_filter = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '';

		$orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'created_at';
		$order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'DESC';

		$args = array(
			'per_page' => $this->per_page,
			'page'     => $current_page,
			'search'   => $search,
			'status'   => $status_filter,
			'orderby'  => $orderby,
			'order'    => $order,
		);

		$quotes_data = Bonza_Quote_Form_Quote::get_all($args);

		$this->items = $quotes_data['quotes'];

		$this->set_pagination_args(
            array(
                'total_items' => $quotes_data['total_items'],
                'per_page'    => $this->per_page,
                'total_pages' => $quotes_data['total_pages'],
		    )
        );

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @since    1.0.0
	 * @param    Bonza_Quote_Form_Quote    $item         
	 * @param    string                    $column_name  
	 * @return   mixed                                   
	 */
	public function column_default($item, $column_name) {
		switch($column_name) {
			case 'name':
				return esc_html($item->name);
			case 'email':
				return sprintf(
                    '<a href="mailto:%s">%s</a>',
                    esc_attr($item->email),
                    esc_html($item->email)
                );
			case 'service_type':
				return esc_html($item->service_type);
			case 'notes':
				$notes = wp_trim_words($item->notes, 15, '...');
				return esc_html($notes);
			case 'created_at':
				return mysql2date(
                    get_option('date_format' ) . ' ' . get_option('time_format'),
                    $item->created_at
                );
			default:
				return apply_filters(
                    "bonza_quote_form_admin_column_{$column_name}",
                    '',
                    $item, $column_name
                );
		}
	}

	/**
	 * Render the checkbox column
	 *
	 * @since    1.0.0
	 * @param    Bonza_Quote_Form_Quote    $item    
	 * @return   string                             
	 */
	public function column_cb($item) {
		return sprintf('<input type="checkbox" name="quote[]" value="%s" />', $item->id);
	}

	/**
	 * Render the name column with row actions
	 *
	 * @since    1.0.0
	 * @param    Bonza_Quote_Form_Quote    $item    
	 * @return   string                             
	 */
	public function column_name($item) {
		$name = esc_html($item->name);

		$actions = array();

		$edit_url = add_query_arg(
            array(
                'page'   => 'bonza-quotes',
                'action' => 'view',
                'quote'  => $item->id,
		    ),
            admin_url('admin.php')
        );

		$actions['view'] = sprintf('<a href="%s">%s</a>', esc_url($edit_url), __('View', 'bonza-quote-form'));

		if($item->status !== 'approved') {
			$approve_url = wp_nonce_url(
                add_query_arg(
                    array(
                        'page'   => 'bonza-quotes',
                        'action' => 'approve',
                        'quote'  => $item->id,
                    ),
                admin_url('admin.php')),
                'approve_quote_' . $item->id
            );

			$actions['approve'] = sprintf('<a href="%s">%s</a>', esc_url($approve_url), __('Approve', 'bonza-quote-form'));
		}

		if($item->status !== 'rejected') {
			$reject_url = wp_nonce_url(
                add_query_arg(
                    array(
                        'page'   => 'bonza-quotes',
                        'action' => 'reject',
                        'quote'  => $item->id,
                    ),
                admin_url('admin.php')),
                'reject_quote_' . $item->id
            );

			$actions['reject'] = sprintf('<a href="%s">%s</a>', esc_url($reject_url), __('Reject', 'bonza-quote-form'));
		}

		$delete_url = wp_nonce_url(
            add_query_arg(
                array(
                    'page'   => 'bonza-quotes',
                    'action' => 'delete',
                    'quote'  => $item->id,
                ),
            admin_url('admin.php')),
            'delete_quote_' . $item->id
        );

		$actions['delete'] = sprintf('<a href="%s" onclick="return confirm(\'%s\')">%s</a>', 
			esc_url($delete_url), 
			esc_js(__('Are you sure you want to delete this quote?', 'bonza-quote-form')),
			__('Delete', 'bonza-quote-form')
		);

		$actions = apply_filters(
            'bonza_quote_form_admin_row_actions',
            $actions,
            $item
        );

		return sprintf('%s %s', $name, $this->row_actions($actions));
	}

	/**
	 * Render the status column
	 *
	 * @since    1.0.0
	 * @param    Bonza_Quote_Form_Quote    $item    
	 * @return   string                             
	 */
	public function column_status($item) {
		$status_labels = array(
			'pending'  => array('label' => __('Pending', 'bonza-quote-form'), 'color' => '#f0ad4e'),
			'approved' => array('label' => __('Approved', 'bonza-quote-form'), 'color' => '#5cb85c'),
			'rejected' => array('label' => __('Rejected', 'bonza-quote-form'), 'color' => '#d9534f'),
		);

		$status = $item->status;
		$label = isset($status_labels[$status]) ? $status_labels[$status]['label'] : ucfirst($status);
		$color = isset($status_labels[$status]) ? $status_labels[$status]['color'] : '#666';

		return sprintf( 
			'<span style="display: inline-block; padding: 4px 8px; border-radius: 3px; background-color: %s; color: white; font-size: 11px; font-weight: bold;">%s</span>',
			esc_attr($color),
			esc_html($label)
		);
	}

	/**
	 * Display status filter dropdown
	 *
	 * @since    1.0.0
	 * @param    string    $which    
	 */
	protected function extra_tablenav($which) {
		if($which !== 'top') {
			return;
		}

		$status_counts = Bonza_Quote_Form_Quote::get_status_counts();
		$current_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

		?>
		<div class="alignleft actions">
			<select name="status">
				<option value="">
                    <?php esc_html_e('All Statuses', 'bonza-quote-form'); ?>
                </option>
				<option value="pending" <?php selected($current_status, 'pending'); ?>>
					<?php printf(__('Pending (%d)', 'bonza-quote-form'), $status_counts['pending']); ?>
				</option>
				<option value="approved" <?php selected($current_status, 'approved'); ?>>
					<?php printf(__('Approved (%d)', 'bonza-quote-form'), $status_counts['approved']); ?>
				</option>
				<option value="rejected" <?php selected( $current_status, 'rejected' ); ?>>
					<?php printf(__('Rejected (%d)', 'bonza-quote-form'), $status_counts['rejected']); ?>
				</option>
			</select>
			<?php submit_button(__('Filter', 'bonza-quote-form'), 'action', 'filter_action', false); ?>
		</div>
		<?php
	}

    /**
	 * Display message when no items found
	 *
	 * @since    1.0.0
	 */
	public function no_items() {
		esc_html_e('No quotes found.', 'bonza-quote-form');
	}
}