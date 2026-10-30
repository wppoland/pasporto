<?php

declare(strict_types=1);

namespace Pasporto\Service;

use Pasporto\Contract\HasHooks;
use Pasporto\Model\PassportData;

defined('ABSPATH') || exit;

/**
 * Service orchestrating Digital Product Passport display and schema markup.
 */
final class PassportService implements HasHooks
{
    public const OPTION = 'pasporto_settings';

    public const META_PASSPORT_DATA = '_pasporto_dpp_data';

    /**
     * Settings a fresh install runs with, before anything is saved.
     */
    public const DEFAULTS = [
        'enabled' => '1',
        'dpp_position' => 'tab',
    ];

    /**
     * @var array<string, mixed>
     */
    private array $settings;

    public function __construct()
    {
        $this->settings = self::settings();
    }

    /**
     * Saved settings over the defaults.
     *
     * @return array<string, mixed>
     */
    public static function settings(): array
    {
        $raw = get_option(self::OPTION);

        return array_merge(self::DEFAULTS, is_array($raw) ? $raw : []);
    }

    public function isEnabled(): bool
    {
        return ! empty($this->settings['enabled']);
    }

    public function registerHooks(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $position = (string) ($this->settings['dpp_position'] ?? 'tab');

        if ($position === 'summary') {
            add_action('woocommerce_single_product_summary', [$this, 'renderPassportSummary'], 28);
        } else {
            add_filter('woocommerce_product_tabs', [$this, 'registerPassportTab']);
        }

        add_filter('woocommerce_structured_data_product', [$this, 'extendStructuredData'], 10, 2);
    }

    public function getPassportData(\WC_Product $product): PassportData
    {
        $raw = $product->get_meta(self::META_PASSPORT_DATA, true);

        return new PassportData(is_array($raw) ? $raw : []);
    }

    public function renderPassportSummary(): void
    {
        global $product;
        if (! $product instanceof \WC_Product) {
            return;
        }

        $dpp = $this->getPassportData($product);
        if (! $dpp->hasData()) {
            return;
        }

        $this->renderPassportHtml($dpp);
    }

    /**
     * @param array<string, mixed> $tabs
     * @return array<string, mixed>
     */
    public function registerPassportTab(array $tabs): array
    {
        global $product;
        if (! $product instanceof \WC_Product) {
            return $tabs;
        }

        $dpp = $this->getPassportData($product);
        if (! $dpp->hasData()) {
            return $tabs;
        }

        $tabs['pasporto_dpp'] = [
            'title' => __('Digital Product Passport (DPP)', 'pasporto'),
            'priority' => 40,
            'callback' => function () use ($dpp): void {
                $this->renderPassportHtml($dpp);
            },
        ];

        return $tabs;
    }

    public function renderPassportHtml(PassportData $dpp): void
    {
        ?>
        <div class="pasporto-dpp-container" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:6px; padding:16px; margin:16px 0;">
            <h4 style="margin:0 0 12px; padding-bottom:10px; border-bottom:1px solid #e2e8f0; font-size:1.1em; color:#0f172a;">
                <?php echo esc_html__('Digital Product Passport (DPP)', 'pasporto'); ?>
            </h4>

            <div style="display:grid; grid-template-columns: 1fr auto; gap:16px; align-items:start;">
                <table style="width:100%; font-size:0.9em; border-collapse:collapse;">
                    <?php if ($dpp->getUniqueId() !== '') : ?>
                        <tr>
                            <td style="padding:4px 0; color:#64748b; width:45%;"><strong><?php echo esc_html__('Product Identifier (UPI / GTIN):', 'pasporto'); ?></strong></td>
                            <td style="padding:4px 0; color:#0f172a; font-family:monospace;"><?php echo esc_html($dpp->getUniqueId()); ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($dpp->getPrimaryMaterial() !== '') : ?>
                        <tr>
                            <td style="padding:4px 0; color:#64748b;"><strong><?php echo esc_html__('Primary Material:', 'pasporto'); ?></strong></td>
                            <td style="padding:4px 0; color:#0f172a;"><?php echo esc_html($dpp->getPrimaryMaterial()); ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($dpp->getRecycledContentPercent() !== null) : ?>
                        <tr>
                            <td style="padding:4px 0; color:#64748b;"><strong><?php echo esc_html__('Recycled Content:', 'pasporto'); ?></strong></td>
                            <td style="padding:4px 0; color:#0f172a;"><?php echo esc_html((string) $dpp->getRecycledContentPercent()); ?>%</td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($dpp->getCarbonFootprintKg() !== null) : ?>
                        <tr>
                            <td style="padding:4px 0; color:#64748b;"><strong><?php echo esc_html__('Carbon Footprint (GWP):', 'pasporto'); ?></strong></td>
                            <td style="padding:4px 0; color:#0f172a;"><?php echo esc_html((string) $dpp->getCarbonFootprintKg()); ?> kg CO2e</td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($dpp->getCircularityClass() !== '') : ?>
                        <tr>
                            <td style="padding:4px 0; color:#64748b;"><strong><?php echo esc_html__('Circularity class (manufacturer-declared):', 'pasporto'); ?></strong></td>
                            <td style="padding:4px 0; color:#0f172a;"><?php echo esc_html($dpp->getCircularityClass()); ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($dpp->getConformityDocUrl() !== '') : ?>
                        <tr>
                            <td style="padding:4px 0; color:#64748b;"><strong><?php echo esc_html__('Declaration of Conformity:', 'pasporto'); ?></strong></td>
                            <td style="padding:4px 0;">
                                <a href="<?php echo esc_url($dpp->getConformityDocUrl()); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html__('View document', 'pasporto'); ?> &#8599;
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>

                <?php if ($dpp->getPassportUrl() !== '') : ?>
                    <a href="<?php echo esc_url($dpp->getPassportUrl()); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block; padding:10px 14px; background:#fff; border:1px solid #cbd5e1; border-radius:4px; color:#0369a1; font-weight:600; text-decoration:none; white-space:nowrap;">
                        <?php echo esc_html__('Open the product passport', 'pasporto'); ?> &#8599;
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Add the passport data to WooCommerce's own Product node instead of
     * printing a second one. WooCommerce escapes the JSON-LD it prints.
     *
     * @param mixed $markup
     * @param mixed $product
     * @return mixed
     */
    public function extendStructuredData($markup, $product)
    {
        if (! is_array($markup) || ! $product instanceof \WC_Product) {
            return $markup;
        }

        $dpp = $this->getPassportData($product);
        if (! $dpp->hasData()) {
            return $markup;
        }

        if ($dpp->getUniqueId() !== '' && empty($markup['identifier'])) {
            $markup['identifier'] = $dpp->getUniqueId();
        }

        $props = $dpp->additionalProperties();
        if ($props !== []) {
            $existing = isset($markup['additionalProperty']) && is_array($markup['additionalProperty']) ? $markup['additionalProperty'] : [];

            $markup['additionalProperty'] = array_merge($existing, $props);
        }

        return $markup;
    }

    /**
     * A duplicated product is a different product: its identifier and passport
     * link must not be copied from the original.
     *
     * @param mixed $duplicate
     */
    public function clearIdentityOnDuplicate($duplicate): void
    {
        if (! $duplicate instanceof \WC_Product) {
            return;
        }

        $raw = $duplicate->get_meta(self::META_PASSPORT_DATA, true);
        if (! is_array($raw) || $raw === []) {
            return;
        }

        $raw['unique_id']    = '';
        $raw['passport_url'] = '';
        $duplicate->update_meta_data(self::META_PASSPORT_DATA, $raw);
        $duplicate->save_meta_data();
    }
}
