<?php

namespace App\Service;

class AffiliateLinkGenerator
{
    public function generateForDream(string $originalUrl, ?string $partner, ?string $trackingId): ?string
    {
        if (empty($originalUrl) || empty($partner)) {
            return null;
        }
        
        $trackingId = trim($trackingId ?? '');
        
        switch ($partner) {
            case 'allegro':
                if ($trackingId !== '') {
                    // Format linku Allegro Partners
                    return $originalUrl . (str_contains($originalUrl, '?') ? '&' : '?') . 'aff_id=' . urlencode($trackingId);
                }
                break;
            case 'ceneo':
                if ($trackingId !== '') {
                    // Przykładowy format CeneoLab
                    return $originalUrl . (str_contains($originalUrl, '?') ? '&' : '?') . 'pid=' . urlencode($trackingId);
                }
                break;
            case 'amazon':
                if ($trackingId !== '') {
                    // Amazon Associates
                    return $originalUrl . (str_contains($originalUrl, '?') ? '&' : '?') . 'tag=' . urlencode($trackingId);
                }
                break;
            case 'other':
                if ($trackingId !== '') {
                    // Dla innych partnerów dodajemy parametr ref
                    return $originalUrl . (str_contains($originalUrl, '?') ? '&' : '?') . 'ref=' . urlencode($trackingId);
                }
                break;
        }
        
        // Jeśli brak trackingId, zwróć oryginalny URL
        return $originalUrl;
    }
}
