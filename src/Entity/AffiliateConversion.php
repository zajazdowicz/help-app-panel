<?php

namespace App\Entity;

use App\Repository\AffiliateConversionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AffiliateConversionRepository::class)]
#[ORM\Table(name: 'affiliate_conversions')]
#[ORM\HasLifecycleCallbacks]
class AffiliateConversion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'affiliateConversions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dream $dream = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?AffiliateClick $click = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $convertedAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $orderId = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $amount = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $commission = null;

    #[ORM\Column]
    private int $quantity = 1;

    #[ORM\PrePersist]
    public function setTimestamps(): void
    {
        if ($this->convertedAt === null) {
            $this->convertedAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDream(): ?Dream
    {
        return $this->dream;
    }

    public function setDream(?Dream $dream): static
    {
        $this->dream = $dream;
        return $this;
    }

    public function getClick(): ?AffiliateClick
    {
        return $this->click;
    }

    public function setClick(?AffiliateClick $click): static
    {
        $this->click = $click;
        return $this;
    }

    public function getConvertedAt(): ?\DateTimeImmutable
    {
        return $this->convertedAt;
    }

    public function setConvertedAt(\DateTimeImmutable $convertedAt): static
    {
        $this->convertedAt = $convertedAt;
        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): static
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCommission(): ?string
    {
        return $this->commission;
    }

    public function setCommission(?string $commission): static
    {
        $this->commission = $commission;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }
}
