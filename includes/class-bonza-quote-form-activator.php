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
}
