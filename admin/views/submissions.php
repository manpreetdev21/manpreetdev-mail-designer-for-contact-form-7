<?php
/**
 * Submissions screen: the log of everything visitors have sent.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

require_once MDMD_DIR . 'admin/class-submissions-list-table.php';

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading which entry to show.
$mdmd_entry_id = isset( $_GET['entry'] ) && ! isset( $_GET['entry_action'] ) ? absint( $_GET['entry'] ) : 0;

$mdmd_entry = $mdmd_entry_id ? MDMD_Submissions::get( $mdmd_entry_id ) : null;
?>
<div class="wrap mdmd">

	<?php if ( $mdmd_entry ) : ?>

		<?php
		MDMD_Admin::header(
			__( 'Submission', 'manpreetdev-mail-designer-for-contact-form-7' ),
			sprintf(
				'<a class="mdmd-btn" href="%s">%s</a>',
				esc_url( MDMD_Plugin::url( 'submissions', array( 'form' => $mdmd_entry['form_id'] ) ) ),
				esc_html__( 'Back to submissions', 'manpreetdev-mail-designer-for-contact-form-7' )
			)
		);

		MDMD_Admin::flash();

		$mdmd_stamp = strtotime( $mdmd_entry['submitted_at'] );
		?>

		<div class="mdmd-card mdmd-card--flush">
			<table class="mdmd-entry">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Form', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
						<td><?php echo esc_html( $mdmd_entry['form_title'] ? $mdmd_entry['form_title'] : sprintf( '#%d', $mdmd_entry['form_id'] ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Submitted', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
						<td><?php echo esc_html( $mdmd_stamp ? wp_date( 'Y-m-d H:i:s', $mdmd_stamp ) : $mdmd_entry['submitted_at'] ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Email', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
						<td>
							<span class="mdmd-badge mdmd-badge--<?php echo 'sent' === $mdmd_entry['status'] ? 'success' : 'danger'; ?>">
								<?php
								echo 'sent' === $mdmd_entry['status']
									? esc_html__( 'Sent', 'manpreetdev-mail-designer-for-contact-form-7' )
									: esc_html__( 'Not sent', 'manpreetdev-mail-designer-for-contact-form-7' );
								?>
							</span>
						</td>
					</tr>
					<?php if ( $mdmd_entry['remote_ip'] ) : ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'IP address', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></th>
							<td><?php echo esc_html( $mdmd_entry['remote_ip'] ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<div class="mdmd-card mdmd-card--flush">
			<div class="mdmd-card__head"><h2><?php esc_html_e( 'Submitted Data', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2></div>

			<?php require MDMD_DIR . 'admin/views/partial-entry-data.php'; ?>
		</div>

	<?php else : ?>

		<?php
		$mdmd_table = new MDMD_Submissions_List_Table();
		$mdmd_table->prepare_items();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filters, carried into the export link.
		$mdmd_export_args = array_filter(
			array(
				'action' => 'mdmd_export_entries',
				'form'   => $mdmd_table->current_form(),
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, carried into the export link.
				'status' => isset( $_REQUEST['status'] ) ? sanitize_key( wp_unslash( $_REQUEST['status'] ) ) : '',
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, carried into the export link.
				's'      => isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '',
			),
			static fn( $value ) => '' !== $value && 0 !== $value
		);

		MDMD_Admin::header(
			__( 'Form Submissions', 'manpreetdev-mail-designer-for-contact-form-7' ),
			$mdmd_table->has_items()
				? sprintf(
					'<a class="mdmd-btn" href="%s"><span class="dashicons dashicons-media-spreadsheet"></span>%s</a>',
					esc_url( wp_nonce_url( add_query_arg( $mdmd_export_args, admin_url( 'admin-post.php' ) ), 'mdmd_export_entries' ) ),
					esc_html__( 'Export CSV', 'manpreetdev-mail-designer-for-contact-form-7' )
				)
				: ''
		);

		MDMD_Admin::flash();
		?>

		<div class="mdmd-card mdmd-card--flush mdmd-list">
			<form method="get">
				<input type="hidden" name="page" value="mdmd-submissions" />
				<input type="hidden" name="form" value="<?php echo esc_attr( (string) $mdmd_table->current_form() ); ?>" />
				<?php
				$mdmd_table->views();
				$mdmd_table->search_box( __( 'Search submissions', 'manpreetdev-mail-designer-for-contact-form-7' ), 'mdmd-entry-search' );
				?>
			</form>

			<form method="post">
				<input type="hidden" name="page" value="mdmd-submissions" />
				<input type="hidden" name="form" value="<?php echo esc_attr( (string) $mdmd_table->current_form() ); ?>" />
				<div class="mdmd-list__scroll">
					<?php $mdmd_table->display(); ?>
				</div>
			</form>
		</div>

		<p class="mdmd-muted">
			<?php esc_html_e( 'Every submission that passes Contact Form 7\'s validation and spam checks is stored here, whether or not the email went out.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
		</p>

	<?php endif; ?>

</div>
