<?php
/**
 * One submission's answers and files, as a label/value table.
 *
 * Shared by the submission screen and the expandable row in the list, so both
 * show exactly the same thing.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 *
 * @var array $mdmd_entry Submission, as returned by MDMD_Submissions::get().
 */

defined( 'ABSPATH' ) || exit;

$mdmd_entry_answers = MDMD_Submissions::answers( $mdmd_entry );
$mdmd_entry_files   = MDMD_Submissions::files( $mdmd_entry );

if ( ! $mdmd_entry_answers && ! $mdmd_entry_files ) :
	?>
	<p class="mdmd-muted"><?php esc_html_e( 'This submission had no fields.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
	<?php
	return;
endif;
?>
<table class="mdmd-entry">
	<tbody>
		<?php foreach ( $mdmd_entry_answers as $mdmd_entry_name => $mdmd_entry_value ) : ?>
			<tr>
				<th scope="row">
					<?php echo esc_html( MDMD_CF7_Bridge::friendly_label( $mdmd_entry_name ) ); ?>
					<code>[<?php echo esc_html( $mdmd_entry_name ); ?>]</code>
				</th>
				<td>
					<?php $mdmd_entry_flat = MDMD_Submissions::flatten( $mdmd_entry_value ); ?>
					<?php if ( '' === trim( $mdmd_entry_flat ) ) : ?>
						<span class="mdmd-muted">&mdash;</span>
					<?php else : ?>
						<div class="mdmd-entry__value"><?php echo nl2br( esc_html( $mdmd_entry_flat ) ); ?></div>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>

		<?php foreach ( $mdmd_entry_files as $mdmd_entry_name => $mdmd_entry_list ) : ?>
			<tr>
				<th scope="row">
					<?php echo esc_html( MDMD_CF7_Bridge::friendly_label( $mdmd_entry_name ) ); ?>
					<code>[<?php echo esc_html( $mdmd_entry_name ); ?>]</code>
				</th>
				<td>
					<ul class="mdmd-list-plain">
						<?php foreach ( $mdmd_entry_list as $mdmd_entry_file ) : ?>
							<li>
								<?php if ( '' !== $mdmd_entry_file['path'] && MDMD_Submissions::file_path( $mdmd_entry_file['path'] ) ) : ?>
									<a class="mdmd-btn mdmd-btn--small"
										href="<?php echo esc_url( MDMD_Submissions::download_url( $mdmd_entry['id'], $mdmd_entry_name, $mdmd_entry_file['index'] ) ); ?>">
										<span class="dashicons dashicons-download" aria-hidden="true"></span>
										<?php echo esc_html( $mdmd_entry_file['name'] ); ?>
									</a>
									<?php if ( $mdmd_entry_file['size'] ) : ?>
										<span class="mdmd-muted"><?php echo esc_html( size_format( $mdmd_entry_file['size'] ) ); ?></span>
									<?php endif; ?>
								<?php else : ?>
									<?php echo esc_html( $mdmd_entry_file['name'] ); ?>
									<span class="mdmd-muted">
										<?php
										echo 'type' === $mdmd_entry_file['error']
											? esc_html__( '— not stored, WordPress does not allow this file type', 'manpreetdev-mail-designer-for-contact-form-7' )
											: esc_html__( '— file no longer on disk', 'manpreetdev-mail-designer-for-contact-form-7' );
										?>
									</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
