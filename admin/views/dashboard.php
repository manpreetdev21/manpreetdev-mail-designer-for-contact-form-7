<?php
/**
 * Dashboard screen.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

$mdmd_counts       = MDMD_Template_Post_Type::counts();
$mdmd_assigned_ids = MDMD_CF7_Bridge::assigned_template_ids();
$mdmd_forms        = MDMD_CF7_Bridge::forms();
$mdmd_assignments  = MDMD_CF7_Bridge::assignments();

$mdmd_cards = array(
	array(
		'icon'  => 'dashicons-email-alt',
		'count' => $mdmd_counts['total'],
		'label' => __( 'Total Templates', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'desc'  => __( 'Every template in your library.', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'link'  => MDMD_Plugin::url( 'templates' ),
		'cta'   => __( 'Manage templates', 'manpreetdev-mail-designer-for-contact-form-7' ),
	),
	array(
		'icon'  => 'dashicons-yes-alt',
		'count' => $mdmd_counts['publish'],
		'label' => __( 'Active Templates', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'desc'  => __( 'Ready to be assigned to a form.', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'link'  => MDMD_Plugin::url( 'templates', array( 'filter' => 'publish' ) ),
		'cta'   => __( 'View active', 'manpreetdev-mail-designer-for-contact-form-7' ),
	),
	array(
		'icon'  => 'dashicons-admin-links',
		'count' => count( $mdmd_assignments ),
		'label' => __( 'Assigned Forms', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'desc'  => __( 'Contact forms using a template.', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'link'  => MDMD_Plugin::url( 'assignments' ),
		'cta'   => __( 'View assignments', 'manpreetdev-mail-designer-for-contact-form-7' ),
	),
	array(
		'icon'  => 'dashicons-archive',
		'count' => max( 0, $mdmd_counts['total'] - count( $mdmd_assigned_ids ) ),
		'label' => __( 'Unused Templates', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'desc'  => __( 'Not assigned to any form yet.', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'link'  => MDMD_Plugin::url( 'templates', array( 'filter' => 'unused' ) ),
		'cta'   => __( 'View unused', 'manpreetdev-mail-designer-for-contact-form-7' ),
	),
	array(
		'icon'  => 'dashicons-feedback',
		'count' => count( $mdmd_forms ),
		'label' => __( 'Contact Forms', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'desc'  => __( 'Forms detected in Contact Form 7.', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'link'  => admin_url( 'admin.php?page=wpcf7' ),
		'cta'   => __( 'Open Contact Form 7', 'manpreetdev-mail-designer-for-contact-form-7' ),
	),
	array(
		'icon'  => 'dashicons-editor-code',
		'count' => MDMD_Template_Post_Type::count_by_type( 'html' ),
		'label' => __( 'HTML Templates', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'desc'  => __( 'Designed, branded email layouts.', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'link'  => MDMD_Plugin::url( 'templates', array( 'filter' => 'html' ) ),
		'cta'   => __( 'View HTML', 'manpreetdev-mail-designer-for-contact-form-7' ),
	),
	array(
		'icon'  => 'dashicons-editor-alignleft',
		'count' => MDMD_Template_Post_Type::count_by_type( 'text' ),
		'label' => __( 'Plain Text Templates', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'desc'  => __( 'Readable in every mail client.', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'link'  => MDMD_Plugin::url( 'templates', array( 'filter' => 'text' ) ),
		'cta'   => __( 'View plain text', 'manpreetdev-mail-designer-for-contact-form-7' ),
	),
	array(
		'icon'  => 'dashicons-paperclip',
		'count' => MDMD_Template_Post_Type::count_with_files(),
		'label' => __( 'File Upload Templates', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'desc'  => __( 'Templates that attach uploaded files.', 'manpreetdev-mail-designer-for-contact-form-7' ),
		'link'  => MDMD_Plugin::url( 'templates', array( 'filter' => 'files' ) ),
		'cta'   => __( 'View file templates', 'manpreetdev-mail-designer-for-contact-form-7' ),
	),
);
?>
<div class="wrap mdmd">

	<?php
	MDMD_Admin::header(
		__( 'Dashboard', 'manpreetdev-mail-designer-for-contact-form-7' ),
		sprintf(
			'<a class="mdmd-btn mdmd-btn--primary" href="%s"><span class="dashicons dashicons-plus-alt2"></span>%s</a>',
			esc_url( MDMD_Plugin::url( 'template-edit' ) ),
			esc_html__( 'Add New Template', 'manpreetdev-mail-designer-for-contact-form-7' )
		)
	);

	MDMD_Admin::flash();
	?>

	<?php if ( 0 === $mdmd_counts['total'] ) : ?>

		<div class="mdmd-empty">
			<span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
			<h2><?php esc_html_e( 'No templates yet.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
			<p><?php esc_html_e( 'Create your first reusable Contact Form 7 email template.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
			<a class="mdmd-btn mdmd-btn--primary" href="<?php echo esc_url( MDMD_Plugin::url( 'template-edit' ) ); ?>">
				<?php esc_html_e( 'Create Template', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
			</a>
		</div>

	<?php else : ?>

		<div class="mdmd-grid">
			<?php foreach ( $mdmd_cards as $mdmd_card ) : ?>
				<div class="mdmd-card mdmd-stat">
					<div class="mdmd-stat__top">
						<span class="dashicons <?php echo esc_attr( $mdmd_card['icon'] ); ?>" aria-hidden="true"></span>
						<span class="mdmd-stat__count"><?php echo esc_html( number_format_i18n( $mdmd_card['count'] ) ); ?></span>
					</div>
					<h2 class="mdmd-stat__label"><?php echo esc_html( $mdmd_card['label'] ); ?></h2>
					<p class="mdmd-muted"><?php echo esc_html( $mdmd_card['desc'] ); ?></p>
					<a class="mdmd-stat__link" href="<?php echo esc_url( $mdmd_card['link'] ); ?>">
						<?php echo esc_html( $mdmd_card['cta'] ); ?> <span aria-hidden="true">&rarr;</span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
		$mdmd_recent = get_posts(
			array(
				'post_type'      => MDMD_Template_Post_Type::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 5,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);
		?>

		<?php if ( $mdmd_recent ) : ?>
			<div class="mdmd-card mdmd-card--flush">
				<div class="mdmd-card__head">
					<h2><?php esc_html_e( 'Recently updated', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
					<a href="<?php echo esc_url( MDMD_Plugin::url( 'templates' ) ); ?>">
						<?php esc_html_e( 'View all', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</a>
				</div>
				<table class="mdmd-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Template', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Updated', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $mdmd_recent as $post ) : ?>
							<?php
							$mdmd_modifier = match ( $post->post_status ) {
								'publish' => 'success',
								'private' => 'neutral',
								default   => 'warning',
							};
							?>
							<tr>
								<td data-label="<?php esc_attr_e( 'Template', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
									<a href="<?php echo esc_url( MDMD_Plugin::url( 'template-edit', array( 'template' => $post->ID ) ) ); ?>">
										<?php echo esc_html( $post->post_title ); ?>
									</a>
								</td>
								<td data-label="<?php esc_attr_e( 'Status', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
									<span class="mdmd-badge mdmd-badge--<?php echo esc_attr( $mdmd_modifier ); ?>">
										<?php echo esc_html( MDMD_Template_Post_Type::status_label( $post->post_status ) ); ?>
									</span>
								</td>
								<td data-label="<?php esc_attr_e( 'Updated', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
									<?php
									$mdmd_timestamp = get_post_timestamp( $post, 'modified' );

									echo esc_html(
										$mdmd_timestamp
											? sprintf(
												/* translators: %s: human-readable time difference */
												__( '%s ago', 'manpreetdev-mail-designer-for-contact-form-7' ),
												human_time_diff( $mdmd_timestamp )
											)
											: '—'
									);
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

	<?php endif; ?>

	<?php if ( ! $mdmd_forms ) : ?>
		<div class="mdmd-alert mdmd-alert--warning">
			<?php
			printf(
				/* translators: %s: link to Contact Form 7 */
				esc_html__( 'No contact forms found yet. %s to create one before assigning templates.', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wpcf7-new' ) ) . '">' . esc_html__( 'Open Contact Form 7', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</a>'
			);
			?>
		</div>
	<?php endif; ?>

</div>
