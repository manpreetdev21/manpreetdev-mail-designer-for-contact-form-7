<?php
/**
 * End-to-end smoke test.
 *
 * Runs against the real WordPress install and the real Contact Form 7, because
 * the whole plugin hangs off CF7's own APIs — mocking them would only test the
 * mock. Creates a throwaway form and template, then cleans both up.
 *
 * Usage:  php tests/smoke-test.php
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( 'Run this from the command line.' );
}

$root = dirname( __DIR__, 4 );

// Needed so the "stand down on CF7 admin screens" guard can be exercised.
define( 'WP_ADMIN', true );

require_once $root . '/wp-load.php';

$failures = 0;
$checks   = 0;

/**
 * Asserts a condition and reports it.
 *
 * @param string $label     What is being checked.
 * @param bool   $condition Result.
 * @param string $detail    Extra context shown on failure.
 */
function mdmd_check( $label, $condition, $detail = '' ) {
	global $failures, $checks;

	++$checks;

	if ( $condition ) {
		echo "  PASS  $label\n";
		return;
	}

	++$failures;
	echo "  FAIL  $label" . ( $detail ? "\n        $detail" : '' ) . "\n";
}

echo "\nManpreetDev Mail Designer for Contact Form 7 — smoke test\n";
echo str_repeat( '-', 60 ) . "\n";

mdmd_check( 'Contact Form 7 is active', MDMD_Plugin::cf7_supported() );
mdmd_check( 'Template post type registered', post_type_exists( 'mdmd_template' ) );

/* -------------------------------------------------------------------------
 * Fixtures
 * ---------------------------------------------------------------------- */

$form = WPCF7_ContactForm::get_template( array( 'title' => 'MDMD Smoke Form' ) );

$form->set_properties(
	array(
		'form' => "[text* your-name]\n[email* your-email]\n[tel your-phone]\n[textarea your-message]\n[file* your-resume]\n[file docs]",
		'mail' => array(
			'subject'            => 'ORIGINAL SUBJECT',
			'sender'             => 'Original <original@example.com>',
			'recipient'          => 'original-recipient@example.com',
			'body'               => 'ORIGINAL BODY',
			'additional_headers' => 'Reply-To: [your-email]',
			'attachments'        => '',
			'use_html'           => 0,
			'exclude_blank'      => 0,
		),
	)
);

$form_id = $form->save();

mdmd_check( 'Test contact form created', $form_id > 0 );

$template_id = MDMD_Template_Post_Type::save(
	array(
		'name'          => 'MDMD Smoke Template',
		'type'          => 'html',
		'status'        => 'publish',
		'subject'       => 'New enquiry from [your-name]',
		'preview_text'  => 'Someone contacted you',
		'body'          => '<!doctype html><html><body><table><tr><td>Hello [your-name] at [mdmd_company_name], reply to [your-email]. [company]</td></tr></table></body></html>',
		'headers'       => 'Reply-To: [your-email]',
		'attachments'   => "[your-resume]
../../wp-config.php
[9bad]
[docs]
[your-resume]",
		'exclude_blank' => 1,
	)
);

mdmd_check( 'Template created', ! is_wp_error( $template_id ), is_wp_error( $template_id ) ? $template_id->get_error_message() : '' );

$stored = MDMD_Template_Post_Type::get( $template_id );

/* -------------------------------------------------------------------------
 * Sanitising
 * ---------------------------------------------------------------------- */

mdmd_check( 'Doctype survives sanitising', str_starts_with( strtolower( ltrim( $stored['body'] ) ), '<!doctype' ), $stored['body'] );
mdmd_check( 'Email-safe HTML survives sanitising', str_contains( $stored['body'], '<table>' ) );
mdmd_check( 'CF7 tags are preserved verbatim', str_contains( $stored['body'], '[your-name]' ) );

mdmd_check(
	'Attachment spec keeps file tags and drops the rest',
	"[your-resume]\n[docs]" === $stored['attachments'],
	str_replace( "\n", ' | ', $stored['attachments'] )
);

mdmd_check(
	'Attachment spec never accepts a bare file path',
	! str_contains( $stored['attachments'], 'wp-config' ),
	$stored['attachments']
);

mdmd_check(
	'File flag derived from the attachment spec',
	1 === (int) get_post_meta( $template_id, '_mdmd_has_files', true )
		&& MDMD_Template_Post_Type::count_with_files() > 0
);

$xss = MDMD_Template_Post_Type::sanitize_body( '<p onclick="evil()">hi</p><script>alert(1)</script><iframe src="x"></iframe>', 'html' );

mdmd_check( 'Scripts stripped from bodies', ! str_contains( $xss, '<script' ) && ! str_contains( $xss, '<iframe' ) && ! str_contains( $xss, 'onclick' ), $xss );

// The visual builder stores what it needs in data attributes, so kses has to
// let them through or every saved layout comes back unreadable.
$blocks = MDMD_Template_Post_Type::sanitize_body(
	'<table><tr><td data-mdmd-block="text" data-align="center" style="padding:8px;">hi</td></tr></table>',
	'html'
);

mdmd_check(
	'Builder block markers survive sanitising',
	str_contains( $blocks, 'data-mdmd-block="text"' ) && str_contains( $blocks, 'data-align="center"' ),
	$blocks
);

/* -------------------------------------------------------------------------
 * Tag detection and validation
 * ---------------------------------------------------------------------- */

$mail_tags = MDMD_CF7_Bridge::mail_tags( $form_id );

mdmd_check(
	'CF7 mail-tags detected',
	array_diff( array( 'your-name', 'your-email', 'your-phone', 'your-message' ), $mail_tags ) === array(),
	implode( ', ', $mail_tags )
);

$file_types = MDMD_CF7_Bridge::file_tag_types();

mdmd_check(
	'File tag types come from CF7 own feature flag',
	array_diff( array( 'file', 'file*' ), $file_types ) === array(),
	implode( ', ', $file_types )
);

$file_fields = MDMD_CF7_Bridge::file_fields( $form_id );

mdmd_check(
	'File upload fields detected',
	array_diff( array( 'your-resume', 'docs' ), $file_fields ) === array() && 2 === count( $file_fields ),
	implode( ', ', $file_fields )
);

mdmd_check(
	'Ordinary fields are not flagged as uploads',
	! array_intersect( array( 'your-name', 'your-email', 'your-message' ), $file_fields )
);

$bad_attach = MDMD_CF7_Bridge::invalid_attachments( "[your-resume]\n[your-message]", $form_id );

mdmd_check(
	'Attachment naming a non-file field is flagged',
	array( 'your-message' ) === $bad_attach,
	implode( ', ', $bad_attach )
);

$unknown = MDMD_CF7_Bridge::unknown_tags( $stored['body'], $form_id );

mdmd_check( 'Unknown tag [company] flagged', in_array( 'company', $unknown, true ), implode( ', ', $unknown ) );
mdmd_check( 'Known and branding tags not flagged', ! array_intersect( array( 'your-name', 'your-email', 'mdmd_company_name' ), $unknown ) );

$unused = MDMD_CF7_Bridge::unused_tags( $stored['body'], $form_id );

mdmd_check( 'Unused form tags reported', in_array( 'your-message', $unused, true ) && ! in_array( 'your-name', $unused, true ), implode( ', ', $unused ) );

mdmd_check( 'Friendly labels derived', 'Email' === MDMD_CF7_Bridge::friendly_label( 'your-email' ) );

/* -------------------------------------------------------------------------
 * Branding and sample rendering
 * ---------------------------------------------------------------------- */

$branded = MDMD_Branding::replace( '[mdmd_company_name] / [mdmd_year]', true );

mdmd_check( 'Branding tags resolved', ! str_contains( $branded, '[mdmd_' ), $branded );

$sample = MDMD_Renderer::sample_render_data( $stored, $form_id );

mdmd_check( 'Sample render replaces form tags', ! str_contains( $sample['body'], '[your-name]' ) && str_contains( $sample['body'], 'John Smith' ) );
mdmd_check( 'Sample render types email fields', str_contains( MDMD_Renderer::sample_values( $form_id )['your-email'], '@' ) );
mdmd_check( 'Preheader injected into HTML body', str_contains( $sample['body'], 'Someone contacted you' ) );

/* -------------------------------------------------------------------------
 * Assignment and runtime injection — the core guarantee
 * ---------------------------------------------------------------------- */

$original_meta = get_post_meta( $form_id, '_mail', true );

mdmd_check( 'Assignment stored', true === MDMD_CF7_Bridge::assign( $form_id, 'admin', $template_id ) );

$live = WPCF7_ContactForm::get_instance( $form_id );
$mail = $live->prop( 'mail' );

mdmd_check( 'Template body injected into CF7', str_contains( $mail['body'], 'Hello [your-name]' ), substr( $mail['body'], 0, 80 ) );
mdmd_check( 'Template subject injected', 'New enquiry from [your-name]' === $mail['subject'], $mail['subject'] );
mdmd_check( 'HTML mode enabled for HTML templates', 1 === (int) $mail['use_html'] );
mdmd_check( 'Exclude-blank carried across', 1 === (int) $mail['exclude_blank'] );
mdmd_check( 'Recipient falls back to the form when the template is silent', 'original-recipient@example.com' === $mail['recipient'], $mail['recipient'] );
mdmd_check( 'Template headers applied', str_contains( $mail['additional_headers'], 'Reply-To' ) );

mdmd_check(
	'Attachment spec reaches CF7 mail property',
	str_contains( $mail['attachments'], '[your-resume]' )
		&& str_contains( $mail['attachments'], '[docs]' ),
	$mail['attachments']
);

/* An empty spec must leave whatever Contact Form 7 already had. */
update_post_meta( $template_id, '_mdmd_attachments', '' );

$fallback = MDMD_Renderer::to_mail_array(
	$template_id,
	array( 'attachments' => '[form-own-file]' ),
	'admin'
);

mdmd_check(
	'Empty attachment spec falls back to the form own value',
	'[form-own-file]' === $fallback['attachments'],
	$fallback['attachments']
);

update_post_meta( $template_id, '_mdmd_attachments', "[your-resume]\n[docs]" );

mdmd_check(
	'CF7 database row is untouched',
	get_post_meta( $form_id, '_mail', true ) === $original_meta && 'ORIGINAL BODY' === $original_meta['body'],
	'CF7 mail meta changed — this must never happen'
);

/* Customer slot must switch CF7's mail_2 on. */
MDMD_CF7_Bridge::assign( $form_id, 'customer', $template_id );

$live2  = WPCF7_ContactForm::get_instance( $form_id );
$mail_2 = $live2->prop( 'mail_2' );

mdmd_check( 'Customer email (mail_2) activated', ! empty( $mail_2['active'] ) );
mdmd_check( 'Customer recipient defaults to the visitor', str_contains( $mail_2['recipient'], '[your-email]' ), $mail_2['recipient'] );

/* -------------------------------------------------------------------------
 * The guard that stops CF7's own save() persisting our template
 * ---------------------------------------------------------------------- */

$_REQUEST['page'] = 'wpcf7';

$guarded = MDMD_CF7_Bridge::filter_properties(
	array( 'mail' => $original_meta, 'mail_2' => array() ),
	WPCF7_ContactForm::get_instance( $form_id )
);

mdmd_check(
	'Filter stands down on CF7 edit screens',
	'ORIGINAL BODY' === $guarded['mail']['body'],
	'CF7 admin save would overwrite the original mail config'
);

unset( $_REQUEST['page'] );

/* -------------------------------------------------------------------------
 * Inactive templates must not take over a live form
 * ---------------------------------------------------------------------- */

wp_update_post( array( 'ID' => $template_id, 'post_status' => 'private' ) );

mdmd_check( 'Inactive template is not applied', null === MDMD_Renderer::to_mail_array( $template_id, $original_meta, 'admin' ) );

wp_update_post( array( 'ID' => $template_id, 'post_status' => 'publish' ) );

/* -------------------------------------------------------------------------
 * Duplication, deletion guard and detach
 * ---------------------------------------------------------------------- */

$copy_id = MDMD_Template_Post_Type::duplicate( $template_id );
$copy    = MDMD_Template_Post_Type::get( $copy_id );

mdmd_check( 'Duplicate copies the body', $copy && $copy['body'] === $stored['body'] );
mdmd_check( 'Duplicate lands as a draft', $copy && 'draft' === $copy['status'] );
mdmd_check( 'Duplicate copies the attachment spec', $copy && $copy['attachments'] === $stored['attachments'] );

mdmd_check( 'Assigned template is reported as in use', count( MDMD_CF7_Bridge::forms_using( $template_id ) ) === 1 );

MDMD_CF7_Bridge::detach( $form_id, 'admin' );
MDMD_CF7_Bridge::detach( $form_id, 'customer' );

$restored = WPCF7_ContactForm::get_instance( $form_id )->prop( 'mail' );

mdmd_check( 'Detach restores CF7 own settings', 'ORIGINAL BODY' === $restored['body'], $restored['body'] );
mdmd_check( 'Assignment removed', array() === MDMD_CF7_Bridge::for_form( $form_id ) );

/* Deleting a template must prune its assignments. */
MDMD_CF7_Bridge::assign( $form_id, 'admin', $copy_id );
wp_update_post( array( 'ID' => $copy_id, 'post_status' => 'publish' ) );
MDMD_CF7_Bridge::assign( $form_id, 'admin', $copy_id );
wp_delete_post( $copy_id, true );

mdmd_check( 'Deleting a template prunes its assignments', array() === MDMD_CF7_Bridge::for_form( $form_id ) );

/* -------------------------------------------------------------------------
 * Screen option and branding round-trip
 * ---------------------------------------------------------------------- */

mdmd_check(
	'Templates-per-page screen option is saved',
	35 === apply_filters( 'set_screen_option_mdmd_per_page', false, 'mdmd_per_page', '35' )
);

mdmd_check(
	'Absurd per-page values are clamped',
	200 === apply_filters( 'set_screen_option_mdmd_per_page', false, 'mdmd_per_page', '99999' )
);

$branding_before = MDMD_Branding::get();

MDMD_Branding::save( array_merge( $branding_before, array( 'company_name' => 'Imported Co', 'primary_color' => '#abcdef' ) ) );

$branding_after = MDMD_Branding::get();

mdmd_check( 'Branding import restores text values', 'Imported Co' === $branding_after['company_name'] );
mdmd_check( 'Branding import restores colours', '#abcdef' === $branding_after['primary_color'] );

MDMD_Branding::save( array_merge( $branding_before, array( 'primary_color' => 'not-a-colour' ) ) );

mdmd_check( 'Invalid colour falls back to the default', '#2271b1' === MDMD_Branding::get()['primary_color'] );

MDMD_Branding::save( $branding_before );

/* -------------------------------------------------------------------------
 * Submissions log
 * ---------------------------------------------------------------------- */

MDMD_Submissions::install();

global $wpdb;

mdmd_check(
	'Submissions table exists',
	MDMD_Submissions::table() === $wpdb->get_var(
		$wpdb->prepare( 'SHOW TABLES LIKE %s', MDMD_Submissions::table() )
	)
);

$entry_before = MDMD_Submissions::count();

$wpdb->insert(
	MDMD_Submissions::table(),
	array(
		'form_id'      => $form_id,
		'form_title'   => 'MDMD Smoke Form',
		'status'       => 'sent',
		'fields'       => wp_json_encode(
			array(
				'your-name'    => 'John Smith',
				'your-message' => "Line one\nLine two",
				'gone-field'   => array( 'a', 'b' ),
			)
		),
		'files'        => wp_json_encode( array( 'your-file' => array( 'cv.pdf' ) ) ),
		'remote_ip'    => '203.0.113.42',
		'submitted_at' => current_time( 'mysql' ),
	)
);

$entry_id = (int) $wpdb->insert_id;
$stored   = MDMD_Submissions::get( $entry_id );

mdmd_check( 'Submission stored and read back', $stored && 'John Smith' === $stored['fields']['your-name'], wp_json_encode( $stored ) );
mdmd_check( 'Multi-value answers survive the round trip', $stored && array( 'a', 'b' ) === $stored['fields']['gone-field'] );
mdmd_check( 'Uploaded file names are kept', $stored && array( 'cv.pdf' ) === $stored['files']['your-file'] );

$listed = MDMD_Submissions::query( array( 'form_id' => $form_id ) );

mdmd_check( 'Submission listed for its form', 1 === $listed['total'] );

mdmd_check(
	'Search matches stored answers',
	1 === MDMD_Submissions::query( array( 'search' => 'John Smith' ) )['total']
);

mdmd_check(
	'Search ignores answers that are not there',
	0 === MDMD_Submissions::query( array( 'search' => 'nobody-by-that-name' ) )['total']
);

$columns = MDMD_Submissions::field_columns( $form_id, $listed['items'] );

mdmd_check(
	'Columns cover the form fields and any extras in the data',
	isset( $columns['your-name'], $columns['your-message'], $columns['gone-field'] ),
	implode( ', ', array_keys( $columns ) )
);

mdmd_check( 'Forms with submissions are listed', isset( MDMD_Submissions::forms()[ $form_id ] ) );

MDMD_Submissions::delete( array( $entry_id ) );

mdmd_check(
	'Submission deleted',
	null === MDMD_Submissions::get( $entry_id ) && $entry_before === MDMD_Submissions::count()
);

/*
 * The capture hook itself, driven through Contact Form 7's own submit() so
 * the test exercises the real path. wp_mail is short-circuited: this checks
 * that a submission is logged, not that the server can send email.
 *
 * Its own form, because the fixture above requires a file upload that a
 * command-line submission cannot provide.
 */
$live = WPCF7_ContactForm::get_template( array( 'title' => 'MDMD Capture Form' ) );

$live->set_properties(
	array( 'form' => "[text* your-name]\n[email* your-email]\n[textarea your-message]" )
);

$live_id = $live->save();

// A command-line post has no browser fingerprint, which CF7 reads as spam.
add_filter( 'wpcf7_skip_spam_check', '__return_true' );
add_filter( 'pre_wp_mail', '__return_true' );

$_POST = array(
	'_wpcf7'          => $live_id,
	'_wpcf7_version'  => WPCF7_VERSION,
	'_wpcf7_locale'   => 'en_US',
	'_wpcf7_unit_tag' => 'wpcf7-f' . $live_id . '-o1',
	'your-name'       => 'Jane Tester',
	'your-email'      => 'jane@example.com',
	'your-message'    => "First line\nSecond line",
);

$submit_result = WPCF7_ContactForm::get_instance( $live_id )->submit();

$_POST = array();

remove_filter( 'pre_wp_mail', '__return_true' );
remove_filter( 'wpcf7_skip_spam_check', '__return_true' );

$captured = MDMD_Submissions::query( array( 'form_id' => $live_id ) );
$logged   = $captured['items'][0] ?? null;

mdmd_check(
	'A real submission is captured',
	1 === $captured['total'],
	'CF7 returned: ' . wp_json_encode( $submit_result )
);

mdmd_check(
	'Captured answers match what was posted',
	$logged && 'Jane Tester' === ( $logged['fields']['your-name'] ?? '' )
		&& "First line\nSecond line" === ( $logged['fields']['your-message'] ?? '' ),
	wp_json_encode( $logged['fields'] ?? array() )
);

mdmd_check( 'The email result is recorded', $logged && 'sent' === $logged['status'] );

mdmd_check(
	'Contact Form 7 internals are not stored as answers',
	$logged && ! array_filter( array_keys( $logged['fields'] ), static fn( $key ) => str_starts_with( $key, '_' ) ),
	implode( ', ', array_keys( $logged['fields'] ?? array() ) )
);

MDMD_Submissions::delete( array( $logged['id'] ?? 0 ) );
wp_delete_post( $live_id, true );

/*
 * Uploaded files: Contact Form 7 deletes its own copy when the request ends,
 * so the copy that matters is ours.
 */
$source_dir  = wp_upload_dir()['basedir'] . '/mdmd-test-source';
$source_file = $source_dir . '/notes.txt';
$blocked     = $source_dir . '/payload.php';

wp_mkdir_p( $source_dir );
file_put_contents( $source_file, 'attached file body' );
file_put_contents( $blocked, '<?php // should never be stored' );

$stored = MDMD_Submissions::store_files(
	array(
		'your-doc'  => array( $source_file ),
		'your-code' => array( $blocked ),
	)
);

$kept_path = MDMD_Submissions::file_path( $stored['your-doc'][0]['path'] ?? '' );

mdmd_check( 'Uploaded file is copied somewhere permanent', '' !== $kept_path, wp_json_encode( $stored ) );

mdmd_check(
	'The stored copy holds the original bytes',
	$kept_path && 'attached file body' === file_get_contents( $kept_path )
);

mdmd_check(
	'The original name is kept for display',
	'notes.txt' === ( $stored['your-doc'][0]['name'] ?? '' )
);

mdmd_check(
	'A file type WordPress will not allow is recorded but never stored',
	'' === ( $stored['your-code'][0]['path'] ?? 'x' )
		&& 'type' === ( $stored['your-code'][0]['error'] ?? '' ),
	wp_json_encode( $stored['your-code'] ?? array() )
);

mdmd_check(
	'The upload folder is closed to the web',
	file_exists( MDMD_Submissions::upload_dir() . '/.htaccess' )
		&& file_exists( MDMD_Submissions::upload_dir() . '/index.html' )
);

mdmd_check(
	'A path climbing out of the upload folder is refused',
	'' === MDMD_Submissions::file_path( '../../../wp-config.php' )
);

$wpdb->insert(
	MDMD_Submissions::table(),
	array(
		'form_id'      => $form_id,
		'form_title'   => 'MDMD Smoke Form',
		'status'       => 'sent',
		'fields'       => wp_json_encode( array( 'your-name' => 'Ada Upload' ) ),
		'files'        => wp_json_encode( $stored ),
		'remote_ip'    => '203.0.113.7',
		'submitted_at' => current_time( 'mysql' ),
	)
);

$file_entry = MDMD_Submissions::get( (int) $wpdb->insert_id );
$listed_file = MDMD_Submissions::files( $file_entry )['your-doc'][0] ?? array();

mdmd_check(
	'Stored files read back with a download index',
	isset( $listed_file['index'] ) && 'notes.txt' === $listed_file['name']
);

MDMD_Submissions::delete( array( $file_entry['id'] ) );

mdmd_check( 'Deleting a submission removes its files', ! file_exists( $kept_path ) );

wp_delete_file( $source_file );
wp_delete_file( $blocked );

/* -------------------------------------------------------------------------
 * Demo templates
 * ---------------------------------------------------------------------- */

require_once MDMD_DIR . 'includes/starter-templates.php';

$starter_names = wp_list_pluck( mdmd_starter_templates(), 'name' );
$all_templates = static function () {
	return get_posts(
		array(
			'post_type'      => MDMD_Template_Post_Type::POST_TYPE,
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
};
$missing_names = static function () use ( $starter_names ) {
	return array_values( array_filter( $starter_names, static fn( $name ) => ! mdmd_template_name_taken( $name ) ) );
};

$first_run  = mdmd_install_starter_templates();
$second_run = mdmd_install_starter_templates();

mdmd_check( 'Every demo template exists after installing', array() === $missing_names(), implode( ', ', $missing_names() ) );
mdmd_check( 'Installing demos again adds nothing', array() === $second_run, wp_json_encode( $second_run ) );

// Leave the site with exactly the templates it had.
foreach ( $first_run as $demo_id ) {
	wp_delete_post( $demo_id, true );
}

/*
 * The fresh-install bug: activating both plugins together ran activation
 * before Contact Form 7 loaded, so the demos were never seeded. The first
 * admin page load has to catch up.
 */
mdmd_check(
	'Demo seeding runs on admin load, not only on activation',
	false !== has_action( 'admin_init', array( 'MDMD_Plugin', 'maybe_seed' ) )
);

$seeded_flag = get_option( 'mdmd_seeded' );
$before_seed = $all_templates();
$admin_ids   = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );

delete_option( 'mdmd_seeded' );
wp_set_current_user( (int) ( $admin_ids[0] ?? 0 ) );

MDMD_Plugin::maybe_seed();

mdmd_check( 'An unseeded site gets its demo templates', array() === $missing_names() && (bool) get_option( 'mdmd_seeded' ) );

$after_first_seed = $all_templates();

MDMD_Plugin::maybe_seed();

mdmd_check( 'Seeding only happens once', $after_first_seed === $all_templates() );

wp_set_current_user( 0 );

foreach ( array_diff( $after_first_seed, $before_seed ) as $demo_id ) {
	wp_delete_post( $demo_id, true );
}

if ( false === $seeded_flag ) {
	delete_option( 'mdmd_seeded' );
} else {
	update_option( 'mdmd_seeded', $seeded_flag );
}

/* -------------------------------------------------------------------------
 * Branding gaps and logo reachability
 * ---------------------------------------------------------------------- */

$gap_branding = MDMD_Branding::get();

MDMD_Branding::save(
	array_merge(
		$gap_branding,
		array(
			'address'          => '',
			'social_facebook'  => '',
			'social_twitter'   => '',
			'social_linkedin'  => '',
			'social_instagram' => '',
			'footer_text'      => 'Footer line',
		)
	)
);

$gap_body = MDMD_Branding::replace(
	'<table>' .
	'<tr><td style="padding:8px 32px;"><p style="margin:0 0 12px;">[mdmd_address]</p></td></tr>' .
	'<tr><td style="padding:8px 32px;"><p style="margin:0 0 12px;">[mdmd_social_links]</p></td></tr>' .
	'<tr><td style="padding:8px 32px;"><p style="margin:0 0 12px;">[mdmd_footer_text]</p></td></tr>' .
	'<tr><td style="height:24px;line-height:24px;font-size:0;">&nbsp;</td></tr>' .
	'</table>',
	true
);

mdmd_check( 'An empty branding value leaves no empty paragraph', ! str_contains( $gap_body, '<p style="margin:0 0 12px;"></p>' ), $gap_body );
mdmd_check( 'The padded row around it goes too', 1 === substr_count( $gap_body, 'padding:8px 32px' ), $gap_body );
mdmd_check( 'The row that still has content stays', str_contains( $gap_body, 'Footer line' ) );
mdmd_check( 'A deliberate spacer row is left alone', str_contains( $gap_body, 'height:24px' ), $gap_body );

$text_body = MDMD_Branding::replace( "Address: [mdmd_address]\nFooter: [mdmd_footer_text]", false );

mdmd_check( 'Plain-text bodies are not pruned', str_contains( $text_body, 'Address: ' ) );

MDMD_Branding::save( $gap_branding );

foreach ( array( 'http://localhost/logo.png', 'http://127.0.0.1/logo.png', 'http://192.168.1.10/logo.png', 'https://mysite.local/logo.png' ) as $local_url ) {
	mdmd_check( 'Unreachable logo host flagged: ' . wp_parse_url( $local_url, PHP_URL_HOST ), MDMD_Branding::is_private_host( $local_url ) );
}

mdmd_check( 'A public logo host is not flagged', ! MDMD_Branding::is_private_host( 'https://example.com/logo.png' ) );
mdmd_check( 'An empty logo is not flagged', ! MDMD_Branding::is_private_host( '' ) );

/*
 * Contact Form 7 puts its own digest of an upload in the posted data. It must
 * never reach the screen as if the visitor had typed it.
 */
$digest_entry = array(
	'id'     => 0,
	'fields' => array(
		'your-name' => 'Jane Tester',
		'file-145'  => '78410ba7cfde1bc7950b88ac10d19f218be541fb13a4cdeb9d1041191b9270de',
	),
	'files'  => array( 'file-145' => array( array( 'name' => 'screenshot.png', 'path' => '', 'size' => 10 ) ) ),
);

$digest_answers = MDMD_Submissions::answers( $digest_entry );

mdmd_check(
	'An upload field is not shown as a typed answer',
	array( 'your-name' => 'Jane Tester' ) === $digest_answers,
	wp_json_encode( $digest_answers )
);

mdmd_check(
	'The upload is still listed as a file',
	'screenshot.png' === ( MDMD_Submissions::files( $digest_entry )['file-145'][0]['name'] ?? '' )
);

/*
 * CSV export. A cell that opens with a formula character is a real attack on
 * whoever opens the file in Excel, so it must never survive as one.
 */
foreach ( array( '=1+1', '+1', '-1', '@SUM(A1)', "\tcmd", "\rcmd" ) as $risky ) {
	mdmd_check(
		'A formula cell is neutralised: ' . trim( $risky ),
		str_starts_with( MDMD_Submissions::csv_cell( $risky ), "'" ),
		MDMD_Submissions::csv_cell( $risky )
	);
}

mdmd_check( 'An ordinary answer is left alone', 'Jane Tester' === MDMD_Submissions::csv_cell( 'Jane Tester' ) );
mdmd_check( 'An email address is left alone', 'jane@example.com' === MDMD_Submissions::csv_cell( 'jane@example.com' ) );

/*
 * Contact Form 7 compatibility. These are the pieces of CF7's API this plugin
 * leans on; if an upgrade moves one, this is where it shows up first.
 */
mdmd_check( 'CF7 is new enough', version_compare( WPCF7_VERSION, MDMD_Plugin::MIN_CF7, '>=' ), 'CF7 ' . WPCF7_VERSION );

foreach ( array( 'scan_form_tags', 'collect_mail_tags', 'prop', 'set_properties', 'title', 'id' ) as $method ) {
	mdmd_check( 'WPCF7_ContactForm::' . $method . '() exists', method_exists( 'WPCF7_ContactForm', $method ) );
}

foreach ( array( 'get_posted_data', 'uploaded_files', 'get_meta' ) as $method ) {
	mdmd_check( 'WPCF7_Submission::' . $method . '() exists', method_exists( 'WPCF7_Submission', $method ) );
}

mdmd_check( 'wpcf7_is_name() exists', function_exists( 'wpcf7_is_name' ) );
mdmd_check( 'File tag types still discoverable', array() !== MDMD_CF7_Bridge::file_tag_types(), implode( ', ', MDMD_CF7_Bridge::file_tag_types() ) );

// A full HTML document must not be wrapped a second time by CF7.
mdmd_check(
	'CF7 still leaves a complete HTML document alone',
	1 === preg_match( '%<html[>\s].*</html>%is', '<!doctype html><html><body>x</body></html>' )
		&& 0 === preg_match( '%<html[>\s].*</html>%is', '<p>partial</p>' )
);

// Every tag offered in the sidebar needs a sample value, or Preview shows the raw tag.
$sample_specials = MDMD_Renderer::sample_values( $form_id );

foreach ( array_keys( MDMD_CF7_Bridge::special_tags() ) as $special ) {
	if ( str_ends_with( $special, '_' ) ) {
		continue; // A prefix, not a tag of its own.
	}

	mdmd_check( 'Preview has a sample for [' . $special . ']', isset( $sample_specials[ $special ] ) );
}

/* -------------------------------------------------------------------------
 * Packaging — what WordPress.org checks on upload
 * ---------------------------------------------------------------------- */

$readme_path = MDMD_DIR . 'readme.txt';

mdmd_check( 'readme.txt exists', file_exists( $readme_path ) );

$readme_text = file_exists( $readme_path ) ? file_get_contents( $readme_path ) : '';

mdmd_check(
	'readme.txt declares a GPL licence',
	(bool) preg_match( '/^License:\s*GPLv2 or later/mi', $readme_text )
		&& (bool) preg_match( '#^License URI:\s*https?://(www\.)?gnu\.org/#mi', $readme_text )
);

preg_match( '/^Stable tag:\s*(\S+)/mi', $readme_text, $mdmd_stable );

mdmd_check(
	'Stable tag matches the plugin version',
	( $mdmd_stable[1] ?? '' ) === MDMD_VERSION,
	( $mdmd_stable[1] ?? 'missing' ) . ' vs ' . MDMD_VERSION
);

mdmd_check(
	'Changelog covers this version',
	str_contains( $readme_text, '= ' . MDMD_VERSION . ' =' )
);

$license_text = file_exists( MDMD_DIR . 'LICENSE' ) ? file_get_contents( MDMD_DIR . 'LICENSE' ) : '';

mdmd_check( 'LICENSE file ships with the plugin', '' !== $license_text );

mdmd_check(
	'LICENSE carries the full GPL version 2 text',
	str_contains( $license_text, 'GNU GENERAL PUBLIC LICENSE' )
		&& str_contains( $license_text, 'Version 2, June 1991' )
		&& str_contains( $license_text, 'TERMS AND CONDITIONS' )
);

/* -------------------------------------------------------------------------
 * Clean up
 * ---------------------------------------------------------------------- */

wp_delete_post( $template_id, true );
wp_delete_post( $form_id, true );

mdmd_check( 'Deleting a form prunes its assignments', array() === MDMD_CF7_Bridge::for_form( $form_id ) );

echo str_repeat( '-', 60 ) . "\n";
printf( "%d checks, %d failures\n\n", $checks, $failures );

exit( $failures ? 1 : 0 );
