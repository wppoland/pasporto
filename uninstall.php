<?php
/**
 * Uninstall routine: clean up plugin options.
 *
 * @package Pasporto
 */

declare(strict_types=1);

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Remove this plugin's options from the current site.
 */
function pasporto_uninstall_site(): void {
	delete_option( 'pasporto_settings' );
}

// Options are per site, so a network uninstall has to visit every site.
if ( is_multisite() ) {
	$pasporto_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $pasporto_site_ids as $pasporto_site_id ) {
		switch_to_blog( (int) $pasporto_site_id );
		pasporto_uninstall_site();
		restore_current_blog();
	}
	unset( $pasporto_site_ids, $pasporto_site_id );
} else {
	pasporto_uninstall_site();
}
