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
}