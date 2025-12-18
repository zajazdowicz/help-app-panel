<?php

namespace App\Entity;

use App\Repository\DreamRepository;
use App\Enum\DreamStatus;
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column(type: Types::TEXT, length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $description = null;

    #[ORM\Column(length: 20, enumType: DreamStatus::class)]
    private ?DreamStatus $status = null;

    #[ORM\Column]
    #[Assert\Positive]
    private int $quantityNeeded = 1;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $affiliatePartner = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $affiliateTrackingId = null;

    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $originalProductUrl = null;

    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $affiliateUrl = null;

    #[ORM\Column]
    private int $purchasedQuantity = 0;

    #[ORM\Column]
    private bool $isUrgent = false;

    #[ORM\OneToMany(mappedBy: 'dream', targetEntity: AffiliateClick::class, orphanRemoval: true)]
    private Collection $affiliateClicks;

    #[ORM\OneToMany(mappedBy: 'dream', targetEntity: AffiliateClick::class, orphanRemoval: true)]
    private Collection $affiliateClicks;

    #[ORM\OneToMany(mappedBy: 'dream', targetEntity: AffiliateConversion::class, orphanRemoval: true)]
    private Collection $affiliateConversions;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->fulfillments = new ArrayCollection();
        $this->affiliateClicks = new ArrayCollection();
        $this->affiliateConversions = new ArrayCollection();
        $this->status = DreamStatus::PENDING;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

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

    public function getStatus(): ?DreamStatus
    {
        return $this->status;
    }

    public function setStatus(DreamStatus|string $status): static
    {
        if (is_string($status)) {
            $status = DreamStatus::tryFrom($status);
            if ($status === null) {
                throw new \InvalidArgumentException(sprintf('Invalid status "%s"', $status));
            }
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


    public function isUrgent(): bool
    {
        return $this->isUrgent;
    }

    public function setIsUrgent(bool $isUrgent): static
    {
        $this->isUrgent = $isUrgent;

        return $this;
    }

    public function getAffiliatePartner(): ?string
    {
        return $this->affiliatePartner;
    }

    public function setAffiliatePartner(?string $affiliatePartner): static
    {
        $this->affiliatePartner = $affiliatePartner;
        return $this;
    }

    public function getAffiliateTrackingId(): ?string
    {
        return $this->affiliateTrackingId;
    }

    public function setAffiliateTrackingId(?string $affiliateTrackingId): static
    {
        $this->affiliateTrackingId = $affiliateTrackingId;
        return $this;
    }

    public function getOriginalProductUrl(): ?string
    {
        return $this->originalProductUrl;
    }

    public function setOriginalProductUrl(?string $originalProductUrl): static
    {
        $this->originalProductUrl = $originalProductUrl;
        return $this;
    }

    public function getAffiliateUrl(): ?string
    {
        return $this->affiliateUrl;
    }

    public function setAffiliateUrl(?string $affiliateUrl): static
    {
        $this->affiliateUrl = $affiliateUrl;
        return $this;
    }

    public function getPurchasedQuantity(): int
    {
        return $this->purchasedQuantity;
    }

    public function setPurchasedQuantity(int $purchasedQuantity): static
    {
        $this->purchasedQuantity = $purchasedQuantity;
        return $this;
    }

    /**
     * @return Collection<int, AffiliateClick>
     */
    public function getAffiliateClicks(): Collection
    {
        return $this->affiliateClicks;
    }

    public function addAffiliateClick(AffiliateClick $affiliateClick): static
    {
        if (!$this->affiliateClicks->contains($affiliateClick)) {
            $this->affiliateClicks->add($affiliateClick);
            $affiliateClick->setDream($this);
        }

        return $this;
    }

    public function removeAffiliateClick(AffiliateClick $affiliateClick): static
    {
        if ($this->affiliateClicks->removeElement($affiliateClick)) {
            // set the owning side to null (unless already changed)
            if ($affiliateClick->getDream() === $this) {
                $affiliateClick->setDream(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AffiliateConversion>
     */
    public function getAffiliateConversions(): Collection
    {
        return $this->affiliateConversions;
    }

    public function addAffiliateConversion(AffiliateConversion $affiliateConversion): static
    {
        if (!$this->affiliateConversions->contains($affiliateConversion)) {
            $this->affiliateConversions->add($affiliateConversion);
            $affiliateConversion->setDream($this);
        }

        return $this;
    }

    public function removeAffiliateConversion(AffiliateConversion $affiliateConversion): static
    {
        if ($this->affiliateConversions->removeElement($affiliateConversion)) {
            // set the owning side to null (unless already changed)
            if ($affiliateConversion->getDream() === $this) {
                $affiliateConversion->setDream(null);
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
        return max(0, $this->quantityNeeded - $this->purchasedQuantity);
    }

    public function isFullyFulfilled(): bool
    {
        return $this->purchasedQuantity >= $this->quantityNeeded;
    }

    public function __toString(): string
    {
        return $this->productTitle ?? 'Dream #' . $this->id;
    }
}
