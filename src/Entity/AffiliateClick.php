<?php

namespace App\Entity;

use App\Repository\AffiliateClickRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AffiliateClickRepository::class)]
#[ORM\Table(name: 'affiliate_clicks')]
#[ORM\HasLifecycleCallbacks]
class AffiliateClick
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'affiliateClicks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dream $dream = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $sessionId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $clickedAt = null;

    #[ORM\PrePersist]
    public function setTimestamps(): void
    {
        if ($this->clickedAt === null) {
            $this->clickedAt = new \DateTimeImmutable();
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

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): static
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function getClickedAt(): ?\DateTimeImmutable
    {
        return $this->clickedAt;
    }

    public function setClickedAt(\DateTimeImmutable $clickedAt): static
    {
        $this->clickedAt = $clickedAt;
        return $this;
    }
}
