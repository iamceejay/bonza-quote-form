<?php

/**
 * The Quote Model Class
 *
 * @link       https://github.com/iamceejay
 * @since      1.0.0
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/includes
 */

/**
 * Model class for handling CRUD operations
 *
 * This class defines all the database operations for quotes including
 * creation, reading, updating, and deletion with validation.
 *
 * @since      1.0.0
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/includes
 * @author     Jasper Cairoane Poly <jaspercairoane.poly@gmail.com>
 */
class Bonza_Quote_Form_Quote {

	/**
	 * Quote ID
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      int
	 */
	public $id;

	/**
	 * Quote Name
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $name;

	/**
	 * Quote Email
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $email;

	/**
	 * Service Type
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $service_type;

	/**
	 * Notes
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $notes;

	/**
	 * Status
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $status;

	/**
	 * Created timestamp
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $created_at;

	/**
	 * Updated timestamp
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $updated_at;

	/**
	 * Valid statuses
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array
	 */
	private static $valid_statuses = array('pending', 'approved', 'rejected');

	/**
	 * Table name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private static $table_name;

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 * @param    array    $data    Quote data array
	 */
	public function __construct($data = array()) {
		global $wpdb;

        if(!self::$table_name) {
			self::$table_name = $wpdb->prefix . 'bonza_quotes';
		}

		if(!empty($data)) {
			$this->populate($data);
		}
	}

    /**
	 * Populate object properties from array
	 *
	 * @since    1.0.0
	 * @param    array    $data    Data array
	 */
	private function populate($data) {
		$this->id = isset($data['id']) ? intval($data['id']) : null;
		$this->name = isset($data['name']) ? sanitize_text_field($data['name']) : '';
		$this->email = isset($data['email']) ? sanitize_email($data['email']) : '';
		$this->service_type = isset($data['service_type']) ? sanitize_text_field($data['service_type']) : '';
		$this->notes = isset($data['notes']) ? sanitize_textarea_field($data['notes']) : '';
		$this->status = isset($data['status']) ? sanitize_text_field($data['status']) : 'pending';
		$this->created_at = isset($data['created_at']) ? $data['created_at'] : null;
		$this->updated_at = isset($data['updated_at']) ? $data['updated_at'] : null;
	}

    /**
	 * Save quote to database
	 *
	 * @since    1.0.0
	 * @return   int|WP_Error    Quote ID on success, WP_Error on failure
	 */
	public function save() {
		global $wpdb;

		$validation = $this->validate();
		if(is_wp_error($validation)) {
			return $validation;
		}

		$data = array(
			'name'         => $this->name,
			'email'        => $this->email,
			'service_type' => $this->service_type,
			'notes'        => $this->notes,
			'status'       => $this->status,
		);

		$format = array('%s', '%s', '%s', '%s', '%s');

		if($this->id) {
			$result = $wpdb->update(
				self::$table_name,
				$data,
				array('id' => $this->id),
				$format,
				array('%d')
			);

			if(false === $result) {
				return new WP_Error(
                    'db_update_error',
                    __('Failed to update quote.', 'bonza-quote-form')
                );
			}

			do_action(
                'bonza_quote_updated',
                $this->id,
                $this
            );

			return $this->id;
		} else {
			$result = $wpdb->insert(
				self::$table_name,
				$data,
				$format
			);

			if(false === $result) {
				return new WP_Error(
                    'db_insert_error',
                    __('Failed to save quote.', 'bonza-quote-form')
                );
			}

			$this->id = $wpdb->insert_id;

			do_action(
                'bonza_quote_created',
                $this->id,
                $this
            );

			return $this->id;
		}
	}

    /**
	 * Validate quote data
	 *
	 * @since    1.0.0
	 * @return   bool|WP_Error    True on success, WP_Error on failure
	 */
	public function validate() {
		$errors = new WP_Error();

		if(empty(trim( $this->name ))) {
			$errors->add(
                'name_required',
                __('Name is required.', 'bonza-quote-form')
            );
		} elseif (strlen($this->name) > 255) {
			$errors->add(
                'name_too_long',
                __('Name must be less than 255 characters.', 'bonza-quote-form')
            );
		}

		if (empty(trim($this->email))) {
			$errors->add(
                'email_required',
                __('Email is required.', 'bonza-quote-form')
            );
		} elseif (!is_email($this->email)) {
			$errors->add(
                'email_invalid',
                __('Please enter a valid email address.', 'bonza-quote-form')
            );
		}

		if (empty(trim($this->service_type))) {
			$errors->add(
                'service_type_required',
                __('Service type is required.', 'bonza-quote-form')
            );
		}

		if (!in_array($this->status, self::$valid_statuses)) {
			$errors->add(
                'status_invalid',
                __('Invalid status provided.', 'bonza-quote-form')
            );
		}

		$errors = apply_filters(
            'bonza_quote_validation_errors',
            $errors,
            $this
        );

		return $errors->has_errors() ? $errors : true;
	}

    /**
	 * Get quote by ID
	 *
	 * @since    1.0.0
	 * @param    int    $id    Quote ID
	 * @return   Bonza_Quote_Form_Quote|null    Quote object or null if not found
	 */
	public static function get_by_id($id) {
		global $wpdb;

		if(!self::$table_name) {
			self::$table_name = $wpdb->prefix . 'bonza_quotes';
		}

		$quote_data = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM " . self::$table_name . " WHERE id = %d",
			$id
		), ARRAY_A);

		if(!$quote_data) {
			return null;
		}

		return new self($quote_data);
	}

    /**
	 * Get all quotes with pagination and filtering
	 *
	 * @since    1.0.0
	 * @param    array    $args    Query arguments
	 * @return   array             Array of quote objects and pagination info
	 */
	public static function get_all($args = array()) {
		global $wpdb;

		if(!self::$table_name) {
			self::$table_name = $wpdb->prefix . 'bonza_quotes';
		}

		$defaults = array(
			'per_page' => 20,
			'page'     => 1,
			'status'   => '',
			'search'   => '',
			'orderby'  => 'created_at',
			'order'    => 'DESC',
		);

		$args = wp_parse_args($args, $defaults);

		$where_conditions = array();
		$where_values = array();

		if(!empty($args['status']) && in_array($args['status'], self::$valid_statuses)) {
			$where_conditions[] = 'status = %s';
			$where_values[] = $args['status'];
		}

		if(!empty($args['search'])) {
			$where_conditions[] = '(name LIKE %s OR email LIKE %s OR service_type LIKE %s)';
			$search_term = '%' . $wpdb->esc_like($args['search']) . '%';
			$where_values[] = $search_term;
			$where_values[] = $search_term;
			$where_values[] = $search_term;
		}

		$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

		$allowed_orderby = array('id', 'name', 'email', 'service_type', 'status', 'created_at', 'updated_at');
		$orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
		$order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

		$count_sql = "SELECT COUNT(*) FROM " . self::$table_name . " " . $where_clause;
		$total_items = $wpdb->get_var(!empty($where_values) ? $wpdb->prepare($count_sql, $where_values) : $count_sql);

		$total_pages = ceil($total_items / $args['per_page']);
		$offset = ($args['page'] - 1) * $args['per_page'];

		$sql = sprintf(
			"SELECT * FROM %s %s ORDER BY %s %s LIMIT %d OFFSET %d",
			self::$table_name,
			$where_clause,
			$orderby,
			$order,
			$args['per_page'],
			$offset
		);

		$quotes_data = $wpdb->get_results( 
			! empty( $where_values ) ? $wpdb->prepare( $sql, $where_values ) : $sql, 
			ARRAY_A 
		);

		$quotes = array();

		foreach($quotes_data as $quote_data) {
			$quotes[] = new self($quote_data);
		}

		return array(
			'quotes'      => $quotes,
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'current_page' => $args['page'],
		);
	}
}