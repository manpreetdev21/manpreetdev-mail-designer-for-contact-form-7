<?php
/**
 * Uninstall routine.
 *
 * Templates are only destroyed when the administrator explicitly opted in
 * under Settings → Advanced. The default is to leave everything alone.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$mdmd_settings = (array) get_option( 'mdmd_settings', array() );

if ( empty( $mdmd_settings['delete_on_uninstall'] ) ) {
	return;
}

$mdmd_templates = get_posts(
	array(
		'post_type'      => 'mdmd_template',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

foreach ( $mdmd_templates as $mdmd_id ) {
	wp_delete_post( $mdmd_id, true );
}

// The submissions log lives in its own table.
global $wpdb;

// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- own table, uninstall only.
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mdmd_submissions' );

// Uploaded files kept alongside the submissions.
$mdmd_uploads = wp_upload_dir();
$mdmd_dir     = untrailingslashit( $mdmd_uploads['basedir'] ) . '/mdmd-submissions';

if ( is_dir( $mdmd_dir ) ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';

	global $wp_filesystem;

	if ( WP_Filesystem() && $wp_filesystem ) {
		$wp_filesystem->delete( $mdmd_dir, true );
	}
}

foreach ( array( 'mdmd_settings', 'mdmd_branding', 'mdmd_assignments', 'mdmd_log', 'mdmd_seeded', 'mdmd_db_version' ) as $mdmd_option ) {
	delete_option( $mdmd_option );
}

// Per-user screen option for the templates list.
delete_metadata( 'user', 0, 'mdmd_per_page', '', true );
delete_metadata( 'user', 0, 'mdmd_entries_per_page', '', true );
