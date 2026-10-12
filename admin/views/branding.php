<?php
/**
 * Global branding screen.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

$mdmd_branding = MDMD_Branding::get();
?>
<div class="wrap mdmd mdmd-branding">

	<?php
	MDMD_Admin::header( __( 'Global Branding', 'manpreetdev-mail-designer-for-contact-form-7' ) );
	MDMD_Admin::flash();
	?>

	<div class="mdmd-alert mdmd-alert--info">
		<?php esc_html_e( 'These values fill in the branding tags used by your templates, so one change updates every email at once.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
	</div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="mdmd-branding__form">
		<input type="hidden" name="action" value="mdmd_save_branding" />
		<?php wp_nonce_field( 'mdmd_save_branding' ); ?>

		<div class="mdmd-columns">

			<div class="mdmd-card">
				<div class="mdmd-card__head"><h2><?php esc_html_e( 'Company', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>

				<p class="mdmd-field">
					<label for="mdmd-company-name"><?php esc_html_e( 'Company Name', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="text" id="mdmd-company-name" name="branding[company_name]"
						value="<?php echo esc_attr( $mdmd_branding['company_name'] ); ?>" />
					<span class="mdmd-help"><code>[mdmd_company_name]</code></span>
				</p>

				<div class="mdmd-field">
					<label for="mdmd-logo"><?php esc_html_e( 'Logo', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<div class="mdmd-media">
						<input type="url" id="mdmd-logo" name="branding[logo]" data-media-field
							value="<?php echo esc_attr( $mdmd_branding['logo'] ); ?>" />
						<button type="button" class="mdmd-btn mdmd-btn--small" data-media-choose>
							<?php esc_html_e( 'Choose image', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
						</button>
					</div>
					<span class="mdmd-help">
						<?php esc_html_e( 'Use a hosted image; email clients cannot read local files.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
						<code>[mdmd_logo]</code>
					</span>
					<?php if ( $mdmd_branding['logo'] && MDMD_Branding::is_private_host( $mdmd_branding['logo'] ) ) : ?>
						<span class="mdmd-alert mdmd-alert--warning">
							<?php
							printf(
								/* translators: %s: host name of the logo URL */
								esc_html__( 'This logo is served from %s, which only exists on this machine. It shows in the preview here, but stays blank in the email your visitors receive. Upload the logo on the live site, or point this field at a publicly reachable URL.', 'manpreetdev-mail-designer-for-contact-form-7' ),
								esc_html( (string) wp_parse_url( $mdmd_branding['logo'], PHP_URL_HOST ) )
							);
							?>
						</span>
					<?php endif; ?>
					<?php if ( $mdmd_branding['logo'] ) : ?>
						<img class="mdmd-media__preview" src="<?php echo esc_url( $mdmd_branding['logo'] ); ?>" alt="" data-media-preview />
					<?php else : ?>
						<img class="mdmd-media__preview" src="" alt="" data-media-preview hidden />
					<?php endif; ?>
				</div>

				<p class="mdmd-field">
					<label for="mdmd-website"><?php esc_html_e( 'Website URL', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="url" id="mdmd-website" name="branding[website]"
						value="<?php echo esc_attr( $mdmd_branding['website'] ); ?>" />
					<span class="mdmd-help"><code>[mdmd_website]</code></span>
				</p>

				<p class="mdmd-field">
					<label for="mdmd-address"><?php esc_html_e( 'Company Address', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<textarea id="mdmd-address" name="branding[address]" rows="3"><?php echo esc_textarea( $mdmd_branding['address'] ); ?></textarea>
					<span class="mdmd-help"><code>[mdmd_address]</code></span>
				</p>
			</div>

			<div class="mdmd-card">
				<div class="mdmd-card__head"><h2><?php esc_html_e( 'Appearance', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>

				<?php
				$mdmd_colors = array(
					'primary_color'   => __( 'Primary Colour', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'secondary_color' => __( 'Secondary Colour', 'manpreetdev-mail-designer-for-contact-form-7' ),
				);

				foreach ( $mdmd_colors as $mdmd_key => $mdmd_label ) :
					$id = 'mdmd-' . str_replace( '_', '-', $mdmd_key );
					?>
					<p class="mdmd-field">
						<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $mdmd_label ); ?></label>
						<span class="mdmd-color">
							<input type="color" id="<?php echo esc_attr( $id ); ?>" name="branding[<?php echo esc_attr( $mdmd_key ); ?>]"
								value="<?php echo esc_attr( $mdmd_branding[ $mdmd_key ] ); ?>" data-color-input />
							<input type="text" class="mdmd-color__hex" aria-label="<?php echo esc_attr( $mdmd_label ); ?>"
								value="<?php echo esc_attr( $mdmd_branding[ $mdmd_key ] ); ?>" maxlength="7" data-color-hex />
						</span>
						<span class="mdmd-help"><code>[mdmd_<?php echo esc_attr( $mdmd_key ); ?>]</code></span>
					</p>
				<?php endforeach; ?>

				<p class="mdmd-field">
					<label for="mdmd-footer-text"><?php esc_html_e( 'Footer Text', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<textarea id="mdmd-footer-text" name="branding[footer_text]" rows="3"><?php echo esc_textarea( $mdmd_branding['footer_text'] ); ?></textarea>
					<span class="mdmd-help"><code>[mdmd_footer_text]</code></span>
				</p>
			</div>

			<div class="mdmd-card">
				<div class="mdmd-card__head"><h2><?php esc_html_e( 'Social Links', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>

				<?php
				$mdmd_socials = array(
					'social_facebook'  => __( 'Facebook', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'social_twitter'   => __( 'X', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'social_linkedin'  => __( 'LinkedIn', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'social_instagram' => __( 'Instagram', 'manpreetdev-mail-designer-for-contact-form-7' ),
				);

				foreach ( $mdmd_socials as $mdmd_key => $mdmd_label ) :
					?>
					<p class="mdmd-field">
						<label for="mdmd-<?php echo esc_attr( $mdmd_key ); ?>"><?php echo esc_html( $mdmd_label ); ?></label>
						<input type="url" id="mdmd-<?php echo esc_attr( $mdmd_key ); ?>" name="branding[<?php echo esc_attr( $mdmd_key ); ?>]"
							value="<?php echo esc_attr( $mdmd_branding[ $mdmd_key ] ); ?>" placeholder="https://" />
					</p>
				<?php endforeach; ?>

				<p class="mdmd-help">
					<?php esc_html_e( 'Only the networks you fill in are shown.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					<code>[mdmd_social_links]</code>
				</p>
			</div>

		</div>

		<p class="mdmd-form-actions">
			<button type="submit" class="mdmd-btn mdmd-btn--primary"><?php esc_html_e( 'Save Branding', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></button>
		</p>
	</form>
</div>
