<?php
/**
 * Contact Forms overview: what each form offers, and which templates it uses.
 *
 * Read-only. Everything here is detected from Contact Form 7's own API, so a
 * form that gains a field shows the change without anything being saved.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

$mdmd_forms       = MDMD_CF7_Bridge::forms();
$mdmd_assignments = MDMD_CF7_Bridge::assignments();

$mdmd_slots = array(
	'admin'    => __( 'Admin Email', 'manpreetdev-mail-designer-for-contact-form-7' ),
	'customer' => __( 'Customer Email', 'manpreetdev-mail-designer-for-contact-form-7' ),
);
?>
<div class="wrap mdmd">

	<?php
	MDMD_Admin::header( __( 'Contact Forms', 'manpreetdev-mail-designer-for-contact-form-7' ) );
	MDMD_Admin::flash();
	?>

	<?php if ( ! $mdmd_forms ) : ?>

		<div class="mdmd-empty">
			<span class="dashicons dashicons-feedback" aria-hidden="true"></span>
			<h2><?php esc_html_e( 'No contact forms found.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
			<p><?php esc_html_e( 'Create a form in Contact Form 7 and its fields will be detected here automatically.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
			<a class="mdmd-btn mdmd-btn--primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wpcf7-new' ) ); ?>">
				<?php esc_html_e( 'Create a contact form', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
			</a>
		</div>

	<?php else : ?>

		<div class="mdmd-card mdmd-card--flush">
			<table class="mdmd-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Contact Form', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Fields', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
						<th scope="col"><?php esc_html_e( 'File Uploads', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
						<?php foreach ( $mdmd_slots as $mdmd_label ) : ?>
							<th scope="col"><?php echo esc_html( $mdmd_label ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $mdmd_forms as $mdmd_form_id => $title ) :
						$mdmd_tags    = MDMD_CF7_Bridge::form_tags( $mdmd_form_id );
						$mdmd_files   = MDMD_CF7_Bridge::file_fields( $mdmd_form_id );
						$mdmd_current = $mdmd_assignments[ $mdmd_form_id ] ?? array();
						?>
						<tr>
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

							<td data-label="<?php esc_attr_e( 'Fields', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
								<?php if ( $mdmd_tags ) : ?>
									<div class="mdmd-muted">
										<?php
										$names = array();

										foreach ( $mdmd_tags as $tag ) {
											if ( empty( $tag['is_file'] ) ) {
												$names[] = '[' . $tag['name'] . ']';
											}
										}

										echo esc_html( $names ? implode( ', ', $names ) : __( 'None', 'manpreetdev-mail-designer-for-contact-form-7' ) );
										?>
									</div>
								<?php else : ?>
									<span class="mdmd-muted"><?php esc_html_e( 'None', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
								<?php endif; ?>
							</td>

							<td data-label="<?php esc_attr_e( 'File Uploads', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
								<?php if ( $mdmd_files ) : ?>
									<?php foreach ( $mdmd_files as $mdmd_name ) : ?>
										<span class="mdmd-badge mdmd-badge--info"><?php echo esc_html( '[' . $mdmd_name . ']' ); ?></span>
									<?php endforeach; ?>
								<?php else : ?>
									<span class="mdmd-muted"><?php esc_html_e( 'None', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
								<?php endif; ?>
							</td>

							<?php
							foreach ( $mdmd_slots as $mdmd_slot => $mdmd_label ) :
								$mdmd_assigned = (int) ( $mdmd_current[ $mdmd_slot ] ?? 0 );
								$mdmd_template = $mdmd_assigned ? MDMD_Template_Post_Type::get( $mdmd_assigned ) : null;
								?>
								<td data-label="<?php echo esc_attr( $mdmd_label ); ?>">
									<?php if ( $mdmd_template ) : ?>
										<a href="<?php echo esc_url( MDMD_Plugin::url( 'template-edit', array( 'template' => $mdmd_assigned ) ) ); ?>">
											<?php echo esc_html( $mdmd_template['name'] ); ?>
										</a>
									<?php else : ?>
										<span class="mdmd-muted"><?php esc_html_e( 'Contact Form 7 default', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<p class="mdmd-help">
			<a class="mdmd-btn" href="<?php echo esc_url( MDMD_Plugin::url( 'assignments' ) ); ?>">
				<?php esc_html_e( 'Manage assignments', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
			</a>
		</p>

	<?php endif; ?>

</div>
