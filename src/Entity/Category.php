<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'categories')]
#[ORM\HasLifecycleCallbacks]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 120, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Dream::class)]
    private Collection $dreams;

    public function __construct()
    {
        $this->dreams = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setTimestamps(): void
    {
        $this->createdAt = new \DateTime();
        if ($this->slug === null) {
            $this->slug = $this->generateSlug($this->name);
        }
    }

    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return $slug;
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
        $this->slug = $this->generateSlug($name);
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
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
            $dream->setCategory($this);
        }

        return $this;
    }

    public function removeDream(Dream $dream): static
    {
        if ($this->dreams->removeElement($dream)) {
            // set the owning side to null (unless already changed)
            if ($dream->getCategory() === $this) {
                $dream->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
