<?php

declare(strict_types=1);

namespace Pasporto\Model;

defined('ABSPATH') || exit;

/**
 * Data structure and validation for a Digital Product Passport (DPP).
 */
final class PassportData
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private array $data)
    {
    }

    public function getUniqueId(): string
    {
        return trim((string) ($this->data['unique_id'] ?? ''));
    }

    public function getPassportUrl(): string
    {
        return trim((string) ($this->data['passport_url'] ?? ''));
    }

    public function getPrimaryMaterial(): string
    {
        return trim((string) ($this->data['primary_material'] ?? ''));
    }

    public function getRecycledContentPercent(): ?float
    {
        $val = $this->data['recycled_percent'] ?? null;
        if ($val === null || $val === '' || ! is_numeric($val)) {
            return null;
        }

        return min(100.0, max(0.0, (float) $val));
    }

    public function getCarbonFootprintKg(): ?float
    {
        $val = $this->data['carbon_footprint'] ?? null;
        if ($val === null || $val === '' || ! is_numeric($val)) {
            return null;
        }

        return max(0.0, (float) $val);
    }

    public function getCircularityClass(): string
    {
        $val = strtoupper(trim((string) ($this->data['circularity_class'] ?? '')));

        return in_array($val, ['A', 'B', 'C', 'D', 'E'], true) ? $val : '';
    }

    public function getConformityDocUrl(): string
    {
        return trim((string) ($this->data['conformity_doc_url'] ?? ''));
    }

    public function hasData(): bool
    {
        return $this->getUniqueId() !== '' ||
            $this->getPassportUrl() !== '' ||
            $this->getPrimaryMaterial() !== '' ||
            $this->getRecycledContentPercent() !== null ||
            $this->getCarbonFootprintKg() !== null ||
            $this->getCircularityClass() !== '' ||
            $this->getConformityDocUrl() !== '';
    }

    /**
     * Schema.org PropertyValue entries for WooCommerce's Product node.
     *
     * @return list<array<string, string>>
     */
    public function additionalProperties(): array
    {
        $props = [];

        if ($this->getPassportUrl() !== '') {
            $props[] = [
                '@type' => 'PropertyValue',
                'name' => 'digitalProductPassport',
                'url' => $this->getPassportUrl(),
            ];
        }

        if ($this->getPrimaryMaterial() !== '') {
            $props[] = [
                '@type' => 'PropertyValue',
                'name' => 'primaryMaterial',
                'value' => $this->getPrimaryMaterial(),
            ];
        }

        if ($this->getRecycledContentPercent() !== null) {
            $props[] = [
                '@type' => 'PropertyValue',
                'name' => 'recycledContentPercentage',
                'value' => (string) $this->getRecycledContentPercent(),
                'unitText' => '%',
            ];
        }

        if ($this->getCarbonFootprintKg() !== null) {
            $props[] = [
                '@type' => 'PropertyValue',
                'name' => 'carbonFootprint',
                'value' => (string) $this->getCarbonFootprintKg(),
                'unitText' => 'kg CO2e',
            ];
        }

        if ($this->getCircularityClass() !== '') {
            $props[] = [
                '@type' => 'PropertyValue',
                'name' => 'circularityClass',
                'value' => $this->getCircularityClass(),
            ];
        }

        if ($this->getConformityDocUrl() !== '') {
            $props[] = [
                '@type' => 'PropertyValue',
                'name' => 'declarationOfConformity',
                'url' => $this->getConformityDocUrl(),
            ];
        }

        return $props;
    }
}
