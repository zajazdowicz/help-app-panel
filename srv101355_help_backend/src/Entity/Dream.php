<?php

namespace App\Entity;

use App\Repository\DreamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DreamRepository::class)]
#[ORM\Table(name: 'dreams')]
#[ORM\HasLifecycleCallbacks]
class Dream
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_CHOICES = [
        'Oczekujące' => self::STATUS_PENDING,
        'Zweryfikowane' => self::STATUS_VERIFIED,
        'W realizacji' => self::STATUS_IN_PROGRESS,
        'Zrealizowane' => self::STATUS_FULFILLED,
        'Anulowane' => self::STATUS_CANCELLED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dreams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    #[ORM\ManyToOne(inversedBy: 'dreams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Orphanage $orphanage = null;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 500)]
    private ?string $productUrl = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $productTitle = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?string $productPrice = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $productCategory = null;

    #[ORM\Column(type: Types::TEXT, length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column]
    #[Assert\Positive]
    private int $quantityNeeded = 1;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $quantityFulfilled = 0;

    #[ORM\Column]
    private bool $isUrgent = false;

    #[ORM\OneToMany(mappedBy: 'dream', targetEntity: DreamFulfillment::class, orphanRemoval: true)]
    private Collection $fulfillments;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->fulfillments = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setTimestamps(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): static
    {
        $this->child = $child;

        return $this;
    }

    public function getOrphanage(): ?Orphanage
    {
        return $this->orphanage;
    }

    public function setOrphanage(?Orphanage $orphanage): static
    {
        $this->orphanage = $orphanage;

        return $this;
    }

    public function getProductUrl(): ?string
    {
        return $this->productUrl;
    }

    public function setProductUrl(string $productUrl): static
    {
        $this->productUrl = $productUrl;

        return $this;
    }

    public function getProductTitle(): ?string
    {
        return $this->productTitle;
    }

    public function setProductTitle(string $productTitle): static
    {
        $this->productTitle = $productTitle;

        return $this;
    }

    public function getProductPrice(): ?string
    {
        return $this->productPrice;
    }

    public function setProductPrice(string $productPrice): static
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getProductCategory(): ?string
    {
        return $this->productCategory;
    }

    public function setProductCategory(string $productCategory): static
    {
        $this->productCategory = $productCategory;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getQuantityNeeded(): int
    {
        return $this->quantityNeeded;
    }

    public function setQuantityNeeded(int $quantityNeeded): static
    {
        $this->quantityNeeded = $quantityNeeded;

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

    public function isUrgent(): bool
    {
        return $this->isUrgent;
    }

    public function setIsUrgent(bool $isUrgent): static
    {
        $this->isUrgent = $isUrgent;

        return $this;
    }

    /**
     * @return Collection<int, DreamFulfillment>
     */
    public function getFulfillments(): Collection
    {
        return $this->fulfillments;
    }

    public function addFulfillment(DreamFulfillment $fulfillment): static
    {
        if (!$this->fulfillments->contains($fulfillment)) {
            $this->fulfillments->add($fulfillment);
            $fulfillment->setDream($this);
        }

        return $this;
    }

    public function removeFulfillment(DreamFulfillment $fulfillment): static
    {
        if ($this->fulfillments->removeElement($fulfillment)) {
            // set the owning side to null (unless already changed)
            if ($fulfillment->getDream() === $this) {
                $fulfillment->setDream(null);
            }
        }

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getRemainingQuantity(): int
    {
        return max(0, $this->quantityNeeded - $this->quantityFulfilled);
    }

    public function isFullyFulfilled(): bool
    {
        return $this->quantityFulfilled >= $this->quantityNeeded;
    }

    public function __toString(): string
    {
        return $this->productTitle ?? 'Dream #' . $this->id;
    }
}
