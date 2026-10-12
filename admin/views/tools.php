<?php
/**
 * Tools screen: import, export, system status and the debug log.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

$mdmd_log      = array_reverse( (array) get_option( 'mdmd_log', array() ) );
$mdmd_counts   = MDMD_Template_Post_Type::counts();
$mdmd_debug_on = (bool) MDMD_Plugin::setting( 'debug' );
?>
<div class="wrap mdmd">

	<?php
	MDMD_Admin::header( __( 'Tools', 'manpreetdev-mail-designer-for-contact-form-7' ) );
	MDMD_Admin::flash();
	?>

	<div class="mdmd-columns">

		<div class="mdmd-card">
			<div class="mdmd-card__head"><h2><?php esc_html_e( 'Demo Templates', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>
			<p class="mdmd-muted"><?php esc_html_e( 'Add the ready-made starter templates: contact notification, customer thank-you, quote, booking, support, newsletter, file upload, simple and blank. Any you already have are skipped, so it is safe to run again.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="mdmd_install_demos" />
				<?php wp_nonce_field( 'mdmd_install_demos' ); ?>
				<button type="submit" class="mdmd-btn mdmd-btn--primary">
					<span class="dashicons dashicons-welcome-add-page" aria-hidden="true"></span>
					<?php esc_html_e( 'Install demo templates', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
				</button>
			</form>
		</div>

		<div class="mdmd-card">
			<div class="mdmd-card__head"><h2><?php esc_html_e( 'Export', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>
			<p class="mdmd-muted">
				<?php
				printf(
					/* translators: %d: number of templates */
					esc_html( _n( 'Download all %d template as a JSON file.', 'Download all %d templates as a JSON file.', $mdmd_counts['total'], 'manpreetdev-mail-designer-for-contact-form-7' ) ),
					(int) $mdmd_counts['total']
				);
				?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="mdmd_export" />
				<?php wp_nonce_field( 'mdmd_export' ); ?>
				<button type="submit" class="mdmd-btn" <?php disabled( 0 === $mdmd_counts['total'] ); ?>>
					<span class="dashicons dashicons-download" aria-hidden="true"></span>
					<?php esc_html_e( 'Export templates', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
				</button>
			</form>
		</div>

		<div class="mdmd-card">
			<div class="mdmd-card__head"><h2><?php esc_html_e( 'Import', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>
			<p class="mdmd-muted"><?php esc_html_e( 'Upload a file exported from this plugin. Imported templates arrive as drafts so you can review them before they go live.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="mdmd_import" />
				<?php wp_nonce_field( 'mdmd_import' ); ?>
				<p class="mdmd-field">
					<label for="mdmd-import-file"><?php esc_html_e( 'Template file (.json)', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="file" id="mdmd-import-file" name="import_file" accept="application/json,.json" required />
				</p>
				<p class="mdmd-field mdmd-field--check">
					<label for="mdmd-import-branding">
						<input type="checkbox" id="mdmd-import-branding" name="import_branding" value="1" />
						<?php esc_html_e( 'Also restore global branding from the file', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</label>
					<span class="mdmd-help"><?php esc_html_e( 'Overwrites your current logo, colours, footer and social links. Off by default.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>
				<button type="submit" class="mdmd-btn">
					<span class="dashicons dashicons-upload" aria-hidden="true"></span>
					<?php esc_html_e( 'Import templates', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
				</button>
			</form>
		</div>

		<div class="mdmd-card">
			<div class="mdmd-card__head"><h2><?php esc_html_e( 'System Status', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>
			<table class="mdmd-table mdmd-table--plain">
				<tbody>
					<?php
					$mdmd_rows = array(
						__( 'Plugin version', 'manpreetdev-mail-designer-for-contact-form-7' )      => MDMD_VERSION,
						__( 'WordPress', 'manpreetdev-mail-designer-for-contact-form-7' )           => get_bloginfo( 'version' ),
						__( 'Contact Form 7', 'manpreetdev-mail-designer-for-contact-form-7' )      => defined( 'WPCF7_VERSION' ) ? WPCF7_VERSION : __( 'Not active', 'manpreetdev-mail-designer-for-contact-form-7' ),
						__( 'PHP', 'manpreetdev-mail-designer-for-contact-form-7' )                 => PHP_VERSION,
						__( 'Templates', 'manpreetdev-mail-designer-for-contact-form-7' )           => number_format_i18n( $mdmd_counts['total'] ),
						__( 'Managed forms', 'manpreetdev-mail-designer-for-contact-form-7' )       => number_format_i18n( count( MDMD_CF7_Bridge::assignments() ) ),
						__( 'Debug logging', 'manpreetdev-mail-designer-for-contact-form-7' )       => $mdmd_debug_on ? __( 'On', 'manpreetdev-mail-designer-for-contact-form-7' ) : __( 'Off', 'manpreetdev-mail-designer-for-contact-form-7' ),
					);

					foreach ( $mdmd_rows as $mdmd_label => $mdmd_value ) :
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $mdmd_label ); ?></th>
							<td><?php echo esc_html( $mdmd_value ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	</div>

	<div class="mdmd-card mdmd-card--flush">
		<div class="mdmd-card__head">
			<h2><?php esc_html_e( 'Debug Log', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
			<?php if ( $mdmd_log ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="mdmd_clear_log" />
					<?php wp_nonce_field( 'mdmd_clear_log' ); ?>
					<button type="submit" class="mdmd-btn mdmd-btn--small"><?php esc_html_e( 'Clear log', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></button>
				</form>
			<?php endif; ?>
		</div>

		<?php if ( ! $mdmd_debug_on && ! $mdmd_log ) : ?>
			<div class="mdmd-empty mdmd-empty--inline">
				<h2><?php esc_html_e( 'Logging is off.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
				<p><?php esc_html_e( 'Turn on debug logging in Settings to record which templates were applied.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
				<a class="mdmd-btn" href="<?php echo esc_url( MDMD_Plugin::url( 'settings' ) ); ?>">
					<?php esc_html_e( 'Open Settings', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
				</a>
			</div>
		<?php elseif ( ! $mdmd_log ) : ?>
			<div class="mdmd-empty mdmd-empty--inline">
				<h2><?php esc_html_e( 'Nothing logged yet.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
				<p><?php esc_html_e( 'Entries appear here after a managed form is submitted.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
			</div>
		<?php else : ?>
			<table class="mdmd-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'When', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Event', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( array_slice( $mdmd_log, 0, 50 ) as $mdmd_entry ) : ?>
						<tr>
							<td data-label="<?php esc_attr_e( 'When', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>" class="mdmd-nowrap">
								<?php echo esc_html( wp_date( 'Y-m-d H:i:s', (int) $mdmd_entry['time'] ) ); ?>
							</td>
							<td data-label="<?php esc_attr_e( 'Event', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
								<?php echo esc_html( $mdmd_entry['message'] ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

</div>
