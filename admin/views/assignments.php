<?php
/**
 * Assignments screen: which template each contact form uses.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

$mdmd_forms       = MDMD_CF7_Bridge::forms();
$mdmd_assignments = MDMD_CF7_Bridge::assignments();
$mdmd_options     = MDMD_Template_Post_Type::options( true );

// An assigned template that was later deactivated must still appear here, or
// the row shows "No template" while the form is still marked as managed.
foreach ( MDMD_CF7_Bridge::assigned_template_ids() as $mdmd_assigned_id ) {
	if ( isset( $mdmd_options[ $mdmd_assigned_id ] ) ) {
		continue;
	}

	$mdmd_stale = MDMD_Template_Post_Type::get( $mdmd_assigned_id );

	if ( $mdmd_stale ) {
		$mdmd_options[ $mdmd_assigned_id ] = sprintf(
			/* translators: 1: template name, 2: status label, e.g. Inactive */
			__( '%1$s (%2$s)', 'manpreetdev-mail-designer-for-contact-form-7' ),
			$mdmd_stale['name'],
			MDMD_Template_Post_Type::status_label( $mdmd_stale['status'] )
		);
	}
}

// The templates list links here with ?template=N to assign that template.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only preselection.
$mdmd_preselect = isset( $_GET['template'] ) ? absint( $_GET['template'] ) : 0;

if ( $mdmd_preselect && ! isset( $mdmd_options[ $mdmd_preselect ] ) ) {
	$mdmd_preselect = 0;
}

$mdmd_slots = array(
	'admin'    => __( 'Admin Email Template', 'manpreetdev-mail-designer-for-contact-form-7' ),
	'customer' => __( 'Customer Email Template', 'manpreetdev-mail-designer-for-contact-form-7' ),
);
?>
<div class="wrap mdmd mdmd-assignments">

	<?php
	MDMD_Admin::header( __( 'Assignments', 'manpreetdev-mail-designer-for-contact-form-7' ) );
	MDMD_Admin::flash();
	?>

	<div class="mdmd-alert mdmd-alert--info">
		<?php esc_html_e( 'Assigning a template does not change your Contact Form 7 mail settings. They stay exactly as they are and take over again the moment you detach.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
	</div>

	<?php if ( $mdmd_preselect ) : ?>
		<div class="mdmd-alert mdmd-alert--info">
			<?php
			printf(
				/* translators: %s: template name */
				esc_html__( '%s is pre-selected below. Pick the form and email it should handle, then press Apply Template.', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'<strong>' . esc_html( $mdmd_options[ $mdmd_preselect ] ) . '</strong>'
			);
			?>
		</div>
	<?php endif; ?>

	<?php if ( ! $mdmd_forms ) : ?>

		<div class="mdmd-empty">
			<span class="dashicons dashicons-feedback" aria-hidden="true"></span>
			<h2><?php esc_html_e( 'No contact forms found.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
			<p><?php esc_html_e( 'Create a form in Contact Form 7 first, then come back to assign a template to it.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
			<a class="mdmd-btn mdmd-btn--primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wpcf7-new' ) ); ?>">
				<?php esc_html_e( 'Create a contact form', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
			</a>
		</div>

	<?php elseif ( ! $mdmd_options ) : ?>

		<div class="mdmd-empty">
			<span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
			<h2><?php esc_html_e( 'No active templates.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
			<p><?php esc_html_e( 'Only active templates can be assigned to a form. Create one, or set an existing template to Active.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
			<a class="mdmd-btn mdmd-btn--primary" href="<?php echo esc_url( MDMD_Plugin::url( 'template-edit' ) ); ?>">
				<?php esc_html_e( 'Create Template', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
			</a>
		</div>

	<?php else : ?>

		<div class="mdmd-card mdmd-card--flush">
			<table class="mdmd-table mdmd-table--assignments">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Contact Form', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
						<?php foreach ( $mdmd_slots as $mdmd_label ) : ?>
							<th scope="col"><?php echo esc_html( $mdmd_label ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $mdmd_forms as $mdmd_form_id => $title ) : ?>
						<?php $mdmd_current = $mdmd_assignments[ $mdmd_form_id ] ?? array(); ?>
						<tr data-form-id="<?php echo esc_attr( (string) $mdmd_form_id ); ?>">
							<td data-label="<?php esc_attr_e( 'Contact Form', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
								<strong><?php echo esc_html( $title ); ?></strong>
								<?php if ( $mdmd_current ) : ?>
									<span class="mdmd-badge mdmd-badge--success"><?php esc_html_e( 'Managed', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
								<?php endif; ?>
								<div class="mdmd-muted">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpcf7&post=' . $mdmd_form_id . '&action=edit' ) ); ?>">
										<?php esc_html_e( 'Edit in Contact Form 7', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
									</a>
								</div>
							</td>

							<?php foreach ( $mdmd_slots as $mdmd_slot => $mdmd_label ) : ?>
								<?php $mdmd_assigned = (int) ( $mdmd_current[ $mdmd_slot ] ?? 0 ); ?>
								<td data-label="<?php echo esc_attr( $mdmd_label ); ?>">
									<div class="mdmd-assign" data-slot="<?php echo esc_attr( $mdmd_slot ); ?>">
										<label class="screen-reader-text" for="mdmd-select-<?php echo esc_attr( $mdmd_form_id . '-' . $mdmd_slot ); ?>">
											<?php
											printf(
												/* translators: 1: slot label, 2: form title */
												esc_html__( '%1$s for %2$s', 'manpreetdev-mail-designer-for-contact-form-7' ),
												esc_html( $mdmd_label ),
												esc_html( $title )
											);
											?>
										</label>
										<select id="mdmd-select-<?php echo esc_attr( $mdmd_form_id . '-' . $mdmd_slot ); ?>" data-template-select>
											<option value="0"><?php esc_html_e( '— No template —', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></option>
											<?php
											// An empty admin slot takes the preselection from ?template=N.
											$mdmd_chosen = $mdmd_assigned;

											if ( ! $mdmd_chosen && $mdmd_preselect && 'admin' === $mdmd_slot ) {
												$mdmd_chosen = $mdmd_preselect;
											}

											foreach ( $mdmd_options as $id => $mdmd_name ) :
												?>
												<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( $mdmd_chosen, $id ); ?>>
													<?php echo esc_html( $mdmd_name ); ?>
												</option>
											<?php endforeach; ?>
										</select>

										<div class="mdmd-assign__actions">
											<button type="button" class="mdmd-btn mdmd-btn--small mdmd-btn--primary" data-action="apply">
												<?php esc_html_e( 'Apply Template', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
											</button>
											<button type="button" class="mdmd-btn mdmd-btn--small" data-action="detach" <?php disabled( ! $mdmd_assigned ); ?>>
												<?php esc_html_e( 'Detach', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
											</button>
										</div>

										<?php if ( $mdmd_assigned ) : ?>
											<p class="mdmd-help">
												<a href="<?php echo esc_url( MDMD_Plugin::url( 'template-edit', array( 'template' => $mdmd_assigned ) ) ); ?>">
													<?php esc_html_e( 'Edit template', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
												</a>
											</p>
											<?php
											$mdmd_assigned_template = MDMD_Template_Post_Type::get( $mdmd_assigned );

											// Only active templates take over a live form, so an
											// inactive one here means Contact Form 7 is still sending.
											if ( $mdmd_assigned_template && 'publish' !== $mdmd_assigned_template['status'] ) :
												?>
												<p class="mdmd-alert mdmd-alert--warning">
													<?php
													printf(
														/* translators: %s: status label, e.g. Draft */
														esc_html__( 'This template is %s, so Contact Form 7 is still sending this email. Set it to Active to use it.', 'manpreetdev-mail-designer-for-contact-form-7' ),
														esc_html( MDMD_Template_Post_Type::status_label( $mdmd_assigned_template['status'] ) )
													);
													?>
												</p>
												<?php
											endif;

											// Mailing the visitor's own upload back to them is
											// occasionally wanted and often a mistake. Warn, never block.
											if (
												'customer' === $mdmd_slot
												&& $mdmd_assigned_template
												&& '' !== trim( (string) $mdmd_assigned_template['attachments'] )
											) :
												?>
												<p class="mdmd-alert mdmd-alert--warning">
													<?php esc_html_e( 'This template attaches the visitor’s uploaded files, and this email goes to the visitor.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
												</p>
											<?php endif; ?>
										<?php elseif ( 'customer' === $mdmd_slot ) : ?>
											<p class="mdmd-help"><?php esc_html_e( 'Optional. Sends a confirmation to the visitor.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
										<?php endif; ?>
									</div>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	<?php endif; ?>

</div>
