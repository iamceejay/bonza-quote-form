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
}