<?php

namespace App\Entity;

use App\Repository\DreamFulfillmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DreamFulfillmentRepository::class)]
#[ORM\Table(name: 'dream_fulfillments')]
#[ORM\HasLifecycleCallbacks]
class DreamFulfillment
{
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_ORDERED = 'ordered';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_CHOICES = [
        'Zarezerwowane' => self::STATUS_RESERVED,
        'Zamówione' => self::STATUS_ORDERED,
        'Dostarczone' => self::STATUS_DELIVERED,
        'Potwierdzone' => self::STATUS_CONFIRMED,
        'Anulowane' => self::STATUS_CANCELLED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fulfillments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dream $dream = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $donorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private ?string $donorEmail = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $donorNickname = null;

    #[ORM\Column]
    private bool $isAnonymous = false;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_RESERVED;

    #[ORM\Column]
    #[Assert\Positive]
    private int $quantityFulfilled = 1;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $childPhotoUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $childMessage = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\PrePersist]
    public function setTimestamps(): void
    {
        $this->createdAt = new \DateTime();
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

    public function getDonorName(): ?string
    {
        return $this->donorName;
    }

    public function setDonorName(?string $donorName): static
    {
        $this->donorName = $donorName;

        return $this;
    }

    public function getDonorEmail(): ?string
    {
        return $this->donorEmail;
    }

    public function setDonorEmail(?string $donorEmail): static
    {
        $this->donorEmail = $donorEmail;

        return $this;
    }

    public function getDonorNickname(): ?string
    {
        return $this->donorNickname;
    }

    public function setDonorNickname(?string $donorNickname): static
    {
        $this->donorNickname = $donorNickname;

        return $this;
    }

    public function isAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): static
    {
        $this->isAnonymous = $isAnonymous;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, array_values(self::STATUS_CHOICES))) {
            throw new \InvalidArgumentException(sprintf('Invalid status "%s"', $status));
        }
        $this->status = $status;

        return $this;
    }

    public function getQuantityFulfilled(): int
    {
        return $this->quantityFulfilled;
    }

    public function setQuantityFulfilled(int $quantityFulfilled): static
    {
        $this->quantityFulfilled = $quantityFulfilled;

        return $this;
    }

    public function getChildPhotoUrl(): ?string
    {
        return $this->childPhotoUrl;
    }

    public function setChildPhotoUrl(?string $childPhotoUrl): static
    {
        $this->childPhotoUrl = $childPhotoUrl;

        return $this;
    }

    public function getChildMessage(): ?string
    {
        return $this->childMessage;
    }

    public function setChildMessage(?string $childMessage): static
    {
        $this->childMessage = $childMessage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function __toString(): string
    {
        return 'Fulfillment #' . $this->id;
    }
}
