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

    public function getOperatorId(): string
    {
        return trim((string) ($this->data['operator_id'] ?? ''));
    }

    public function getFacilityId(): string
    {
        return trim((string) ($this->data['facility_id'] ?? ''));
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

    public function getRepairGuideUrl(): string
    {
        return trim((string) ($this->data['repair_guide_url'] ?? ''));
    }

    public function hasData(): bool
    {
        return $this->getUniqueId() !== '' ||
            $this->getPassportUrl() !== '' ||
            $this->getPrimaryMaterial() !== '' ||
            $this->getRecycledContentPercent() !== null ||
            $this->getCarbonFootprintKg() !== null ||
            $this->getCircularityClass() !== '';
    }

    /**
     * Build JSON-LD structured representation for DPP.
     *
     * @return array<string, mixed>
     */
    public function toJsonLd(): array
    {
        $json = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
        ];

        if ($this->getUniqueId() !== '') {
            $json['identifier'] = $this->getUniqueId();
        }

        if ($this->getPassportUrl() !== '') {
            $json['sameAs'] = $this->getPassportUrl();
        }

        $additionalProps = [];

        if ($this->getPrimaryMaterial() !== '') {
            $additionalProps[] = [
                '@type' => 'PropertyValue',
                'name' => 'primaryMaterial',
                'value' => $this->getPrimaryMaterial(),
            ];
        }

        if ($this->getRecycledContentPercent() !== null) {
            $additionalProps[] = [
                '@type' => 'PropertyValue',
                'name' => 'recycledContentPercentage',
                'value' => (string) $this->getRecycledContentPercent() . '%',
            ];
        }

        if ($this->getCarbonFootprintKg() !== null) {
            $additionalProps[] = [
                '@type' => 'PropertyValue',
                'name' => 'carbonFootprintKgCO2e',
                'value' => (string) $this->getCarbonFootprintKg(),
            ];
        }

        if ($this->getCircularityClass() !== '') {
            $additionalProps[] = [
                '@type' => 'PropertyValue',
                'name' => 'circularityClass',
                'value' => $this->getCircularityClass(),
            ];
        }

        if (! empty($additionalProps)) {
            $json['additionalProperty'] = $additionalProps;
        }

        return $json;
    }
}
