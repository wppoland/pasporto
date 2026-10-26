<?php
/**
 * Autoloading: prefer Composer classmap when present. Fall back to a
 * minimal PSR-4 autoloader so the plugin still boots if vendor/ is absent.
 *
 * @package Pasporto
 */

declare(strict_types=1);

namespace Pasporto;

defined('ABSPATH') || exit;

$pasporto_composer = __DIR__ . '/vendor/autoload.php';
if (is_readable($pasporto_composer)) {
    require_once $pasporto_composer;
    return;
}

spl_autoload_register(static function (string $class): void {
    $prefix  = 'Pasporto\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative = substr($class, $len);
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (is_readable($file)) {
        require_once $file;
    }
});
