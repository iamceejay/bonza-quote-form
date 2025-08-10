<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/public/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Variables available from the public class:
// $attributes, $service_types, $form_id, $form_submitted, $form_message, $form_errors
?>

<div class="bonza-quote-form-container" id="<?php echo esc_attr( $form_id ); ?>">
	<?php if ( $attributes['show_title'] === 'true' && ! empty( $attributes['title'] ) ) : ?>
		<h3 class="bonza-quote-form-title"><?php echo esc_html( $attributes['title'] ); ?></h3>
	<?php endif; ?>

	<?php if ( $form_submitted ) : ?>
		<div class="bonza-quote-form-message bonza-quote-form-success">
			<?php echo esc_html( $form_message ); ?>
		</div>
	<?php else : ?>

		<?php if ( ! empty( $form_errors ) ) : ?>
			<div class="bonza-quote-form-message bonza-quote-form-error">
				<ul>
					<?php foreach ( $form_errors as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form class="bonza-quote-form" method="post" <?php echo $attributes['ajax'] === 'true' ? 'data-ajax="true"' : ''; ?>>
			
			<?php wp_nonce_field( 'bonza_quote_form_nonce', 'bonza_quote_nonce' ); ?>
			
			<div class="bonza-quote-form-row">
				<label for="bonza_quote_name" class="bonza-quote-form-label">
					<?php esc_html_e( 'Name', 'bonza-quote-form' ); ?> <span class="required">*</span>
				</label>
				<input 
					type="text" 
					id="bonza_quote_name" 
					name="bonza_quote_name" 
					class="bonza-quote-form-input" 
					value="<?php echo isset( $_POST['bonza_quote_name'] ) ? esc_attr( $_POST['bonza_quote_name'] ) : ''; ?>"
					required
					maxlength="255"
					placeholder="<?php esc_attr_e( 'Enter your full name', 'bonza-quote-form' ); ?>"
				>
			</div>

			<div class="bonza-quote-form-row">
				<label for="bonza_quote_email" class="bonza-quote-form-label">
					<?php esc_html_e( 'Email', 'bonza-quote-form' ); ?> <span class="required">*</span>
				</label>
				<input 
					type="email" 
					id="bonza_quote_email" 
					name="bonza_quote_email" 
					class="bonza-quote-form-input" 
					value="<?php echo isset( $_POST['bonza_quote_email'] ) ? esc_attr( $_POST['bonza_quote_email'] ) : ''; ?>"
					required
					maxlength="255"
					placeholder="<?php esc_attr_e( 'your.email@example.com', 'bonza-quote-form' ); ?>"
				>
			</div>

			<div class="bonza-quote-form-row">
				<label for="bonza_quote_service_type" class="bonza-quote-form-label">
					<?php esc_html_e( 'Service Type', 'bonza-quote-form' ); ?> <span class="required">*</span>
				</label>
				<?php if ( ! empty( $service_types ) ) : ?>
					<select id="bonza_quote_service_type" name="bonza_quote_service_type" class="bonza-quote-form-select" required>
						<option value=""><?php esc_html_e( 'Select a service...', 'bonza-quote-form' ); ?></option>
						<?php foreach ( $service_types as $service ) : ?>
							<option value="<?php echo esc_attr( $service ); ?>" <?php selected( isset( $_POST['bonza_quote_service_type'] ) ? $_POST['bonza_quote_service_type'] : '', $service ); ?>>
								<?php echo esc_html( $service ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input 
						type="text" 
						id="bonza_quote_service_type" 
						name="bonza_quote_service_type" 
						class="bonza-quote-form-input" 
						value="<?php echo isset( $_POST['bonza_quote_service_type'] ) ? esc_attr( $_POST['bonza_quote_service_type'] ) : ''; ?>"
						required
						maxlength="255"
						placeholder="<?php esc_attr_e( 'e.g., Web Design, SEO, Marketing', 'bonza-quote-form' ); ?>"
					>
				<?php endif; ?>
			</div>

			<div class="bonza-quote-form-row">
				<label for="bonza_quote_notes" class="bonza-quote-form-label">
					<?php esc_html_e( 'Additional Notes', 'bonza-quote-form' ); ?>
				</label>
				<textarea 
					id="bonza_quote_notes" 
					name="bonza_quote_notes" 
					class="bonza-quote-form-textarea" 
					rows="4"
					placeholder="<?php esc_attr_e( 'Please provide any additional details about your project...', 'bonza-quote-form' ); ?>"
				><?php echo isset( $_POST['bonza_quote_notes'] ) ? esc_textarea( $_POST['bonza_quote_notes'] ) : ''; ?></textarea>
			</div>

			<?php 
			/**
			 * Action hook to add custom fields before submit button
			 *
			 * @since 1.0.0
			 * @param array $attributes Shortcode attributes
			 */
			do_action( 'bonza_quote_form_before_submit', $attributes );
			?>

			<div class="bonza-quote-form-row bonza-quote-form-submit-row">
				<button type="submit" name="bonza_quote_submit" class="bonza-quote-form-submit">
					<?php echo esc_html( $attributes['submit_text'] ); ?>
				</button>
				<div class="bonza-quote-form-loading" style="display: none;">
					<?php esc_html_e( 'Processing...', 'bonza-quote-form' ); ?>
				</div>
			</div>

			<?php if ( ! empty( $attributes['redirect_url'] ) ) : ?>
				<input type="hidden" name="bonza_quote_redirect" value="<?php echo esc_url( $attributes['redirect_url'] ); ?>">
			<?php endif; ?>

			<?php 
			/**
			 * Action hook to add custom fields after submit button
			 *
			 * @since 1.0.0
			 * @param array $attributes Shortcode attributes
			 */
			do_action( 'bonza_quote_form_after_submit', $attributes );
			?>
		</form>
	<?php endif; ?>
</div>

<?php
/**
 * Action hook after form container
 *
 * @since 1.0.0
 * @param array $attributes Shortcode attributes
 * @param string $form_id Unique form ID
 */
do_action( 'bonza_quote_form_after_container', $attributes, $form_id );
?>