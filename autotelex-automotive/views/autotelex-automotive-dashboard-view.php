<?php
/**
 * Print admin dashboard
 *
 * @package autotelex-automotive
 */

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Autotelex Automotive Dashboard', 'autotelex-automotive' ); ?></h1>
	<hr class="wp-header-end">
	<p><?php esc_html_e( 'Autotelex Automotive settings', 'autotelex-automotive' ); ?></p>
	<form action='/wp-admin/admin.php?page=aa_admin_menu' method='post'>
		<?php
		settings_fields( 'autotelex_automotive_settings' );
		do_settings_sections( 'aa_admin_menu' );
		submit_button();
		?>
	</form>
</div>
