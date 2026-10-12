<?php
/**
 * The nine starter templates seeded on first activation.
 *
 * Every HTML starter is a complete <html> document on purpose: CF7's
 * WPCF7_Mail::htmlize() only skips its own wrapper when the body already
 * matches <html>...</html>, so partial documents would end up double-wrapped.
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wraps content in the shared, email-safe HTML shell.
 *
 * @param string $heading Panel heading.
 * @param string $inner   Inner HTML for the content cell.
 * @return string
 */
function mdmd_html_shell( $heading, $inner ) {
	return '<!doctype html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>' . $heading . '</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f1f1;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f1f1;padding:24px 12px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:100%;background-color:#ffffff;border:1px solid #e0e0e0;border-radius:6px;overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Helvetica,Arial,sans-serif;color:#1d2327;">

<tr>
<td style="background-color:[mdmd_primary_color];padding:24px 32px;" align="left">
[mdmd_logo]
</td>
</tr>

<tr>
<td style="padding:32px;">
<h1 style="margin:0 0 16px;font-size:20px;line-height:1.3;font-weight:600;color:[mdmd_secondary_color];">' . $heading . '</h1>
' . $inner . '
</td>
</tr>

<tr>
<td style="padding:20px 32px;background-color:#fafafa;border-top:1px solid #e0e0e0;font-size:12px;line-height:1.6;color:#646970;">
<p style="margin:0 0 8px;">[mdmd_footer_text]</p>
<p style="margin:0 0 8px;">[mdmd_address]</p>
<p style="margin:0;">[mdmd_social_links]</p>
<p style="margin:8px 0 0;">&copy; [mdmd_year] [mdmd_company_name] &nbsp;·&nbsp; <a href="[mdmd_website]" style="color:[mdmd_primary_color];text-decoration:none;">[mdmd_website]</a></p>
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>';
}

/**
 * Builds a two-column details table from label => tag pairs.
 *
 * @param array $rows Label => CF7 tag markup.
 * @return string
 */
function mdmd_html_rows( $rows ) {
	$html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;font-size:14px;line-height:1.6;">';

	foreach ( $rows as $label => $tag ) {
		$html .= '
<tr>
<td width="140" valign="top" style="padding:10px 12px 10px 0;border-bottom:1px solid #f0f0f1;color:#646970;font-weight:600;">' . $label . '</td>
<td valign="top" style="padding:10px 0;border-bottom:1px solid #f0f0f1;color:#1d2327;">' . $tag . '</td>
</tr>';
	}

	return $html . '
</table>';
}

/**
 * The starter template definitions.
 *
 * @return array
 */
function mdmd_starter_templates() {
	$paragraph = 'style="margin:0 0 16px;font-size:14px;line-height:1.7;"';
	$button    = static function ( $label, $href ) {
		return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 16px;">
<tr><td align="center" bgcolor="[mdmd_primary_color]" style="border-radius:4px;">
<a href="' . $href . '" style="display:inline-block;padding:12px 24px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;">' . $label . '</a>
</td></tr>
</table>';
	};

	return array(

		array(
			'name'         => __( 'Contact Form Notification', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'type'         => 'html',
			'category'     => __( 'Admin', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'subject'      => __( 'New enquiry from [your-name]', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'preview_text' => __( 'A new message was submitted on your website.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'description'  => __( 'Admin notification listing every submitted field.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'headers'      => 'Reply-To: [your-email]',
			'body'         => mdmd_html_shell(
				__( 'New Contact Enquiry', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'<p ' . $paragraph . '>' . __( 'Someone has submitted the contact form on [mdmd_company_name].', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
				. mdmd_html_rows(
					array(
						__( 'Name', 'manpreetdev-mail-designer-for-contact-form-7' )    => '[your-name]',
						__( 'Email', 'manpreetdev-mail-designer-for-contact-form-7' )   => '<a href="mailto:[your-email]" style="color:[mdmd_primary_color];">[your-email]</a>',
						__( 'Phone', 'manpreetdev-mail-designer-for-contact-form-7' )   => '[your-phone]',
						__( 'Subject', 'manpreetdev-mail-designer-for-contact-form-7' ) => '[your-subject]',
						__( 'Message', 'manpreetdev-mail-designer-for-contact-form-7' ) => '[your-message]',
					)
				)
				. '<p style="margin:20px 0 0;font-size:12px;color:#646970;">' . __( 'Received [_date] at [_time] from [_remote_ip] on [_url]', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
			),
		),

		array(
			'name'         => __( 'Customer Thank You', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'type'         => 'html',
			'category'     => __( 'Customer', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'recipient'    => '[your-email]',
			'subject'      => __( 'Thank you for contacting us', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'preview_text' => __( 'We have received your message and will reply shortly.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'description'  => __( 'Confirmation sent to the person who filled in the form.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'body'         => mdmd_html_shell(
				__( 'Thank you for getting in touch', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'<p ' . $paragraph . '>' . __( 'Hello [your-name],', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
				. '<p ' . $paragraph . '>' . __( 'Thank you for contacting [mdmd_company_name]. We have received your message and a member of our team will reply as soon as possible.', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
				. '<p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#646970;">' . __( 'Your message', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
				. '<div style="padding:16px;background-color:#f6f7f7;border-left:3px solid [mdmd_primary_color];font-size:14px;line-height:1.7;">[your-message]</div>'
				. '<p style="margin:16px 0 0;font-size:14px;line-height:1.7;">' . __( 'Kind regards,', 'manpreetdev-mail-designer-for-contact-form-7' ) . '<br />[mdmd_company_name]</p>'
			),
		),

		array(
			'name'         => __( 'Quote Request', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'type'         => 'html',
			'category'     => __( 'Admin', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'subject'      => __( 'Quote request from [your-name]', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'preview_text' => __( 'A new quote request is waiting for you.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'description'  => __( 'Admin notification for pricing and quote enquiries.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'headers'      => 'Reply-To: [your-email]',
			'body'         => mdmd_html_shell(
				__( 'New Quote Request', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'<p ' . $paragraph . '>' . __( 'A visitor has requested a quote.', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
				. mdmd_html_rows(
					array(
						__( 'Name', 'manpreetdev-mail-designer-for-contact-form-7' )    => '[your-name]',
						__( 'Company', 'manpreetdev-mail-designer-for-contact-form-7' ) => '[company]',
						__( 'Email', 'manpreetdev-mail-designer-for-contact-form-7' )   => '<a href="mailto:[your-email]" style="color:[mdmd_primary_color];">[your-email]</a>',
						__( 'Phone', 'manpreetdev-mail-designer-for-contact-form-7' )   => '[your-phone]',
						__( 'Budget', 'manpreetdev-mail-designer-for-contact-form-7' )  => '[budget]',
						__( 'Details', 'manpreetdev-mail-designer-for-contact-form-7' ) => '[your-message]',
					)
				)
			),
		),

		array(
			'name'         => __( 'Booking Request', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'type'         => 'html',
			'category'     => __( 'Admin', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'subject'      => __( 'Booking request from [your-name]', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'preview_text' => __( 'A new booking request has come in.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'description'  => __( 'Admin notification for appointment and booking forms.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'headers'      => 'Reply-To: [your-email]',
			'body'         => mdmd_html_shell(
				__( 'New Booking Request', 'manpreetdev-mail-designer-for-contact-form-7' ),
				mdmd_html_rows(
					array(
						__( 'Name', 'manpreetdev-mail-designer-for-contact-form-7' )     => '[your-name]',
						__( 'Email', 'manpreetdev-mail-designer-for-contact-form-7' )    => '<a href="mailto:[your-email]" style="color:[mdmd_primary_color];">[your-email]</a>',
						__( 'Phone', 'manpreetdev-mail-designer-for-contact-form-7' )    => '[your-phone]',
						__( 'Date', 'manpreetdev-mail-designer-for-contact-form-7' )     => '[booking-date]',
						__( 'Time', 'manpreetdev-mail-designer-for-contact-form-7' )     => '[booking-time]',
						__( 'People', 'manpreetdev-mail-designer-for-contact-form-7' )   => '[guests]',
						__( 'Requests', 'manpreetdev-mail-designer-for-contact-form-7' ) => '[your-message]',
					)
				)
				. '<p style="margin:20px 0 0;font-size:12px;color:#646970;">' . __( 'Submitted [_date] at [_time].', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
			),
		),

		array(
			'name'         => __( 'Support Request', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'type'         => 'html',
			'category'     => __( 'Admin', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'subject'      => __( 'Support request: [your-subject]', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'preview_text' => __( 'A customer needs help.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'description'  => __( 'Admin notification for help desk and support forms.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'headers'      => 'Reply-To: [your-email]',
			'body'         => mdmd_html_shell(
				__( 'New Support Request', 'manpreetdev-mail-designer-for-contact-form-7' ),
				mdmd_html_rows(
					array(
						__( 'From', 'manpreetdev-mail-designer-for-contact-form-7' )     => '[your-name] &lt;[your-email]&gt;',
						__( 'Subject', 'manpreetdev-mail-designer-for-contact-form-7' )  => '[your-subject]',
						__( 'Priority', 'manpreetdev-mail-designer-for-contact-form-7' ) => '[priority]',
						__( 'Details', 'manpreetdev-mail-designer-for-contact-form-7' )  => '[your-message]',
					)
				)
				. $button( __( 'Reply to customer', 'manpreetdev-mail-designer-for-contact-form-7' ), 'mailto:[your-email]' )
			),
		),

		array(
			'name'         => __( 'Newsletter Signup', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'type'         => 'html',
			'category'     => __( 'Customer', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'recipient'    => '[your-email]',
			'subject'      => __( 'You are subscribed', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'preview_text' => __( 'Welcome aboard — your subscription is confirmed.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'description'  => __( 'Confirmation for newsletter and mailing list signups.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'body'         => mdmd_html_shell(
				__( 'Welcome aboard', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'<p ' . $paragraph . '>' . __( 'Hello [your-name],', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
				. '<p ' . $paragraph . '>' . __( 'Thanks for subscribing to updates from [mdmd_company_name]. We will send you news now and then — never spam.', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
				. $button( __( 'Visit our website', 'manpreetdev-mail-designer-for-contact-form-7' ), '[mdmd_website]' )
			),
		),

		array(
			'name'         => __( 'File Upload Notification', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'type'         => 'html',
			'category'     => __( 'Admin', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'subject'      => __( 'New file from [your-name]', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'preview_text' => __( 'A new submission arrived with an attachment.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'description'  => __( 'Admin notification for forms with an upload field. Change [your-file] to match your own field name.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'headers'      => 'Reply-To: [your-email]',
			'attachments'  => '[your-file]',
			'body'         => mdmd_html_shell(
				__( 'New File Submission', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'<p ' . $paragraph . '>' . __( 'Someone submitted a file through [mdmd_company_name].', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
				. mdmd_html_rows(
					array(
						__( 'Name', 'manpreetdev-mail-designer-for-contact-form-7' )          => '[your-name]',
						__( 'Email', 'manpreetdev-mail-designer-for-contact-form-7' )         => '<a href="mailto:[your-email]" style="color:[mdmd_primary_color];">[your-email]</a>',
						__( 'Message', 'manpreetdev-mail-designer-for-contact-form-7' )       => '[your-message]',
						__( 'Uploaded file', 'manpreetdev-mail-designer-for-contact-form-7' ) => '[your-file]',
					)
				)
				. '<p style="margin:20px 0 0;font-size:12px;color:#646970;">' . __( 'The file is attached to this email. If nothing is attached, the visitor did not upload one.', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
			),
		),

		array(
			'name'         => __( 'Simple Notification', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'type'         => 'text',
			'category'     => __( 'Admin', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'subject'      => __( 'New enquiry from [your-name]', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'description'  => __( 'Plain-text admin notification. Works in every mail client.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'headers'      => 'Reply-To: [your-email]',
			'body'         => __(
				'New Contact Enquiry
==================

Name:  [your-name]
Email: [your-email]
Phone: [your-phone]

Message:
[your-message]

--
Sent from [_site_title] ([_site_url])
Received [_date] at [_time] from [_remote_ip]',
				'manpreetdev-mail-designer-for-contact-form-7'
			),
		),

		array(
			'name'        => __( 'Blank Template', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'type'        => 'html',
			'category'    => __( 'Starter', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'status'      => 'draft',
			'subject'     => '',
			'description' => __( 'An empty branded shell to build your own layout in.', 'manpreetdev-mail-designer-for-contact-form-7' ),
			'body'        => mdmd_html_shell(
				__( 'Heading', 'manpreetdev-mail-designer-for-contact-form-7' ),
				'<p style="margin:0 0 16px;font-size:14px;line-height:1.7;">' . __( 'Write your email here, and insert form tags from the sidebar.', 'manpreetdev-mail-designer-for-contact-form-7' ) . '</p>'
			),
		),
	);
}

/**
 * Inserts any starter templates that are not already on the site.
 *
 * Safe to run again: a starter whose name is already taken is left alone, so
 * the Tools button tops up missing demos without ever duplicating one.
 *
 * @return int[] IDs of the templates created.
 */
function mdmd_install_starter_templates() {
	$created = array();

	foreach ( mdmd_starter_templates() as $template ) {
		if ( mdmd_template_name_taken( $template['name'] ) ) {
			continue;
		}

		$id = MDMD_Template_Post_Type::save(
			wp_parse_args(
				$template,
				array(
					'status'        => 'publish',
					'recipient'     => '',
					'sender'        => '',
					'headers'       => '',
					'preview_text'  => '',
					'exclude_blank' => 1,
				)
			)
		);

		if ( ! is_wp_error( $id ) ) {
			$created[] = $id;
		}
	}

	return $created;
}

/**
 * Whether a live template already uses this name. Trashed ones do not count,
 * so a demo someone binned can be brought back.
 *
 * @param string $name Template name.
 * @return bool
 */
function mdmd_template_name_taken( $name ) {
	return (bool) get_posts(
		array(
			'post_type'      => MDMD_Template_Post_Type::POST_TYPE,
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'title'          => $name,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
}
