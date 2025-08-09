<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/iamceejay
 * @since      1.0.0
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/includes
 * @author     Jasper Cairoane Poly <jaspercairoane.poly@gmail.com>
 */
class Bonza_Quote_Form_Activator {

	public static function activate() {
		self::create_quotes_table();
		self::create_database_version();
	}

	/**
	 * Create the quotes table in the database
	 *
	 * @since    1.0.0
	 */
	private static function create_quotes_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'bonza_quotes';

		if($wpdb->get_var($wpdb->prepare( "SHOW TABLES LIKE %s", $table_name)) != $table_name) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				email varchar(255) NOT NULL,
				service_type varchar(255) NOT NULL,
				notes text,
				status varchar(20) NOT NULL DEFAULT 'pending',
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				INDEX idx_status (status),
				INDEX idx_email (email),
				INDEX idx_created_at (created_at)
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			dbDelta($sql);

			if($wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) == $table_name) {
				error_log('Bonza Quote Form: Database table created successfully.' );
			} else {
				error_log('Bonza Quote Form: Failed to create database table.');
			}
		}
	}

	/**
	 * Store database version for future upgrades
	 *
	 * @since    1.0.0
	 */
	private static function create_database_version() {
		add_option('bonza_quote_form_db_version', '1.0.0');
		
		add_option('bonza_quote_form_activated_time', current_time('timestamp'));
	}

	/**
	 * Check if database needs upgrade
	 *
	 * @since    1.0.0
	 * @return   boolean True if upgrade needed, false otherwise
	 */
	public static function needs_database_upgrade() {
		$current_version = get_option('bonza_quote_form_db_version', '0.0.0');
		$plugin_version = '1.0.0';
		
		return version_compare($current_version, $plugin_version, '<');
	}

	/**
	 * Upgrade database if needed
	 *
	 * @since    1.0.0
	 */
	public static function maybe_upgrade_database() {
		if (self::needs_database_upgrade()) {
			self::create_quotes_table();
			update_option('bonza_quote_form_db_version', '1.0.0');
		}
	}

	/**
	 * Validate table structure
	 *
	 * @since    1.0.0
	 * @return   boolean True if table structure is valid, false otherwise
	 */
	public static function validate_table_structure() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'bonza_quotes';
		
		$required_columns = array(
			'id',
			'name', 
			'email',
			'service_type',
			'notes',
			'status',
			'created_at',
			'updated_at'
		);

		$existing_columns = $wpdb->get_col($wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
			DB_NAME,
			$table_name
		));

		foreach ($required_columns as $column) {
			if (!in_array( $column, $existing_columns)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get table statistics
	 *
	 * @since    1.0.0
	 * @return   array Table statistics
	 */
	public static function get_table_stats() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'bonza_quotes';
		
		$stats = array(
			'total_quotes' => 0,
			'pending_quotes' => 0,
			'approved_quotes' => 0,
			'rejected_quotes' => 0,
			'table_exists' => false
		);

		if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) == $table_name) {
			$stats['table_exists'] = true;
			
			$stats['total_quotes'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
			
			$stats['pending_quotes'] = $wpdb->get_var($wpdb->prepare( 
				"SELECT COUNT(*) FROM $table_name WHERE status = %s", 
				'pending' 
			));
			
			$stats['approved_quotes'] = $wpdb->get_var($wpdb->prepare( 
				"SELECT COUNT(*) FROM $table_name WHERE status = %s", 
				'approved' 
			));
			
			$stats['rejected_quotes'] = $wpdb->get_var($wpdb->prepare( 
				"SELECT COUNT(*) FROM $table_name WHERE status = %s", 
				'rejected' 
			));
		}

		return $stats;
	}
}
