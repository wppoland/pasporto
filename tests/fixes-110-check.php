<?php
/**
 * Self-check for the 1.1.0 fixes. Each section reports on its own, so a run
 * against older code shows which fixes are missing.
 * Run: php tests/fixes-110-check.php
 *
 * @package Pasporto
 */

declare(strict_types=1);

define('ABSPATH', __DIR__);
define('PASPORTO_DIR', __DIR__ . '/../');

require_once __DIR__ . '/../autoload.php';

$GLOBALS['options'] = [];
$GLOBALS['hooks']   = [];

function __(string $text, string $domain = 'default'): string
{
    return $text;
}
function esc_html__(string $text, string $domain = 'default'): string
{
    return $text;
}
function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES);
}
function esc_url(string $url): string
{
    return htmlspecialchars($url, ENT_QUOTES);
}
function add_action(string $tag, mixed $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['hooks'][] = $tag;
}
function add_filter(string $tag, mixed $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['hooks'][] = $tag;
}
function get_option(string $option, mixed $default = false): mixed
{
    return $GLOBALS['options'][$option] ?? $default;
}
function wc_get_product(int $id): mixed
{
    return null;
}

class WC_Product
{
    public int $saves = 0;

    /** @param array<string, mixed> $meta */
    public function __construct(public array $meta = [])
    {
    }
    public function get_parent_id(): int
    {
        return 0;
    }
    public function get_meta(string $key, bool $single = true): mixed
    {
        return $this->meta[$key] ?? '';
    }
    public function update_meta_data(string $key, mixed $value): void
    {
        $this->meta[$key] = $value;
    }
    public function save_meta_data(): void
    {
        $this->saves++;
    }
}

use Pasporto\Admin\ProductMetaBox;
use Pasporto\Admin\SettingsPage;
use Pasporto\Model\PassportData;
use Pasporto\Service\PassportService;

$failed = 0;
/** @param callable(): (string|true) $fn */
function check(string $label, callable $fn): void
{
    global $failed;
    try {
        $r = $fn();
    } catch (\Throwable $e) {
        $r = get_class($e) . ': ' . $e->getMessage();
    }
    if ($r === true) {
        echo "  ok    {$label}\n";
        return;
    }
    $failed++;
    echo "  FAIL  {$label}: {$r}\n";
}

$full = [
    'unique_id' => 'urn:gtin:04012345678901',
    'passport_url' => 'https://dpp.example.org/p/04012345678901',
    'primary_material' => 'Organic cotton',
    'recycled_percent' => 35.5,
    'carbon_footprint' => 4.25,
    'circularity_class' => 'B',
    'conformity_doc_url' => 'https://example.org/doc',
];

check('fresh install (nothing saved) is enabled and hooks the storefront', function (): string|bool {
    $GLOBALS['options'] = [];
    $GLOBALS['hooks']   = [];
    $s = new PassportService();
    if (! $s->isEnabled()) {
        return 'isEnabled() is false with no saved option';
    }
    $s->registerHooks();
    return in_array('woocommerce_product_tabs', $GLOBALS['hooks'], true) ? true : 'no product tab hook';
});

check('a saved enabled=0 still switches display off', function (): string|bool {
    $GLOBALS['options'] = [PassportService::OPTION => ['enabled' => '0', 'dpp_position' => 'tab']];
    $on = (new PassportService())->isEnabled();
    $GLOBALS['options'] = [];
    return $on ? 'still enabled' : true;
});

check('no second Product node printed in the footer', function (): string|bool {
    $GLOBALS['hooks'] = [];
    (new PassportService())->registerHooks();
    if (in_array('wp_footer', $GLOBALS['hooks'], true)) {
        return 'wp_footer JSON-LD printer still hooked';
    }
    return in_array('woocommerce_structured_data_product', $GLOBALS['hooks'], true) ? true : 'WooCommerce structured data not extended';
});

check('passport data is merged into WooCommerce\'s own Product node', function () use ($full): string|bool {
    $s = new PassportService();
    if (! method_exists($s, 'extendStructuredData')) {
        return 'extendStructuredData() missing';
    }
    $product = new WC_Product([PassportService::META_PASSPORT_DATA => $full]);
    $wc      = ['@type' => 'Product', '@id' => 'https://shop.test/p/#product', 'name' => 'Shirt', 'offers' => [['price' => '10']]];
    $out     = $s->extendStructuredData($wc, $product);
    if (($out['name'] ?? null) !== 'Shirt' || ! isset($out['offers'], $out['@id'])) {
        return 'name/offers/@id lost';
    }
    if (isset($out['sameAs'])) {
        return 'passport URL still sent as sameAs';
    }
    $names = array_column($out['additionalProperty'] ?? [], 'name');
    if (! in_array('digitalProductPassport', $names, true) || ! in_array('declarationOfConformity', $names, true)) {
        return 'passport or conformity property missing: ' . implode(',', $names);
    }
    return ($out['identifier'] ?? '') === $full['unique_id'] ? true : 'identifier missing';
});

check('a duplicated product loses the original\'s identifier and passport link', function () use ($full): string|bool {
    $GLOBALS['hooks'] = [];
    $s = new PassportService();
    (new ProductMetaBox($s))->registerHooks();
    if (! in_array('woocommerce_product_duplicate', $GLOBALS['hooks'], true)) {
        return 'woocommerce_product_duplicate not hooked';
    }
    $copy = new WC_Product([PassportService::META_PASSPORT_DATA => $full]);
    $s->clearIdentityOnDuplicate($copy);
    $meta = $copy->meta[PassportService::META_PASSPORT_DATA];
    if ($meta['unique_id'] !== '' || $meta['passport_url'] !== '' || $copy->saves !== 1) {
        return 'identity copied';
    }
    return $meta['primary_material'] === 'Organic cotton' ? true : 'other data lost';
});

check('a product with only a Declaration of Conformity link has passport data', function (): string|bool {
    return (new PassportData(['conformity_doc_url' => 'https://example.org/doc']))->hasData() ? true : 'hasData() false';
});

check('dead getters with no input field are gone', function (): string|bool {
    foreach (['getOperatorId', 'getFacilityId', 'getRepairGuideUrl'] as $m) {
        if (method_exists(PassportData::class, $m)) {
            return "{$m} still defined";
        }
    }
    return true;
});

check('rendered box makes no PDF, scan or regulation-badge claim', function () use ($full): string|bool {
    ob_start();
    (new PassportService())->renderPassportHtml(new PassportData($full));
    $html = (string) ob_get_clean();
    foreach (['(PDF)', 'Scan', 'Regulation (EU) 2024/1781', '&#128241;'] as $needle) {
        if (str_contains($html, $needle)) {
            return "output contains {$needle}";
        }
    }
    return str_contains($html, 'https://example.org/doc') ? true : 'conformity link missing';
});

check('settings sanitizer accepts a non-array value', function (): string|bool {
    $out = (new SettingsPage())->sanitizeSettings('garbage');
    return ($out['enabled'] ?? null) === '0' ? true : 'unexpected result';
});

check('product fields stay editable while display is off', function (): string|bool {
    $GLOBALS['options'] = [PassportService::OPTION => ['enabled' => '0']];
    $GLOBALS['hooks']   = [];
    (new ProductMetaBox(new PassportService()))->registerHooks();
    $GLOBALS['options'] = [];
    return in_array('woocommerce_product_options_general_product_data', $GLOBALS['hooks'], true) ? true : 'fields hidden';
});

if ($failed > 0) {
    echo "\n{$failed} check(s) failed\n";
    exit(1);
}
echo "\nAll 1.1.0 checks passed\n";
