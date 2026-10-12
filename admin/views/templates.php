<?php
/**
 * Templates list screen.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

require_once MDMD_DIR . 'admin/class-templates-list-table.php';

$mdmd_table = new MDMD_Templates_List_Table();
$mdmd_table->prepare_items();

$mdmd_has_any = MDMD_Template_Post_Type::counts()['total'] > 0;
?>
<div class="wrap mdmd">

	<?php
	MDMD_Admin::header(
		__( 'Email Templates', 'manpreetdev-mail-designer-for-contact-form-7' ),
		sprintf(
			'<a class="mdmd-btn mdmd-btn--primary" href="%s"><span class="dashicons dashicons-plus-alt2"></span>%s</a>',
			esc_url( MDMD_Plugin::url( 'template-edit' ) ),
			esc_html__( 'Add New Template', 'manpreetdev-mail-designer-for-contact-form-7' )
		)
	);

	MDMD_Admin::flash();
	?>

	<?php if ( ! $mdmd_has_any ) : ?>

		<div class="mdmd-empty">
			<span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
			<h2><?php esc_html_e( 'No templates yet.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
			<p><?php esc_html_e( 'Create your first reusable Contact Form 7 email template.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
			<a class="mdmd-btn mdmd-btn--primary" href="<?php echo esc_url( MDMD_Plugin::url( 'template-edit' ) ); ?>">
				<?php esc_html_e( 'Create Template', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
			</a>
		</div>

	<?php else : ?>

		<div class="mdmd-card mdmd-card--flush mdmd-list">
			<form method="get">
				<input type="hidden" name="page" value="mdmd-templates" />
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, preserved across search.
				$mdmd_filter = isset( $_GET['filter'] ) ? sanitize_key( wp_unslash( $_GET['filter'] ) ) : '';

				if ( $mdmd_filter ) {
					printf( '<input type="hidden" name="filter" value="%s" />', esc_attr( $mdmd_filter ) );
				}

				$mdmd_table->views();
				$mdmd_table->search_box( __( 'Search templates', 'manpreetdev-mail-designer-for-contact-form-7' ), 'mdmd-search' );
				?>
			</form>

			<form method="post">
				<?php
				// WP_List_Table::display() emits the bulk-action nonce itself.
				$mdmd_table->display();
				?>
			</form>

			<?php if ( ! $mdmd_table->has_items() ) : ?>
				<div class="mdmd-empty mdmd-empty--inline">
					<h2><?php esc_html_e( 'Nothing matches that filter.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
					<p><?php esc_html_e( 'Try a different search term or clear the filter.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
					<a class="mdmd-btn" href="<?php echo esc_url( MDMD_Plugin::url( 'templates' ) ); ?>">
						<?php esc_html_e( 'Clear filters', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>

	<?php endif; ?>

</div>
