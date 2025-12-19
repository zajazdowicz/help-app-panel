<?php

namespace App\Tests\Unit\Service;

use App\Service\AffiliateLinkGenerator;
use PHPUnit\Framework\TestCase;

class AffiliateLinkGeneratorTest extends TestCase
{
    private AffiliateLinkGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new AffiliateLinkGenerator();
    }

    public function testGenerateForDreamWithAllegro(): void
    {
        $originalUrl = 'https://allegro.pl/item123';
        $partner = 'allegro';
        $trackingId = 'test123';

        $result = $this->generator->generateForDream($originalUrl, $partner, $trackingId);
        $this->assertSame('https://allegro.pl/item123?aff_id=test123', $result);
    }

    public function testGenerateForDreamWithAllegroAndExistingQuery(): void
    {
        $originalUrl = 'https://allegro.pl/item123?color=red';
        $partner = 'allegro';
        $trackingId = 'test456';

        $result = $this->generator->generateForDream($originalUrl, $partner, $trackingId);
        $this->assertSame('https://allegro.pl/item123?color=red&aff_id=test456', $result);
    }

    public function testGenerateForDreamWithCeneo(): void
    {
        $originalUrl = 'https://ceneo.pl/789';
        $partner = 'ceneo';
        $trackingId = 'pid789';

        $result = $this->generator->generateForDream($originalUrl, $partner, $trackingId);
        $this->assertSame('https://ceneo.pl/789?pid=pid789', $result);
    }

    public function testGenerateForDreamWithAmazon(): void
    {
        $originalUrl = 'https://amazon.pl/product';
        $partner = 'amazon';
        $trackingId = 'mytag-20';

        $result = $this->generator->generateForDream($originalUrl, $partner, $trackingId);
        $this->assertSame('https://amazon.pl/product?tag=mytag-20', $result);
    }

    public function testGenerateForDreamWithOther(): void
    {
        $originalUrl = 'https://example.com/item';
        $partner = 'other';
        $trackingId = 'ref123';

        $result = $this->generator->generateForDream($originalUrl, $partner, $trackingId);
        $this->assertSame('https://example.com/item?ref=ref123', $result);
    }

    public function testGenerateForDreamWithoutTrackingId(): void
    {
        $originalUrl = 'https://example.com/item';
        $partner = 'allegro';
        $trackingId = '';

        $result = $this->generator->generateForDream($originalUrl, $partner, $trackingId);
        $this->assertSame($originalUrl, $result);
    }

    public function testGenerateForDreamWithNullPartner(): void
    {
        $originalUrl = 'https://example.com/item';
        $partner = null;
        $trackingId = 'test';

        $result = $this->generator->generateForDream($originalUrl, $partner, $trackingId);
        $this->assertNull($result);
    }

    public function testGenerateForDreamWithEmptyOriginalUrl(): void
    {
        $originalUrl = '';
        $partner = 'allegro';
        $trackingId = 'test';

        $result = $this->generator->generateForDream($originalUrl, $partner, $trackingId);
        $this->assertNull($result);
    }
}
