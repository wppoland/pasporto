<?php

declare(strict_types=1);

namespace Pasporto\Admin;

use Pasporto\Contract\HasHooks;
use Pasporto\Service\PassportService;

defined('ABSPATH') || exit;

/**
 * Settings admin interface for Pasporto Digital Product Passport.
 */
final class SettingsPage implements HasHooks
{
    public const PAGE_SLUG = 'pasporto-settings';

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        // options.php checks manage_options unless told otherwise; match the menu capability.
        add_filter('option_page_capability_' . self::PAGE_SLUG, static fn (): string => 'manage_woocommerce');
    }

    public function registerMenu(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Pasporto DPP', 'pasporto'),
            __('Product Passport (DPP)', 'pasporto'),
            'manage_woocommerce',
            self::PAGE_SLUG,
            [$this, 'renderPage']
        );
    }

    public function registerSettings(): void
    {
        register_setting(self::PAGE_SLUG, PassportService::OPTION, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitizeSettings'],
            'default' => PassportService::DEFAULTS,
        ]);
    }

    /**
     * @param mixed $input Whatever reached update_option(); not always an array.
     * @return array<string, mixed>
     */
    public function sanitizeSettings($input): array
    {
        $input     = is_array($input) ? $input : [];
        $sanitized = [];

        $sanitized['enabled'] = ! empty($input['enabled']) ? '1' : '0';
        $sanitized['dpp_position'] = ($input['dpp_position'] ?? '') === 'summary' ? 'summary' : 'tab';

        return $sanitized;
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $settings = PassportService::settings();
        ?>
        <div class="wrap pasporto-settings-wrap">
            <h1><?php echo esc_html__('Pasporto - Digital Product Passport (DPP)', 'pasporto'); ?></h1>
            <p class="description">
                <?php echo esc_html__('Choose where the passport data you enter on each product is shown. The same data is added to WooCommerce\'s structured data for the product.', 'pasporto'); ?>
            </p>

            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE_SLUG); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable Product Passports', 'pasporto'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(PassportService::OPTION); ?>[enabled]" value="1" <?php checked(! empty($settings['enabled'])); ?> />
                                <?php echo esc_html__('Show the Digital Product Passport on product pages and add it to WooCommerce\'s structured data for the product', 'pasporto'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Display Location', 'pasporto'); ?></th>
                        <td>
                            <label>
                                <input type="radio" name="<?php echo esc_attr(PassportService::OPTION); ?>[dpp_position]" value="tab" <?php checked(($settings['dpp_position'] ?? 'tab') === 'tab'); ?> />
                                <?php echo esc_html__('In its own product tab ("Digital Product Passport (DPP)")', 'pasporto'); ?>
                            </label><br/>
                            <label>
                                <input type="radio" name="<?php echo esc_attr(PassportService::OPTION); ?>[dpp_position]" value="summary" <?php checked(($settings['dpp_position'] ?? '') === 'summary'); ?> />
                                <?php echo esc_html__('Under single product summary (near add to cart)', 'pasporto'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
