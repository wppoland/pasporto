<?php
/**
 * Uninstall routine: clean up plugin options.
 *
 * @package Pasporto
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('pasporto_settings');
