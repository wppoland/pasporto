<?php

declare(strict_types=1);

namespace Pasporto\Admin;

use Pasporto\Contract\HasHooks;
use Pasporto\Service\PassportService;

defined('ABSPATH') || exit;

/**
 * Product edit screen fields for Digital Product Passport (DPP).
 */
final class ProductMetaBox implements HasHooks
{
    public function __construct(private PassportService $passportService)
    {
    }

    public function registerHooks(): void
    {
        if (! $this->passportService->isEnabled()) {
            return;
        }

        add_action('woocommerce_product_options_general_product_data', [$this, 'renderProductFields']);
        add_action('woocommerce_process_product_meta', [$this, 'saveProductFields']);
    }

    public function renderProductFields(): void
    {
        global $post;
        $product = wc_get_product($post->ID);
        $dpp     = $product instanceof \WC_Product ? $this->passportService->getPassportData($product) : null;

        wp_nonce_field('pasporto_save_product_fields', 'pasporto_product_nonce');

        echo '<div class="options_group pasporto-options-group">';
        echo '<h4 style="padding-left:12px;">' . esc_html__('Digital Product Passport (ESPR Regulation (EU) 2024/1781)', 'pasporto') . '</h4>';

        woocommerce_wp_text_input([
            'id' => '_pasporto_unique_id',
            'name' => '_pasporto_dpp[unique_id]',
            'value' => $dpp ? $dpp->getUniqueId() : '',
            'label' => __('Unique Product ID (UPI / GTIN)', 'pasporto'),
            'description' => __('GS1 Digital Link, GTIN, EPC, or unique product passport identifier.', 'pasporto'),
            'desc_tip' => true,
        ]);

        woocommerce_wp_text_input([
            'id' => '_pasporto_passport_url',
            'name' => '_pasporto_dpp[passport_url]',
            'value' => $dpp ? $dpp->getPassportUrl() : '',
            'label' => __('Passport URL / Data Carrier', 'pasporto'),
            'description' => __('Direct web link to the official Digital Product Passport or verification portal.', 'pasporto'),
            'desc_tip' => true,
        ]);

        woocommerce_wp_text_input([
            'id' => '_pasporto_primary_material',
            'name' => '_pasporto_dpp[primary_material]',
            'value' => $dpp ? $dpp->getPrimaryMaterial() : '',
            'label' => __('Primary Material', 'pasporto'),
            'description' => __('Main material composition (e.g. Organic Cotton, Recycled Aluminum, LiFePO4).', 'pasporto'),
            'desc_tip' => true,
        ]);

        woocommerce_wp_text_input([
            'id' => '_pasporto_recycled_percent',
            'name' => '_pasporto_dpp[recycled_percent]',
            'value' => $dpp && $dpp->getRecycledContentPercent() !== null ? (string) $dpp->getRecycledContentPercent() : '',
            'label' => __('Recycled Content (%)', 'pasporto'),
            'description' => __('Percentage of certified recycled content in the product (0 - 100%).', 'pasporto'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => ['min' => '0', 'max' => '100', 'step' => '0.1'],
        ]);

        woocommerce_wp_text_input([
            'id' => '_pasporto_carbon_footprint',
            'name' => '_pasporto_dpp[carbon_footprint]',
            'value' => $dpp && $dpp->getCarbonFootprintKg() !== null ? (string) $dpp->getCarbonFootprintKg() : '',
            'label' => __('Carbon Footprint (kg CO2e)', 'pasporto'),
            'description' => __('Life-cycle Global Warming Potential (GWP) per unit.', 'pasporto'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => ['min' => '0', 'step' => '0.01'],
        ]);

        woocommerce_wp_select([
            'id' => '_pasporto_circularity_class',
            'name' => '_pasporto_dpp[circularity_class]',
            'value' => $dpp ? $dpp->getCircularityClass() : '',
            'label' => __('Circularity Class', 'pasporto'),
            'description' => __('Circularity or recyclability score class from A (highest) to E.', 'pasporto'),
            'desc_tip' => true,
            'options' => [
                '' => __('Not specified', 'pasporto'),
                'A' => __('Class A (Excellent circularity)', 'pasporto'),
                'B' => __('Class B (Good)', 'pasporto'),
                'C' => __('Class C (Moderate)', 'pasporto'),
                'D' => __('Class D (Low)', 'pasporto'),
                'E' => __('Class E (Poor)', 'pasporto'),
            ],
        ]);

        woocommerce_wp_text_input([
            'id' => '_pasporto_conformity_doc_url',
            'name' => '_pasporto_dpp[conformity_doc_url]',
            'value' => $dpp ? $dpp->getConformityDocUrl() : '',
            'label' => __('Declaration of Conformity (URL)', 'pasporto'),
            'description' => __('Direct link to the EU Declaration of Conformity or CE test report.', 'pasporto'),
            'desc_tip' => true,
        ]);

        echo '</div>';
    }

    public function saveProductFields(int $productId): void
    {
        if (
            ! isset($_POST['pasporto_product_nonce']) ||
            ! wp_verify_nonce(sanitize_key(wp_unslash($_POST['pasporto_product_nonce'])), 'pasporto_save_product_fields')
        ) {
            return;
        }

        if (! current_user_can('edit_post', $productId)) {
            return;
        }

        $product = wc_get_product($productId);
        if (! $product instanceof \WC_Product) {
            return;
        }

        $input = isset($_POST['_pasporto_dpp']) && is_array($_POST['_pasporto_dpp'])
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized individually below
            ? (array) wp_unslash($_POST['_pasporto_dpp'])
            : [];

        $sanitized = [
            'unique_id' => isset($input['unique_id']) ? sanitize_text_field((string) $input['unique_id']) : '',
            'passport_url' => isset($input['passport_url']) ? esc_url_raw((string) $input['passport_url']) : '',
            'primary_material' => isset($input['primary_material']) ? sanitize_text_field((string) $input['primary_material']) : '',
            'recycled_percent' => isset($input['recycled_percent']) && '' !== $input['recycled_percent'] ? min(100.0, max(0.0, (float) $input['recycled_percent'])) : '',
            'carbon_footprint' => isset($input['carbon_footprint']) && '' !== $input['carbon_footprint'] ? max(0.0, (float) $input['carbon_footprint']) : '',
            'circularity_class' => isset($input['circularity_class']) && in_array($input['circularity_class'], ['A', 'B', 'C', 'D', 'E'], true) ? $input['circularity_class'] : '',
            'conformity_doc_url' => isset($input['conformity_doc_url']) ? esc_url_raw((string) $input['conformity_doc_url']) : '',
        ];

        $product->update_meta_data(PassportService::META_PASSPORT_DATA, $sanitized);
        $product->save_meta_data();
    }
}
