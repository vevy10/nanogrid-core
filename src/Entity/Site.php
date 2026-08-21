<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[ORM\Column(length: 150)]
    private string $name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50, unique: true)]
    private string $code;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100)]
    private string $region;

    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30)]
    private string $status;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $commissionedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, Equipment> */
    #[ORM\OneToMany(targetEntity: Equipment::class, mappedBy: 'site')]
    private Collection $equipment;

    /** @var Collection<int, Household> */
    #[ORM\OneToMany(targetEntity: Household::class, mappedBy: 'site')]
    private Collection $households;

    /** @var Collection<int, Incident> */
    #[ORM\OneToMany(targetEntity: Incident::class, mappedBy: 'site')]
    private Collection $incidents;

    /**
     * @var Collection<int, SiteContract>
     */
    #[ORM\OneToMany(targetEntity: SiteContract::class, mappedBy: 'site')]
    private Collection $siteContracts;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->equipment = new ArrayCollection();
        $this->households = new ArrayCollection();
        $this->incidents = new ArrayCollection();
        $this->siteContracts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCommissionedAt(): ?\DateTimeImmutable
    {
        return $this->commissionedAt;
    }

    public function setCommissionedAt(?\DateTimeImmutable $commissionedAt): static
    {
        $this->commissionedAt = $commissionedAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /** @return Collection<int, Equipment> */
    public function getEquipment(): Collection
    {
        return $this->equipment;
    }

    public function addEquipment(Equipment $equipment): static
    {
        if (!$this->equipment->contains($equipment)) {
            $this->equipment->add($equipment);
            $equipment->setSite($this);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): static
    {
        $this->equipment->removeElement($equipment);

        return $this;
    }

    /** @return Collection<int, Household> */
    public function getHouseholds(): Collection
    {
        return $this->households;
    }

    public function addHousehold(Household $household): static
    {
        if (!$this->households->contains($household)) {
            $this->households->add($household);
            $household->setSite($this);
        }

        return $this;
    }

    public function removeHousehold(Household $household): static
    {
        $this->households->removeElement($household);

        return $this;
    }

    /** @return Collection<int, Incident> */
    public function getIncidents(): Collection
    {
        return $this->incidents;
    }

    public function addIncident(Incident $incident): static
    {
        if (!$this->incidents->contains($incident)) {
            $this->incidents->add($incident);
            $incident->setSite($this);
        }

        return $this;
    }

    public function removeIncident(Incident $incident): static
    {
        $this->incidents->removeElement($incident);

        return $this;
    }

    /**
     * @return Collection<int, SiteContract>
     */
    public function getSiteContracts(): Collection
    {
        return $this->siteContracts;
    }

    public function addSiteContract(SiteContract $siteContract): static
    {
        if (!$this->siteContracts->contains($siteContract)) {
            $this->siteContracts->add($siteContract);
            $siteContract->setSite($this);
        }

        return $this;
    }

    public function removeSiteContract(SiteContract $siteContract): static
    {
        $this->siteContracts->removeElement($siteContract);

        return $this;
    }
}