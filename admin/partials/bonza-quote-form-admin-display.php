<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/iamceejay
 * @since      1.0.0
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Variables available from the admin class:
// $quote - The quote object
// $status_labels - Array of status labels with colors
?>

<div class="wrap">
	<h1><?php esc_html_e('Quote Details', 'bonza-quote-form'); ?></h1>
	
	<a href="<?php echo esc_url(admin_url('admin.php?page=bonza-quotes')); ?>" class="page-title-action">
		<?php esc_html_e('← Back to Quotes', 'bonza-quote-form'); ?>
	</a>

	<?php 
	/**
	 * Action hook for adding content after the page title
	 *
	 * @since 1.0.0
	 * @param object $quote Quote object
	 */
	do_action('bonza_quote_form_admin_details_after_title', $quote); 
	?>

	<div class="bonza-quote-details-container">
		<div class="bonza-quote-details-card">
			<h2><?php esc_html_e('Quote Information', 'bonza-quote-form'); ?></h2>
			
			<?php 
			/**
			 * Action hook for adding content before quote information table
			 *
			 * @since 1.0.0
			 * @param object $quote Quote object
			 */
			do_action('bonza_quote_form_admin_details_before_table', $quote); 
			?>
			
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
						<?php 
						$status = $quote->status;
						$label = isset($status_labels[$status]) ? $status_labels[$status]['label'] : ucfirst($status);
						$color = isset($status_labels[$status]) ? $status_labels[$status]['color'] : '#666';
						?>
						<span class="bonza-quote-status-badge" style="display: inline-block; padding: 6px 12px; border-radius: 4px; background-color: <?php echo esc_attr($color); ?>; color: white; font-size: 12px; font-weight: bold; text-transform: uppercase;">
							<?php echo esc_html($label); ?>
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Date Submitted', 'bonza-quote-form'); ?></th>
					<td><?php echo esc_html(mysql2date(get_option( 'date_format') . ' ' . get_option('time_format'), $quote->created_at)); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Last Updated', 'bonza-quote-form'); ?></th>
					<td><?php echo esc_html( mysql2date( get_option('date_format') . ' ' . get_option('time_format'), $quote->updated_at)); ?></td>
				</tr>
			</table>

			<?php 
			/**
			 * Action hook for adding content after quote information table
			 *
			 * @since 1.0.0
			 * @param object $quote Quote object
			 */
			do_action('bonza_quote_form_admin_details_after_table', $quote); 
			?>

			<?php if(!empty($quote->notes)) : ?>
				<h3><?php esc_html_e('Additional Notes', 'bonza-quote-form'); ?></h3>
				<div class="bonza-quote-notes-text">
					<?php echo wp_kses_post(nl2br(esc_html($quote->notes))); ?>
				</div>
			<?php endif; ?>

			<?php 
			/**
			 * Action hook for adding content before actions section
			 *
			 * @since 1.0.0
			 * @param object $quote Quote object
			 */
			do_action('bonza_quote_form_admin_details_before_actions', $quote); 
			?>

			<div class="bonza-quote-actions">
				<h3><?php esc_html_e('Actions', 'bonza-quote-form'); ?></h3>
				
				<?php if($quote->status !== 'approved') : ?>
					<a href="<?php echo esc_url(wp_nonce_url( 
						add_query_arg(array( 
							'page' => 'bonza-quotes',
							'action' => 'approve', 
							'quote' => $quote->id 
						), admin_url('admin.php')), 
						'approve_quote_' . $quote->id 
					) ); ?>" 
					   class="button button-primary bonza-action-button" 
					   data-action="approve">
						<?php esc_html_e('Approve Quote', 'bonza-quote-form'); ?>
					</a>
				<?php endif; ?>

				<?php if($quote->status !== 'rejected') : ?>
					<a href="<?php echo esc_url(wp_nonce_url( 
						add_query_arg(array( 
							'page' => 'bonza-quotes',
							'action' => 'reject', 
							'quote' => $quote->id 
						), admin_url('admin.php')), 
						'reject_quote_' . $quote->id 
					) ); ?>" 
					   class="button bonza-action-button" 
					   data-action="reject">
						<?php esc_html_e('Reject Quote', 'bonza-quote-form'); ?>
					</a>
				<?php endif; ?>

				<a href="<?php echo esc_url(wp_nonce_url( 
					add_query_arg(array( 
						'page' => 'bonza-quotes',
						'action' => 'delete', 
						'quote' => $quote->id 
					), admin_url( 'admin.php')), 
					'delete_quote_' . $quote->id 
				) ); ?>" 
				   class="button button-link-delete bonza-action-button" 
				   data-action="delete">
					<?php esc_html_e('Delete Quote', 'bonza-quote-form'); ?>
				</a>

				<?php 
				/**
				 * Action hook for adding custom action buttons
				 *
				 * @since 1.0.0
				 * @param object $quote Quote object
				 */
				do_action('bonza_quote_form_admin_details_custom_actions', $quote); 
				?>
			</div>

			<?php 
			/**
			 * Action hook for adding content after actions section
			 *
			 * @since 1.0.0
			 * @param object $quote Quote object
			 */
			do_action('bonza_quote_form_admin_details_after_actions', $quote); 
			?>
		</div>
	</div>
</div>

<?php
/**
 * Action hook at the end of quote details page
 *
 * @since 1.0.0
 * @param object $quote Quote object
 */
do_action('bonza_quote_form_admin_details_page_end', $quote);
?>
