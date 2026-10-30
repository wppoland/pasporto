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

// 2. Test structured data properties
$props = $dpp->additionalProperties();
assert(count($props) === 6, '6 additional properties emitted');
assert($props[0]['name'] === 'digitalProductPassport' && $props[0]['url'] === 'https://dpp.example.org/p/04012345678901', 'Passport property correct');
assert($props[1]['name'] === 'primaryMaterial' && $props[1]['value'] === '100% GOTS Organic Cotton', 'Material property correct');
assert($props[2]['name'] === 'recycledContentPercentage' && $props[2]['value'] === '35.5' && $props[2]['unitText'] === '%', 'Recycled percentage property correct');
assert($props[3]['name'] === 'carbonFootprint' && $props[3]['value'] === '4.25', 'Carbon footprint property correct');
assert($props[4]['name'] === 'circularityClass' && $props[4]['value'] === 'A', 'Circularity property correct');
assert($props[5]['name'] === 'declarationOfConformity' && $props[5]['url'] === 'https://example.org/doc.pdf', 'Conformity property correct');

echo "  ok  Structured data properties verified\n";

echo "\nALL PASPORTO CHECKS PASSED!\n";
exit(0);
