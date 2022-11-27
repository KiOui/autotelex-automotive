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
	<form action='options.php' method='post'>
		<?php
		settings_fields( 'autotelex_automotive_settings' );
		do_settings_sections( 'autotelex_automotive_settings' );
		submit_button();
		?>
	</form>
</div>
