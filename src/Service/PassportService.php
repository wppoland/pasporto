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
     * @var array<string, mixed>
     */
    private array $settings;

    public function __construct()
    {
        $raw            = get_option(self::OPTION, []);
        $this->settings = is_array($raw) ? $raw : [];
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

        add_action('wp_footer', [$this, 'renderJsonLdSchema']);
    }

    public function getPassportData(\WC_Product $product): PassportData
    {
        $raw = $product->get_meta(self::META_PASSPORT_DATA, true);

        // Fall back to parent if variation has no specific DPP data
        if ((! is_array($raw) || empty($raw)) && $product->get_parent_id() > 0) {
            $parent = wc_get_product($product->get_parent_id());
            if ($parent instanceof \WC_Product) {
                $raw = $parent->get_meta(self::META_PASSPORT_DATA, true);
            }
        }

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
            <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #e2e8f0; padding-bottom:10px; margin-bottom:12px;">
                <h4 style="margin:0; font-size:1.1em; color:#0f172a;">
                    &#128220; <?php echo esc_html__('Digital Product Passport (EU DPP)', 'pasporto'); ?>
                </h4>
                <span style="font-size:0.8em; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:4px; font-weight:600;">
                    <?php echo esc_html__('Regulation (EU) 2024/1781', 'pasporto'); ?>
                </span>
            </div>

            <div style="display:grid; grid-template-columns: 1fr auto; gap:16px;">
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
                            <td style="padding:4px 0; color:#0f172a; font-weight:600;"><?php echo esc_html((string) $dpp->getRecycledContentPercent()); ?>%</td>
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
                            <td style="padding:4px 0; color:#64748b;"><strong><?php echo esc_html__('Circularity Class:', 'pasporto'); ?></strong></td>
                            <td style="padding:4px 0;">
                                <span style="display:inline-block; padding:1px 6px; background:#16a34a; color:#fff; border-radius:3px; font-weight:bold;">
                                    <?php echo esc_html($dpp->getCircularityClass()); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($dpp->getConformityDocUrl() !== '') : ?>
                        <tr>
                            <td style="padding:4px 0; color:#64748b;"><strong><?php echo esc_html__('Declaration of Conformity:', 'pasporto'); ?></strong></td>
                            <td style="padding:4px 0;">
                                <a href="<?php echo esc_url($dpp->getConformityDocUrl()); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html__('View Document (PDF)', 'pasporto'); ?> &#8599;
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>

                <?php if ($dpp->getPassportUrl() !== '') : ?>
                    <div style="text-align:center; padding:10px; background:#fff; border:1px solid #e2e8f0; border-radius:4px;">
                        <a href="<?php echo esc_url($dpp->getPassportUrl()); ?>" target="_blank" rel="noopener noreferrer" style="display:block; text-decoration:none;">
                            <div style="font-size:2em; line-height:1;">&#128241;</div>
                            <small style="color:#0284c7; font-weight:600;"><?php echo esc_html__('Scan / Open DPP', 'pasporto'); ?></small>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function renderJsonLdSchema(): void
    {
        if (! is_product()) {
            return;
        }

        global $product;
        if (! $product instanceof \WC_Product) {
            return;
        }

        $dpp = $this->getPassportData($product);
        if (! $dpp->hasData()) {
            return;
        }

        $json = $dpp->toJsonLd();
        echo '<script type="application/ld+json">' . wp_json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
}
