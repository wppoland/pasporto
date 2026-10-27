<?php

declare(strict_types=1);

namespace Pasporto;

use Pasporto\Admin\ProductMetaBox;
use Pasporto\Admin\SettingsPage;
use Pasporto\Service\PassportService;

defined('ABSPATH') || exit;

/**
 * Main plugin coordinator for Pasporto.
 */
final class Plugin
{
    private static ?self $instance = null;

    private PassportService $passportService;
    private ProductMetaBox $productMetaBox;
    private SettingsPage $settingsPage;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->passportService = new PassportService();
        $this->productMetaBox  = new ProductMetaBox($this->passportService);
        $this->settingsPage    = new SettingsPage();
    }

    public function boot(): void
    {
        $this->passportService->registerHooks();
        $this->productMetaBox->registerHooks();
        $this->settingsPage->registerHooks();
    }

    public function getPassportService(): PassportService
    {
        return $this->passportService;
    }
}
