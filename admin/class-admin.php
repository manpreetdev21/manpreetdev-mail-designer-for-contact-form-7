<?php
/**
 * Admin menus, screens, assets and the non-AJAX form handlers.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

class MDMD_Admin {

	/** Our page hook suffixes, filled in by register_menu(). */
	private static $hooks = array();

	/**
	 * Hooks the admin layer.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );

		add_action( 'admin_post_mdmd_save_branding', array( __CLASS__, 'handle_save_branding' ) );
		add_action( 'admin_post_mdmd_save_settings', array( __CLASS__, 'handle_save_settings' ) );
		add_action( 'admin_post_mdmd_export', array( __CLASS__, 'handle_export' ) );
		add_action( 'admin_post_mdmd_import', array( __CLASS__, 'handle_import' ) );
		add_action( 'admin_post_mdmd_install_demos', array( __CLASS__, 'handle_install_demos' ) );
		add_action( 'admin_post_mdmd_export_entries', array( __CLASS__, 'handle_export_entries' ) );
		add_action( 'admin_post_mdmd_clear_log', array( __CLASS__, 'handle_clear_log' ) );
		add_action( 'admin_post_mdmd_download', array( __CLASS__, 'handle_download' ) );

		// Without this, core discards the "Templates per page" screen option.
		add_filter(
			'set_screen_option_mdmd_per_page',
			static function ( $status, $option, $value ) {
				return max( 1, min( 200, absint( $value ) ) );
			},
			10,
			3
		);

		add_filter(
			'set_screen_option_mdmd_entries_per_page',
			static function ( $status, $option, $value ) {
				return max( 1, min( 200, absint( $value ) ) );
			},
			10,
			3
		);
	}

	/**
	 * Registers the top-level menu and its submenus.
	 */
	public static function register_menu() {
		$cap = MDMD_Plugin::cap();

		self::$hooks['dashboard'] = add_menu_page(
			__( 'ManpreetDev Mail Designer for Contact Form 7', 'manpreetdev-mail-designer-for-contact-form-7' ),
			__( 'Mail Designer', 'manpreetdev-mail-designer-for-contact-form-7' ),
			$cap,
			'mdmd',
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-email-alt',
			30
		);

		$submenus = array(
			'mdmd'                => array( __( 'Dashboard', 'manpreetdev-mail-designer-for-contact-form-7' ), 'render_dashboard' ),
			'mdmd-templates'      => array( __( 'Email Templates', 'manpreetdev-mail-designer-for-contact-form-7' ), 'render_templates' ),
			'mdmd-template-edit'  => array( __( 'Add New', 'manpreetdev-mail-designer-for-contact-form-7' ), 'render_editor' ),
			'mdmd-contact-forms'  => array( __( 'Contact Forms', 'manpreetdev-mail-designer-for-contact-form-7' ), 'render_contact_forms' ),
			'mdmd-assignments'    => array( __( 'Assignments', 'manpreetdev-mail-designer-for-contact-form-7' ), 'render_assignments' ),
			'mdmd-branding'       => array( __( 'Global Branding', 'manpreetdev-mail-designer-for-contact-form-7' ), 'render_branding' ),
			'mdmd-settings'       => array( __( 'Settings', 'manpreetdev-mail-designer-for-contact-form-7' ), 'render_settings' ),
			'mdmd-tools'          => array( __( 'Tools', 'manpreetdev-mail-designer-for-contact-form-7' ), 'render_tools' ),
			'mdmd-submissions'    => array( __( 'Submissions', 'manpreetdev-mail-designer-for-contact-form-7' ), 'render_submissions' ),
		);

		foreach ( $submenus as $slug => $config ) {
			list( $title, $callback ) = $config;

			$hook = add_submenu_page(
				'mdmd',
				$title,
				$title,
				$cap,
				$slug,
				array( __CLASS__, $callback )
			);

			self::$hooks[ $slug ] = $hook;
		}

		add_action( 'load-' . self::$hooks['mdmd-templates'], array( __CLASS__, 'load_templates_screen' ) );
		add_action( 'load-' . self::$hooks['mdmd-submissions'], array( __CLASS__, 'load_submissions_screen' ) );
	}

	/**
	 * Sets up per-page screen options for the templates list.
	 */
	public static function load_templates_screen() {
		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Templates per page', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'default' => 20,
				'option'  => 'mdmd_per_page',
			)
		);
	}

	/**
	 * Per-page option for the submissions list, plus the single-row delete
	 * link (a GET action, so it is handled before anything is printed).
	 */
	public static function load_submissions_screen() {
		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Submissions per page', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'default' => 20,
				'option'  => 'mdmd_entries_per_page',
			)
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the nonce is checked below.
		if ( 'delete' !== sanitize_key( wp_unslash( $_GET['entry_action'] ?? '' ) ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the nonce is checked below.
		$id = absint( $_GET['entry'] ?? 0 );

		self::verify( 'mdmd_delete_entry_' . $id );

		$entry = MDMD_Submissions::get( $id );

		MDMD_Submissions::delete( array( $id ) );

		wp_safe_redirect(
			MDMD_Plugin::url(
				'submissions',
				array(
					'form'          => $entry ? $entry['form_id'] : 0,
					'mdmd_notice' => 'entry_deleted',
				)
			)
		);
		exit;
	}

	/**
	 * Whether the current screen belongs to this plugin.
	 *
	 * @param string $hook Current admin page hook.
	 * @return bool
	 */
	private static function is_plugin_screen( $hook ) {
		return in_array( $hook, self::$hooks, true );
	}

	/**
	 * The blocks the visual builder offers, in palette order.
	 *
	 * @return array Type => label and dashicon.
	 */
	public static function block_types() {
		return array(
			'heading' => array( 'label' => __( 'Heading', 'manpreetdev-mail-designer-for-contact-form-7' ), 'icon' => 'heading' ),
			'text'    => array( 'label' => __( 'Text', 'manpreetdev-mail-designer-for-contact-form-7' ), 'icon' => 'editor-paragraph' ),
			'fields'  => array( 'label' => __( 'Form Fields', 'manpreetdev-mail-designer-for-contact-form-7' ), 'icon' => 'list-view' ),
			'button'  => array( 'label' => __( 'Button', 'manpreetdev-mail-designer-for-contact-form-7' ), 'icon' => 'button' ),
			'image'   => array( 'label' => __( 'Image', 'manpreetdev-mail-designer-for-contact-form-7' ), 'icon' => 'format-image' ),
			'divider' => array( 'label' => __( 'Divider', 'manpreetdev-mail-designer-for-contact-form-7' ), 'icon' => 'minus' ),
			'spacer'  => array( 'label' => __( 'Spacer', 'manpreetdev-mail-designer-for-contact-form-7' ), 'icon' => 'editor-expand' ),
		);
	}

	/**
	 * Strings the visual builder needs in the browser.
	 *
	 * @return array
	 */
	private static function builder_i18n() {
		return array(
			'text'          => __( 'Text', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'textHelp'      => __( 'Leave a blank line between paragraphs.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'level'         => __( 'Size', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'align'         => __( 'Alignment', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'left'          => __( 'Left', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'center'        => __( 'Centre', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'right'         => __( 'Right', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'background'    => __( 'Background', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'textColour'    => __( 'Text colour', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'buttonColour'  => __( 'Button colour', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'lineColour'    => __( 'Line colour', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'url'           => __( 'Link URL', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'imageUrl'      => __( 'Image URL', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'altText'       => __( 'Alt text', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'width'         => __( 'Width (px)', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'height'        => __( 'Height (px)', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'fieldRows'     => __( 'Rows', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'rowLabel'      => __( 'Label', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'rowTag'        => __( '[your-name]', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'addRow'        => __( 'Add row', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'addFormFields' => __( 'Add all form fields', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'removeRow'     => __( 'Remove row', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'chooseImage'   => __( 'Choose image', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'moveUp'        => __( 'Move up', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'moveDown'      => __( 'Move down', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'duplicate'     => __( 'Duplicate block', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'remove'        => __( 'Remove block', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'headingSample' => __( 'Heading', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'buttonSample'  => __( 'Click here', 'manpreetdev-mail-designer-for-contact-form-7' ),
		);
	}

	/**
	 * Loads CSS and JS on this plugin's screens only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue( $hook ) {
		if ( ! self::is_plugin_screen( $hook ) ) {
			return;
		}

		wp_enqueue_style(
			'mdmd-admin',
			MDMD_URL . 'assets/css/admin.css',
			array(),
			MDMD_VERSION
		);

		$deps = array();

		if ( self::$hooks['mdmd-branding'] === $hook ) {
			// wp.media has to be loaded before admin.js runs, or the
			// "Choose image" button binds nothing.
			wp_enqueue_media();
			$deps[] = 'media-editor';
		}

		wp_enqueue_script(
			'mdmd-admin',
			MDMD_URL . 'assets/js/admin.js',
			$deps,
			MDMD_VERSION,
			true
		);

		wp_localize_script(
			'mdmd-admin',
			'mdmd',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mdmd_admin' ),
				'i18n'    => array(
					'saving'       => __( 'Saving…', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'saved'        => __( 'Template saved.', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'error'        => __( 'Something went wrong. Please try again.', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'confirm'      => __( 'Are you sure?', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'cancel'       => __( 'Cancel', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'deleteTitle'  => __( 'Delete template', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'deleteBody'   => __( 'Are you sure you want to delete this template? This cannot be undone.', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'deleteButton' => __( 'Delete template', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'unsaved'      => __( 'You have unsaved changes.', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'copied'       => __( 'Tag copied to clipboard.', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'testSent'     => __( 'Test email sent.', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'testPrompt'   => __( 'Send the test email to:', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'sendTest'     => __( 'Send test', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'previewTitle' => __( 'Email preview', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'close'        => __( 'Close', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'save'         => __( 'Save', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'subject'      => __( 'Subject:', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'remove'       => __( 'Remove', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'keep'         => __( 'Keep', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'replaceWith'  => __( 'Replace with…', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'noTags'       => __( 'This form has no fields that can be used in an email.', 'manpreetdev-mail-designer-for-contact-form-7' ),
					/* translators: %s: comma-separated list of mail tags */
					'badAttach'    => __( 'Not a file upload field on the selected form: %s. Nothing will be attached.', 'manpreetdev-mail-designer-for-contact-form-7' ),
					'newTagsOne'   => __( '1 form tag is available but not used in this template.', 'manpreetdev-mail-designer-for-contact-form-7' ),
					/* translators: %d: number of unused form tags */
					'newTagsMany'  => __( '%d form tags are available but not used in this template.', 'manpreetdev-mail-designer-for-contact-form-7' ),
				),
			)
		);

		if ( self::$hooks['mdmd-template-edit'] === $hook ) {
			// Core already ships CodeMirror; no third-party editor needed.
			$settings = wp_enqueue_code_editor(
				array(
					'type'       => 'text/html',
					'codemirror' => array(
						'lineNumbers' => true,
						'lineWrapping' => true,
					),
				)
			);

			// The builder picks images out of the media library.
			wp_enqueue_media();

			wp_enqueue_script(
				'mdmd-editor',
				MDMD_URL . 'assets/js/editor.js',
				array( 'mdmd-admin' ),
				MDMD_VERSION,
				true
			);

			wp_enqueue_script(
				'mdmd-builder',
				MDMD_URL . 'assets/js/builder.js',
				array( 'mdmd-editor', 'media-editor', 'jquery-ui-sortable', 'jquery-ui-draggable' ),
				MDMD_VERSION,
				true
			);

			wp_localize_script(
				'mdmd-editor',
				'mdmdEditor',
				array(
					// False when the user turned syntax highlighting off; the
					// editor then falls back to a plain textarea.
					'codeEditor' => $settings ? $settings : false,
					'blocks'     => wp_list_pluck( self::block_types(), 'label' ),
					'i18n'       => self::builder_i18n(),
				)
			);
		}
	}

	/* ---------------------------------------------------------------------
	 * Screens
	 * ------------------------------------------------------------------ */

	/**
	 * Renders one of the view files.
	 *
	 * @param string $view View file base name.
	 * @param array  $data Variables extracted into the view.
	 */
	private static function view( $view, $data = array() ) {
		MDMD_Plugin::require_cap();

		$file = MDMD_DIR . 'admin/views/' . $view . '.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- controlled, internal view data.
		extract( $data );

		require $file;
	}

	/** Dashboard screen. */
	public static function render_dashboard() {
		self::view( 'dashboard' );
	}

	/** Templates list screen. */
	public static function render_templates() {
		self::view( 'templates' );
	}

	/** Template editor screen. */
	public static function render_editor() {
		self::view( 'editor' );
	}

	/** Contact forms overview screen. */
	public static function render_contact_forms() {
		self::view( 'contact-forms' );
	}

	/** Assignments screen. */
	public static function render_assignments() {
		self::view( 'assignments' );
	}

	/** Global branding screen. */
	public static function render_branding() {
		self::view( 'branding' );
	}

	/** Settings screen. */
	public static function render_settings() {
		self::view( 'settings' );
	}

	/** Tools screen. */
	public static function render_tools() {
		self::view( 'tools' );
	}

	/**
	 * Streams one stored upload to an administrator.
	 *
	 * The files live outside the web root's reach on purpose, so this is the
	 * only way to them, and it costs a capability check and a nonce.
	 */
	public static function handle_download() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the nonce is verified on the next line.
		$entry_id = absint( $_GET['entry'] ?? 0 );

		self::verify( 'mdmd_download_' . $entry_id );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified above.
		$field = sanitize_text_field( wp_unslash( $_GET['field'] ?? '' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified above.
		$index = absint( $_GET['index'] ?? 0 );

		$entry = MDMD_Submissions::get( $entry_id );
		$files = $entry ? MDMD_Submissions::files( $entry ) : array();
		$file  = $files[ $field ][ $index ] ?? null;
		$path  = $file ? MDMD_Submissions::file_path( $file['path'] ) : '';

		if ( ! $path ) {
			wp_die(
				esc_html__( 'That file is no longer available.', 'manpreetdev-mail-designer-for-contact-form-7' ),
				404
			);
		}

		nocache_headers();

		header( 'Content-Type: ' . ( wp_check_filetype( $path )['type'] ?: 'application/octet-stream' ) );
		header( 'Content-Length: ' . filesize( $path ) );
		header(
			'Content-Disposition: attachment; filename="' . sanitize_file_name( $file['name'] ) . '"'
		);
		// The browser must not sniff a different type out of the bytes.
		header( 'X-Content-Type-Options: nosniff' );

		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- streaming a local file, not fetching a URL.
		exit;
	}

	/** Renders the submissions log. */
	public static function render_submissions() {
		self::view( 'submissions' );
	}

	/**
	 * Shared page header markup.
	 *
	 * @param string $title   Screen title.
	 * @param string $actions Optional HTML for the right-hand actions.
	 */
	public static function header( $title, $actions = '' ) {
		printf(
			'<div class="mdmd-header"><div><h1 class="mdmd-header__title">%s</h1></div><div class="mdmd-header__actions">%s</div></div>',
			esc_html( $title ),
			wp_kses_post( $actions )
		);
	}

	/**
	 * Prints an admin notice queued through the redirect URL.
	 */
	public static function flash() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only message key.
		$notice = isset( $_GET['mdmd_notice'] ) ? sanitize_key( wp_unslash( $_GET['mdmd_notice'] ) ) : '';

		if ( ! $notice ) {
			return;
		}

		$messages = array(
			'branding_saved'  => array( 'success', __( 'Branding saved.', 'manpreetdev-mail-designer-for-contact-form-7' ) ),
			'settings_saved'  => array( 'success', __( 'Settings saved.', 'manpreetdev-mail-designer-for-contact-form-7' ) ),
			'imported'        => array( 'success', __( 'Templates imported.', 'manpreetdev-mail-designer-for-contact-form-7' ) ),
			'import_failed'   => array( 'error', __( 'That file could not be imported. Please upload a valid export file.', 'manpreetdev-mail-designer-for-contact-form-7' ) ),
			'log_cleared'     => array( 'success', __( 'Debug log cleared.', 'manpreetdev-mail-designer-for-contact-form-7' ) ),
			'template_saved'  => array( 'success', __( 'Template saved.', 'manpreetdev-mail-designer-for-contact-form-7' ) ),
			'entry_deleted'   => array( 'success', __( 'Submission deleted.', 'manpreetdev-mail-designer-for-contact-form-7' ) ),
			'demos_installed' => array( 'success', __( 'Demo templates installed.', 'manpreetdev-mail-designer-for-contact-form-7' ) ),
			'demos_present'   => array( 'info', __( 'All demo templates are already installed. Nothing was added.', 'manpreetdev-mail-designer-for-contact-form-7' ) ),
		);

		if ( ! isset( $messages[ $notice ] ) ) {
			return;
		}

		list( $type, $message ) = $messages[ $notice ];

		printf(
			'<div class="mdmd-alert mdmd-alert--%s" role="status">%s</div>',
			esc_attr( $type ),
			esc_html( $message )
		);
	}

	/* ---------------------------------------------------------------------
	 * Form handlers
	 * ------------------------------------------------------------------ */

	/**
	 * Verifies nonce and capability for an admin-post request.
	 *
	 * @param string $action Nonce action.
	 */
	private static function verify( $action ) {
		MDMD_Plugin::require_cap();
		check_admin_referer( $action );
	}

	/**
	 * Redirects back to a plugin screen with a notice.
	 *
	 * @param string $page   Page slug suffix.
	 * @param string $notice Notice key.
	 */
	private static function redirect( $page, $notice ) {
		wp_safe_redirect( MDMD_Plugin::url( $page, array( 'mdmd_notice' => $notice ) ) );
		exit;
	}

	/** Saves the global branding form. */
	public static function handle_save_branding() {
		self::verify( 'mdmd_save_branding' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- each field is sanitized in MDMD_Branding::save(); nonce checked in self::verify() above.
		MDMD_Branding::save( wp_unslash( (array) ( $_POST['branding'] ?? array() ) ) );

		self::redirect( 'branding', 'branding_saved' );
	}

	/** Saves the settings form. */
	public static function handle_save_settings() {
		self::verify( 'mdmd_save_settings' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- sanitized field by field below; nonce checked in self::verify() above.
		$input = wp_unslash( (array) ( $_POST['settings'] ?? array() ) );

		$clean = array(
			'default_type'        => ( isset( $input['default_type'] ) && 'text' === $input['default_type'] ) ? 'text' : 'html',
			'default_sender'      => sanitize_text_field( $input['default_sender'] ?? '' ),
			'test_recipient'      => sanitize_email( $input['test_recipient'] ?? '' ),
			'debug'               => empty( $input['debug'] ) ? 0 : 1,
			'delete_on_uninstall' => empty( $input['delete_on_uninstall'] ) ? 0 : 1,
		);

		update_option( MDMD_Plugin::SETTINGS, $clean );

		self::redirect( 'settings', 'settings_saved' );
	}

	/**
	 * Streams the submissions currently being viewed as a CSV file.
	 *
	 * The same form, email-result and search filters the screen is using are
	 * applied, so what downloads is what was on screen — not just the page
	 * that happened to be open.
	 */
	public static function handle_export_entries() {
		self::verify( 'mdmd_export_entries' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified above.
		$form_id = absint( $_REQUEST['form'] ?? 0 );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified above.
		$status = sanitize_key( wp_unslash( $_REQUEST['status'] ?? '' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified above.
		$search = sanitize_text_field( wp_unslash( $_REQUEST['s'] ?? '' ) );

		$filters = array(
			'form_id'  => $form_id,
			'status'   => $status,
			'search'   => $search,
			'per_page' => 200,
		);

		/*
		 * Two passes over the rows, 200 at a time. The first works out which
		 * columns this export needs; the second writes them. Batching keeps a
		 * long log from being held in memory all at once.
		 */
		$answers = array();
		$files   = array();

		// A chosen form contributes its own field order first.
		foreach ( MDMD_CF7_Bridge::form_tags( $form_id ) as $tag ) {
			if ( $tag['is_file'] ) {
				$files[ $tag['name'] ] = $tag['label'];
			} else {
				$answers[ $tag['name'] ] = $tag['label'];
			}
		}

		foreach ( self::entry_batches( $filters ) as $batch ) {
			foreach ( $batch as $entry ) {
				foreach ( array_keys( MDMD_Submissions::answers( $entry ) ) as $name ) {
					$answers[ $name ] = $answers[ $name ] ?? MDMD_CF7_Bridge::friendly_label( $name );
				}

				foreach ( array_keys( MDMD_Submissions::files( $entry ) ) as $name ) {
					$files[ $name ] = $files[ $name ] ?? MDMD_CF7_Bridge::friendly_label( $name );
				}
			}
		}

		$name = $form_id ? sanitize_title( MDMD_Submissions::forms()[ $form_id ]['title'] ?? (string) $form_id ) : 'all-forms';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=mdmd-submissions-' . $name . '-' . gmdate( 'Y-m-d' ) . '.csv' );
		header( 'X-Content-Type-Options: nosniff' );

		$out = fopen( 'php://output', 'w' );

		// Without the byte order mark Excel reads UTF-8 as its own codepage.
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- writing to the response, not the filesystem.

		self::csv_row(
			$out,
			array_merge(
				array(
					__( 'Submitted', 'manpreetdev-mail-designer-for-contact-form-7' ),
					__( 'Form', 'manpreetdev-mail-designer-for-contact-form-7' ),
					__( 'Email result', 'manpreetdev-mail-designer-for-contact-form-7' ),
					__( 'IP address', 'manpreetdev-mail-designer-for-contact-form-7' ),
				),
				array_values( $answers ),
				array_values( $files )
			)
		);

		foreach ( self::entry_batches( $filters ) as $batch ) {
			foreach ( $batch as $entry ) {
				$row = array(
					$entry['submitted_at'],
					$entry['form_title'],
					'sent' === $entry['status']
						? __( 'Sent', 'manpreetdev-mail-designer-for-contact-form-7' )
						: __( 'Not sent', 'manpreetdev-mail-designer-for-contact-form-7' ),
					$entry['remote_ip'],
				);

				$entry_answers = MDMD_Submissions::answers( $entry );

				foreach ( array_keys( $answers ) as $field ) {
					$row[] = isset( $entry_answers[ $field ] )
						? MDMD_Submissions::flatten( $entry_answers[ $field ] )
						: '';
				}

				$entry_files = MDMD_Submissions::files( $entry );

				foreach ( array_keys( $files ) as $field ) {
					$row[] = isset( $entry_files[ $field ] )
						? implode( ', ', wp_list_pluck( $entry_files[ $field ], 'name' ) )
						: '';
				}

				self::csv_row( $out, $row );
			}
		}

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- closing the response stream.
		exit;
	}

	/**
	 * Yields matching submissions a page at a time.
	 *
	 * @param array $filters Query arguments, including per_page.
	 * @return Generator
	 */
	private static function entry_batches( $filters ) {
		$page = 1;

		do {
			$results = MDMD_Submissions::query( array( 'page' => $page ) + $filters );

			if ( $results['items'] ) {
				yield $results['items'];
			}

			$seen = $page * $filters['per_page'];
			++$page;
		} while ( $seen < $results['total'] );
	}

	/**
	 * Writes one CSV line.
	 *
	 * A cell that opens with =, +, - or @ is treated as a formula by Excel and
	 * Google Sheets, so it is quoted into being plain text first.
	 *
	 * @param resource $handle Output stream.
	 * @param array    $row    Cells.
	 */
	private static function csv_row( $handle, $row ) {
		fputcsv( $handle, array_map( array( 'MDMD_Submissions', 'csv_cell' ), $row ), ',', '"', '' );
	}

	/** Installs whichever starter templates are missing. */
	public static function handle_install_demos() {
		self::verify( 'mdmd_install_demos' );

		require_once MDMD_DIR . 'includes/starter-templates.php';

		self::redirect( 'tools', mdmd_install_starter_templates() ? 'demos_installed' : 'demos_present' );
	}

	/** Streams a JSON export of all templates. */
	public static function handle_export() {
		self::verify( 'mdmd_export' );

		$templates = array();

		foreach ( array_keys( MDMD_Template_Post_Type::options() ) as $id ) {
			$template = MDMD_Template_Post_Type::get( $id );

			if ( ! $template ) {
				continue;
			}

			unset( $template['id'], $template['author'], $template['modified'], $template['form_context'] );

			$templates[] = $template;
		}

		$payload = array(
			'format'    => 'mdmd',
			'version'   => MDMD_VERSION,
			'exported'  => gmdate( 'c' ),
			'branding'  => MDMD_Branding::get(),
			'templates' => $templates,
		);

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=mdmd-email-templates-' . gmdate( 'Y-m-d' ) . '.json' );
		header( 'X-Content-Type-Options: nosniff' );

		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		exit;
	}

	/** Imports templates from an uploaded JSON file. */
	public static function handle_import() {
		self::verify( 'mdmd_import' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce checked in self::verify(); is_uploaded_file() is the validation that matters for a temp path.
		if ( empty( $_FILES['import_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['import_file']['tmp_name'] ) ) {
			self::redirect( 'tools', 'import_failed' );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- server-side temp path from $_FILES; nonce checked in self::verify() above.
		$raw = file_get_contents( $_FILES['import_file']['tmp_name'] );

		$data = json_decode( (string) $raw, true );

		if ( ! is_array( $data ) || 'mdmd' !== ( $data['format'] ?? '' ) || empty( $data['templates'] ) || ! is_array( $data['templates'] ) ) {
			self::redirect( 'tools', 'import_failed' );
		}

		// Branding is exported too, but it is global: only restore on request.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in self::verify() above.
		if ( ! empty( $_POST['import_branding'] ) && ! empty( $data['branding'] ) && is_array( $data['branding'] ) ) {
			MDMD_Branding::save( $data['branding'] );
		}

		$imported = 0;

		foreach ( $data['templates'] as $template ) {
			if ( ! is_array( $template ) || empty( $template['name'] ) ) {
				continue;
			}

			// Never trust an ID from a file: always insert a new template.
			$template['id'] = 0;

			// Imported templates land as drafts so nothing goes live unreviewed.
			$template['status'] = 'draft';

			if ( ! is_wp_error( MDMD_Template_Post_Type::save( $template ) ) ) {
				++$imported;
			}
		}

		self::redirect( 'tools', $imported ? 'imported' : 'import_failed' );
	}

	/** Empties the debug log. */
	public static function handle_clear_log() {
		self::verify( 'mdmd_clear_log' );

		delete_option( 'mdmd_log' );

		self::redirect( 'tools', 'log_cleared' );
	}
}
