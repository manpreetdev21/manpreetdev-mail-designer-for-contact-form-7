<?php
/**
 * Template editor screen.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading which template to edit.
$mdmd_template_id = isset( $_GET['template'] ) ? absint( $_GET['template'] ) : 0;

$mdmd_template = $mdmd_template_id ? MDMD_Template_Post_Type::get( $mdmd_template_id ) : null;

if ( ! $mdmd_template ) {
	$mdmd_template = array(
		'id'            => 0,
		'name'          => '',
		'body'          => '',
		'description'   => '',
		'status'        => 'publish',
		'type'          => MDMD_Plugin::setting( 'default_type', 'html' ),
		'subject'       => '',
		'preview_text'  => '',
		'recipient'     => '',
		'sender'        => '',
		'headers'       => '',
		'attachments'   => '',
		'exclude_blank' => 1,
		'category'      => '',
		'form_context'  => 0,
	);
}

$mdmd_forms = MDMD_CF7_Bridge::forms();

// Pick the most useful form to validate against: the stored one, then a form
// this template is already assigned to, then the first available form.
$mdmd_form_context = (int) $mdmd_template['form_context'];

if ( ! $mdmd_form_context || ! isset( $mdmd_forms[ $mdmd_form_context ] ) ) {
	$mdmd_using        = $mdmd_template['id'] ? MDMD_CF7_Bridge::forms_using( $mdmd_template['id'] ) : array();
	$mdmd_form_context = $mdmd_using ? (int) $mdmd_using[0] : (int) ( array_key_first( $mdmd_forms ) ?? 0 );
}

$mdmd_assigned_to = $mdmd_template['id'] ? MDMD_CF7_Bridge::forms_using( $mdmd_template['id'] ) : array();

$mdmd_status_modifier = match ( $mdmd_template['status'] ) {
	'publish' => 'success',
	'private' => 'neutral',
	default   => 'warning',
};
?>
<div class="wrap mdmd mdmd-editor"
	data-template-id="<?php echo esc_attr( (string) $mdmd_template['id'] ); ?>"
	data-form-id="<?php echo esc_attr( (string) $mdmd_form_context ); ?>">

	<?php MDMD_Admin::flash(); ?>

	<div class="mdmd-editor__bar">
		<div class="mdmd-editor__identity">
			<a class="mdmd-editor__back" href="<?php echo esc_url( MDMD_Plugin::url( 'templates' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Back to templates', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
				<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
			</a>
			<div>
				<label class="screen-reader-text" for="mdmd-name"><?php esc_html_e( 'Template name', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
				<input type="text" id="mdmd-name" class="mdmd-editor__name" data-field="name"
					value="<?php echo esc_attr( $mdmd_template['name'] ); ?>"
					placeholder="<?php esc_attr_e( 'Untitled template', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>" />
				<div class="mdmd-editor__meta">
					<span class="mdmd-badge mdmd-badge--<?php echo esc_attr( $mdmd_status_modifier ); ?>" data-status-badge>
						<?php echo esc_html( MDMD_Template_Post_Type::status_label( $mdmd_template['status'] ) ); ?>
					</span>
					<span class="mdmd-muted" data-dirty-flag hidden><?php esc_html_e( 'Unsaved changes', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</div>
			</div>
		</div>

		<div class="mdmd-editor__actions">
			<button type="button" class="mdmd-btn" data-action="preview">
				<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
				<?php esc_html_e( 'Preview', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
			</button>
			<button type="button" class="mdmd-btn" data-action="send-test">
				<span class="dashicons dashicons-email" aria-hidden="true"></span>
				<?php esc_html_e( 'Send Test', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
			</button>
			<button type="button" class="mdmd-btn mdmd-btn--primary" data-action="save">
				<?php esc_html_e( 'Save', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
			</button>
		</div>
	</div>

	<div class="mdmd-editor__layout">

		<!-- LEFT: available tags -->
		<aside class="mdmd-panel mdmd-tags" aria-label="<?php esc_attr_e( 'Available tags', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
			<div class="mdmd-panel__head">
				<h2><?php esc_html_e( 'Available Tags', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
			</div>

			<div class="mdmd-panel__body">
				<p class="mdmd-field">
					<label for="mdmd-form-context"><?php esc_html_e( 'Detect tags from', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<select id="mdmd-form-context" data-field="form_context">
						<option value="0"><?php esc_html_e( '— Select a contact form —', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></option>
						<?php foreach ( $mdmd_forms as $id => $title ) : ?>
							<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( $mdmd_form_context, $id ); ?>>
								<?php echo esc_html( $title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="mdmd-help"><?php esc_html_e( 'Used to detect tags and to check the template. It does not assign the template.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>

				<p class="mdmd-field">
					<label class="screen-reader-text" for="mdmd-tag-search"><?php esc_html_e( 'Search tags', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="search" id="mdmd-tag-search" placeholder="<?php esc_attr_e( 'Search tags…', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>" />
				</p>

				<div class="mdmd-tags__recent" data-recent-tags hidden>
					<h3><?php esc_html_e( 'Recently used', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h3>
					<div class="mdmd-tags__list" data-recent-list></div>
				</div>

				<div class="mdmd-tags__group">
					<h3><?php esc_html_e( 'Form Fields', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h3>
					<div class="mdmd-tags__list" data-form-tags>
						<p class="mdmd-muted"><?php esc_html_e( 'Select a contact form to see its fields.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
					</div>
				</div>

				<div class="mdmd-tags__group" data-file-group hidden>
					<h3><?php esc_html_e( 'File Uploads', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h3>
					<div class="mdmd-tags__list" data-file-tags></div>
					<p class="mdmd-help"><?php esc_html_e( 'Contact Form 7 replaces a file tag with the uploaded file name.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
				</div>

				<div class="mdmd-tags__group">
					<h3><?php esc_html_e( 'System Tags', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h3>
					<div class="mdmd-tags__list">
						<?php foreach ( MDMD_CF7_Bridge::special_tags() as $mdmd_tag => $mdmd_label ) : ?>
							<?php require MDMD_DIR . 'admin/views/partial-tag.php'; ?>
						<?php endforeach; ?>
					</div>
					<p class="mdmd-help"><?php esc_html_e( 'Tags starting [_user_ are filled in only when the visitor is logged in; otherwise they come through empty.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
				</div>

				<div class="mdmd-tags__group">
					<h3><?php esc_html_e( 'Branding', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h3>
					<div class="mdmd-tags__list">
						<?php foreach ( MDMD_Branding::tags() as $mdmd_tag => $mdmd_label ) : ?>
							<?php require MDMD_DIR . 'admin/views/partial-tag.php'; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</aside>

		<!-- CENTRE: the email itself -->
		<main class="mdmd-panel mdmd-compose">
			<div class="mdmd-panel__body">

				<div class="mdmd-alert mdmd-alert--warning" data-unknown-tags hidden>
					<p>
						<strong><?php esc_html_e( 'Warning', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></strong>
						<?php esc_html_e( 'This template contains tags that are not available in the selected Contact Form 7 form.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</p>
					<div class="mdmd-tags__list" data-unknown-list></div>
					<p class="mdmd-help"><?php esc_html_e( 'Nothing is removed automatically. Keep a tag if you plan to add the field, or remove it from the template.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
				</div>

				<div class="mdmd-alert mdmd-alert--info" data-new-tags hidden>
					<p data-new-tags-message></p>
					<div class="mdmd-tags__list" data-new-tags-list></div>
				</div>

				<p class="mdmd-field">
					<label for="mdmd-subject"><?php esc_html_e( 'Subject', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="text" id="mdmd-subject" data-field="subject" data-insertable="1"
						value="<?php echo esc_attr( $mdmd_template['subject'] ); ?>"
						placeholder="<?php esc_attr_e( 'New enquiry from [your-name]', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>" />
				</p>

				<p class="mdmd-field">
					<label for="mdmd-preview-text"><?php esc_html_e( 'Preview Text', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="text" id="mdmd-preview-text" data-field="preview_text" data-insertable="1"
						value="<?php echo esc_attr( $mdmd_template['preview_text'] ); ?>" />
					<span class="mdmd-help"><?php esc_html_e( 'The short line inboxes show next to the subject. Optional.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>

				<div class="mdmd-modes" data-modes>
					<button type="button" class="mdmd-mode is-active" data-mode="visual" aria-pressed="true">
						<span class="dashicons dashicons-layout" aria-hidden="true"></span>
						<?php esc_html_e( 'Visual Builder', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</button>
					<button type="button" class="mdmd-mode" data-mode="html" aria-pressed="false">
						<span class="dashicons dashicons-editor-code" aria-hidden="true"></span>
						<?php esc_html_e( 'HTML', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</button>
				</div>

				<div class="mdmd-builder" data-builder hidden>
					<div class="mdmd-builder__palette" data-block-palette>
						<?php foreach ( MDMD_Admin::block_types() as $mdmd_block_type => $mdmd_block ) : ?>
							<button type="button" class="mdmd-blockbtn" data-add-block="<?php echo esc_attr( $mdmd_block_type ); ?>">
								<span class="dashicons dashicons-<?php echo esc_attr( $mdmd_block['icon'] ); ?>" aria-hidden="true"></span>
								<?php echo esc_html( $mdmd_block['label'] ); ?>
							</button>
						<?php endforeach; ?>
					</div>

					<div class="mdmd-builder__main">
						<div class="mdmd-alert mdmd-alert--info" data-blocks-import hidden>
							<p><?php esc_html_e( 'This template is hand-written HTML. Converting rebuilds it as blocks from the content it can recognise — check the preview afterwards, and nothing is saved until you press Save.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
							<button type="button" class="mdmd-btn mdmd-btn--small" data-action="convert-blocks">
								<?php esc_html_e( 'Convert to blocks', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
							</button>
						</div>

						<div class="mdmd-builder__canvas" data-blocks-canvas></div>

						<p class="mdmd-builder__empty" data-blocks-empty>
							<?php esc_html_e( 'Drag a block from the left, or click one to add it.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
						</p>
					</div>
				</div>

				<div class="mdmd-field mdmd-field--grow" data-html-editor hidden>
					<label for="mdmd-body"><?php esc_html_e( 'Email Body', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<textarea id="mdmd-body" data-field="body" data-insertable="1" rows="24"
						spellcheck="false"><?php echo esc_textarea( $mdmd_template['body'] ); ?></textarea>
					<span class="mdmd-help"><?php esc_html_e( 'Click any tag on the left to insert it where your cursor is.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</div>

			</div>
		</main>

		<!-- RIGHT: settings -->
		<aside class="mdmd-panel mdmd-settings-panel" aria-label="<?php esc_attr_e( 'Template settings', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>">
			<div class="mdmd-panel__head">
				<h2><?php esc_html_e( 'Settings', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></h2>
			</div>

			<div class="mdmd-panel__body">
				<p class="mdmd-field">
					<label for="mdmd-type"><?php esc_html_e( 'Template Type', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<select id="mdmd-type" data-field="type">
						<option value="html" <?php selected( $mdmd_template['type'], 'html' ); ?>><?php esc_html_e( 'HTML', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></option>
						<option value="text" <?php selected( $mdmd_template['type'], 'text' ); ?>><?php esc_html_e( 'Plain Text', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></option>
					</select>
				</p>

				<p class="mdmd-field">
					<label for="mdmd-status"><?php esc_html_e( 'Status', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<select id="mdmd-status" data-field="status">
						<option value="publish" <?php selected( $mdmd_template['status'], 'publish' ); ?>><?php esc_html_e( 'Active', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></option>
						<option value="draft" <?php selected( $mdmd_template['status'], 'draft' ); ?>><?php esc_html_e( 'Draft', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></option>
						<option value="private" <?php selected( $mdmd_template['status'], 'private' ); ?>><?php esc_html_e( 'Inactive', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></option>
					</select>
					<span class="mdmd-help"><?php esc_html_e( 'Only active templates are used when a form is submitted.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>

				<p class="mdmd-field">
					<label for="mdmd-category"><?php esc_html_e( 'Category', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="text" id="mdmd-category" data-field="category"
						value="<?php echo esc_attr( $mdmd_template['category'] ); ?>"
						placeholder="<?php esc_attr_e( 'Admin, Customer…', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>" />
				</p>

				<hr class="mdmd-rule" />

				<p class="mdmd-field">
					<label for="mdmd-recipient"><?php esc_html_e( 'To', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="text" id="mdmd-recipient" data-field="recipient" data-insertable="1"
						value="<?php echo esc_attr( $mdmd_template['recipient'] ); ?>"
						placeholder="<?php esc_attr_e( 'Leave empty to keep the form’s own recipient', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>" />
				</p>

				<p class="mdmd-field">
					<label for="mdmd-sender"><?php esc_html_e( 'From', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<input type="text" id="mdmd-sender" data-field="sender" data-insertable="1"
						value="<?php echo esc_attr( $mdmd_template['sender'] ); ?>"
						placeholder="<?php esc_attr_e( 'Leave empty to keep the form’s own sender', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>" />
				</p>

				<p class="mdmd-field">
					<label for="mdmd-headers"><?php esc_html_e( 'Additional Headers', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<textarea id="mdmd-headers" data-field="headers" rows="3" data-insertable="1"
						placeholder="Reply-To: [your-email]"><?php echo esc_textarea( $mdmd_template['headers'] ); ?></textarea>
					<span class="mdmd-help"><?php esc_html_e( 'One header per line, for example Reply-To or Cc.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>

				<p class="mdmd-field mdmd-field--check">
					<label for="mdmd-exclude-blank">
						<input type="checkbox" id="mdmd-exclude-blank" data-field="exclude_blank"
							<?php checked( (int) $mdmd_template['exclude_blank'], 1 ); ?> />
						<?php esc_html_e( 'Hide empty fields', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</label>
					<span class="mdmd-help"><?php esc_html_e( 'Removes lines whose tags came back empty.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
				</p>

				<p class="mdmd-field">
					<label for="mdmd-attachments"><?php esc_html_e( 'Attachments', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<textarea id="mdmd-attachments" data-field="attachments" rows="3" data-insertable="1"
						placeholder="[your-resume]"><?php echo esc_textarea( $mdmd_template['attachments'] ); ?></textarea>
					<span class="mdmd-help"><?php esc_html_e( 'One file tag per line. Whatever the visitor uploaded to that field is attached to this email. Leave empty to keep the form’s own attachment settings.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
					<span class="mdmd-alert mdmd-alert--warning" data-attachment-warning hidden></span>
				</p>

				<hr class="mdmd-rule" />

				<p class="mdmd-field">
					<label for="mdmd-description"><?php esc_html_e( 'Description', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></label>
					<textarea id="mdmd-description" data-field="description" rows="3"><?php echo esc_textarea( $mdmd_template['description'] ); ?></textarea>
				</p>

				<div class="mdmd-field">
					<span class="mdmd-field__label"><?php esc_html_e( 'Assigned Forms', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></span>
					<?php if ( $mdmd_assigned_to ) : ?>
						<ul class="mdmd-list-plain">
							<?php foreach ( $mdmd_assigned_to as $mdmd_form_id ) : ?>
								<li><?php echo esc_html( $mdmd_forms[ $mdmd_form_id ] ?? sprintf( '#%d', $mdmd_form_id ) ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="mdmd-muted"><?php esc_html_e( 'Not assigned to any form yet.', 'manpreetdev-mail-designer-for-contact-form-7' ); ?></p>
					<?php endif; ?>
					<a class="mdmd-btn mdmd-btn--small" href="<?php echo esc_url( MDMD_Plugin::url( 'assignments' ) ); ?>">
						<?php esc_html_e( 'Manage assignments', 'manpreetdev-mail-designer-for-contact-form-7' ); ?>
					</a>
				</div>
			</div>
		</aside>

	</div>
</div>
