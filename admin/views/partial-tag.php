<?php
/**
 * A single insertable tag chip.
 *
 * Expects $mdmd_tag (tag name without brackets) and $mdmd_label (friendly name).
 *
 * @package ManpreetDev_Mail_Designer_For_Contact_Form_7
 */

defined( 'ABSPATH' ) || exit;
?>
<span class="mdmd-tag" data-tag="<?php echo esc_attr( $mdmd_tag ); ?>" data-search="<?php echo esc_attr( strtolower( $mdmd_label . ' ' . $mdmd_tag ) ); ?>">
	<button type="button" class="mdmd-tag__insert" data-insert="<?php echo esc_attr( $mdmd_tag ); ?>"
		title="<?php echo esc_attr( sprintf( '[%s]', $mdmd_tag ) ); ?>">
		<span class="mdmd-tag__label"><?php echo esc_html( $mdmd_label ); ?></span>
		<code class="mdmd-tag__code">[<?php echo esc_html( $mdmd_tag ); ?>]</code>
	</button>
	<button type="button" class="mdmd-tag__copy" data-copy="<?php echo esc_attr( $mdmd_tag ); ?>"
		aria-label="<?php echo esc_attr( sprintf( /* translators: %s: tag name */ __( 'Copy [%s]', 'manpreetdev-mail-designer-for-contact-form-7' ), $mdmd_tag ) ); ?>">
		<span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
	</button>
</span>
