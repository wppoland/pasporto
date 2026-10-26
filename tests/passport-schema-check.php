<?php
/**
 * Self-check for Pasporto Digital Product Passport schema and data model.
 * Run: php tests/passport-schema-check.php
 *
 * @package Pasporto
 */

declare(strict_types=1);

define('ABSPATH', __DIR__);
define('PASPORTO_DIR', __DIR__ . '/../');

require_once __DIR__ . '/../autoload.php';

// Minimal stubs for WordPress and WooCommerce functions used in testing.
function __(string $text, string $domain = 'default'): string
{
    return $text;
}

function esc_html__(string $text, string $domain = 'default'): string
{
    return $text;
}

function add_action(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void
{
}

function add_filter(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void
{
}

$GLOBALS['options'] = [];

function get_option(string $option, $default = false)
{
    return $GLOBALS['options'][$option] ?? $default;
}

// Test Stub for WC_Product
class WC_Product
{
    /**
     * @var array<string, mixed>
     */
    private array $meta = [];

    public function __construct(private int $id, private int $parentId = 0)
    {
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_parent_id(): int
    {
        return $this->parentId;
    }

    public function get_meta(string $key, bool $single = true)
    {
        return $this->meta[$key] ?? '';
    }

    public function set_meta(string $key, $value): void
    {
        $this->meta[$key] = $value;
    }
}

$GLOBALS['products'] = [];

function wc_get_product(int $id)
{
    return $GLOBALS['products'][$id] ?? null;
}

// ------------------- TEST SUITE -------------------
echo "Running Pasporto DPP Tests...\n";

// 1. Test PassportData Model
$raw = [
    'unique_id' => 'urn:gtin:04012345678901',
    'passport_url' => 'https://dpp.example.org/p/04012345678901',
    'primary_material' => '100% GOTS Organic Cotton',
    'recycled_percent' => 35.5,
    'carbon_footprint' => 4.25,
    'circularity_class' => 'A',
    'conformity_doc_url' => 'https://example.org/doc.pdf',
];

$dpp = new \Pasporto\Model\PassportData($raw);

assert($dpp->hasData() === true, 'PassportData has data');
assert($dpp->getUniqueId() === 'urn:gtin:04012345678901', 'Unique ID matches');
assert($dpp->getPrimaryMaterial() === '100% GOTS Organic Cotton', 'Primary material matches');
assert($dpp->getRecycledContentPercent() === 35.5, 'Recycled % matches');
assert($dpp->getCarbonFootprintKg() === 4.25, 'Carbon footprint matches');
assert($dpp->getCircularityClass() === 'A', 'Circularity class matches');

echo "  ok  PassportData model methods and getters verified\n";

// 2. Test JSON-LD Schema Generation
$json = $dpp->toJsonLd();
assert($json['@context'] === 'https://schema.org', 'Context is schema.org');
assert($json['@type'] === 'Product', 'Type is Product');
assert($json['identifier'] === 'urn:gtin:04012345678901', 'Identifier matches');
assert($json['sameAs'] === 'https://dpp.example.org/p/04012345678901', 'sameAs matches passport URL');

$props = $json['additionalProperty'];
assert(count($props) === 4, '4 additional properties emitted');
assert($props[0]['name'] === 'primaryMaterial' && $props[0]['value'] === '100% GOTS Organic Cotton', 'Material property correct');
assert($props[1]['name'] === 'recycledContentPercentage' && $props[1]['value'] === '35.5%', 'Recycled percentage property correct');
assert($props[2]['name'] === 'carbonFootprintKgCO2e' && $props[2]['value'] === '4.25', 'Carbon footprint property correct');
assert($props[3]['name'] === 'circularityClass' && $props[3]['value'] === 'A', 'Circularity property correct');

echo "  ok  JSON-LD Schema generation for EU ESPR verified\n";

// 3. Test Variation Parent Inheritance
$GLOBALS['options'][\Pasporto\Service\PassportService::OPTION] = ['enabled' => '1'];
$service = new \Pasporto\Service\PassportService();

$parent = new WC_Product(100);
$parent->set_meta(\Pasporto\Service\PassportService::META_PASSPORT_DATA, $raw);
$GLOBALS['products'][100] = $parent;

$variation = new WC_Product(101, 100);
$GLOBALS['products'][101] = $variation;

$varDpp = $service->getPassportData($variation);
assert($varDpp->hasData() === true, 'Child variation inherits parent passport data');
assert($varDpp->getUniqueId() === 'urn:gtin:04012345678901', 'Variation inherited parent unique ID');
assert($varDpp->getPrimaryMaterial() === '100% GOTS Organic Cotton', 'Variation inherited parent material');

echo "  ok  Variation parent passport inheritance verified\n";

echo "\nALL PASPORTO CHECKS PASSED!\n";
exit(0);
