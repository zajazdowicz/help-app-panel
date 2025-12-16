<?php

namespace App\Entity;

use App\Repository\OrphanageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrphanageRepository::class)]
#[ORM\Table(name: 'orphanages')]
#[ORM\HasLifecycleCallbacks]
class Orphanage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $city = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $region = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    private ?string $contactPhone = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'orphanage', targetEntity: Child::class, orphanRemoval: true)]
    private Collection $children;

    #[ORM\OneToMany(mappedBy: 'orphanage', targetEntity: Dream::class)]
    private Collection $dreams;

    #[ORM\OneToOne(mappedBy: 'orphanage', targetEntity: User::class)]
    private ?User $director = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->dreams = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Child>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Child $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setOrphanage($this);
        }

        return $this;
    }

    public function removeChild(Child $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getOrphanage() === $this) {
                $child->setOrphanage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Dream>
     */
    public function getDreams(): Collection
    {
        return $this->dreams;
    }

    public function addDream(Dream $dream): static
    {
        if (!$this->dreams->contains($dream)) {
            $this->dreams->add($dream);
            $dream->setOrphanage($this);
        }

        return $this;
    }

    public function removeDream(Dream $dream): static
    {
        if ($this->dreams->removeElement($dream)) {
            // set the owning side to null (unless already changed)
            if ($dream->getOrphanage() === $this) {
                $dream->setOrphanage(null);
            }
        }

        return $this;
    }

    public function getDirector(): ?User
    {
        return $this->director;
    }

    public function setDirector(?User $director): static
    {
        // unset the owning side of the relation if necessary
        if ($director === null && $this->director !== null) {
            $this->director->setOrphanage(null);
        }

        // set the owning side of the relation if necessary
        if ($director !== null && $director->getOrphanage() !== $this) {
            $director->setOrphanage($this);
        }

        $this->director = $director;

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

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
