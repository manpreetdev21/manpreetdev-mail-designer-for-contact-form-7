<?php
/**
 * Settings screen.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

$mdmd_settings = MDMD_Plugin::settings();
?>
<div class="wrap mdmd">

	<?php
	MDMD_Admin::header( __( 'Settings', 'manpreetdev-mail-designer-for-contact-form-7' ) );
	MDMD_Admin::flash();
	?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="mdmd_save_settings" />
		<?php wp_nonce_field( 'mdmd_save_settings' ); ?>

		<div class="mdmd-columns">

			<div class="mdmd-card">
				<div class="mdmd-card__head"><h2><?php esc_html_e( 'General', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>

				<p class="mdmd-field">
					<label for="mdmd-default-type"><?php esc_html_e( 'Default Email Format', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<select id="mdmd-default-type" name="settings[default_type]">
						<option value="html" <?php selected( $mdmd_settings['default_type'], 'html' ); ?>><?php esc_html_e( 'HTML', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></option>
						<option value="text" <?php selected( $mdmd_settings['default_type'], 'text' ); ?>><?php esc_html_e( 'Plain Text', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></option>
					</select>
					<span class="mdmd-help"><?php esc_html_e( 'Used when you create a new template.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>

				<p class="mdmd-field">
					<label for="mdmd-default-sender"><?php esc_html_e( 'Default Sender', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="text" id="mdmd-default-sender" name="settings[default_sender]"
						value="<?php echo esc_attr( $mdmd_settings['default_sender'] ); ?>"
						placeholder="<?php echo esc_attr( sprintf( '%s <%s>', get_bloginfo( 'name' ), get_option( 'admin_email' ) ) ); ?>" />
					<span class="mdmd-help"><?php esc_html_e( 'Used when neither the template nor the form sets a From address.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>
			</div>

			<div class="mdmd-card">
				<div class="mdmd-card__head"><h2><?php esc_html_e( 'Email', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>

				<p class="mdmd-field">
					<label for="mdmd-test-recipient"><?php esc_html_e( 'Test Email Recipient', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="email" id="mdmd-test-recipient" name="settings[test_recipient]"
						value="<?php echo esc_attr( $mdmd_settings['test_recipient'] ); ?>"
						placeholder="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>" />
					<span class="mdmd-help"><?php esc_html_e( 'Where test emails go by default. Leave empty to use your own address.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>

				<p class="mdmd-help">
					<?php esc_html_e( 'Templates are sent by Contact Form 7 using WordPress mail, so any SMTP plugin you already use keeps working. This plugin never stores or displays mail server credentials.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
				</p>
			</div>

			<div class="mdmd-card">
				<div class="mdmd-card__head"><h2><?php esc_html_e( 'Files', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>

				<p class="mdmd-help">
					<?php esc_html_e( 'Attachments are chosen per template, in the template editor. List one file tag per line and Contact Form 7 attaches whatever the visitor uploaded to that field.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
				</p>

				<p class="mdmd-help">
					<?php esc_html_e( 'File size limits, allowed file types and validation stay with Contact Form 7 and WordPress. There is nothing to configure here, and nothing here can loosen those limits.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
				</p>

				<p class="mdmd-help">
					<?php esc_html_e( 'Contact Form 7 removes an uploaded file shortly after sending, so each submission keeps its own copy under Submissions. Those copies are closed to the web and only reachable through a download link in the admin. A file path or download link is never put in an email.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
				</p>
			</div>

			<div class="mdmd-card">
				<div class="mdmd-card__head"><h2><?php esc_html_e( 'Advanced', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>

				<p class="mdmd-field mdmd-field--check">
					<label for="mdmd-debug">
						<input type="checkbox" id="mdmd-debug" name="settings[debug]" value="1" <?php checked( (int) $mdmd_settings['debug'], 1 ); ?> />
						<?php esc_html_e( 'Enable debug logging', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</label>
					<span class="mdmd-help"><?php esc_html_e( 'Records which template was applied to which form, and whether sending succeeded. Email content is never logged.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>

				<p class="mdmd-field mdmd-field--check">
					<label for="mdmd-delete-data">
						<input type="checkbox" id="mdmd-delete-data" name="settings[delete_on_uninstall]" value="1"
							<?php checked( (int) $mdmd_settings['delete_on_uninstall'], 1 ); ?> />
						<?php esc_html_e( 'Delete all plugin data when the plugin is uninstalled', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</label>
					<span class="mdmd-help"><?php esc_html_e( 'Off by default. Your templates survive deactivating or deleting the plugin unless you turn this on.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>
			</div>

		</div>

		<p class="mdmd-form-actions">
			<button type="submit" class="mdmd-btn mdmd-btn--primary"><?php esc_html_e( 'Save Settings', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></button>
		</p>
	</form>
</div>
