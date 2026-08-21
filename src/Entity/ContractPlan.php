<?php

namespace App\Entity;

use App\Repository\ContractPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContractPlanRepository::class)]
class ContractPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100)]
    private string $name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30, unique: true)]
    private string $code;

    #[ORM\Column]
    private int $annualPrice;

    #[ORM\Column]
    private int $freePreventiveVisitsPerYear;

    #[ORM\Column]
    private int $additionalVisitCost;

    #[ORM\Column]
    private int $curativeInterventionCost;

    #[ORM\Column]
    private int $consumableReplacementCost;

    #[ORM\Column(nullable: true)]
    private ?int $annualConsumableCoverageLimit = null;

    #[ORM\Column]
    private bool $phoneSupportIncluded;

    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30)]
    private string $status;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, SiteContract>
     */
    #[ORM\OneToMany(targetEntity: SiteContract::class, mappedBy: 'contractPlan')]
    private Collection $siteContracts;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
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

    public function getAnnualPrice(): int
    {
        return $this->annualPrice;
    }

    public function setAnnualPrice(int $annualPrice): static
    {
        $this->annualPrice = $annualPrice;

        return $this;
    }

    public function getFreePreventiveVisitsPerYear(): int
    {
        return $this->freePreventiveVisitsPerYear;
    }

    public function setFreePreventiveVisitsPerYear(int $freePreventiveVisitsPerYear): static
    {
        $this->freePreventiveVisitsPerYear = $freePreventiveVisitsPerYear;

        return $this;
    }

    public function getAdditionalVisitCost(): int
    {
        return $this->additionalVisitCost;
    }

    public function setAdditionalVisitCost(int $additionalVisitCost): static
    {
        $this->additionalVisitCost = $additionalVisitCost;

        return $this;
    }

    public function getCurativeInterventionCost(): int
    {
        return $this->curativeInterventionCost;
    }

    public function setCurativeInterventionCost(int $curativeInterventionCost): static
    {
        $this->curativeInterventionCost = $curativeInterventionCost;

        return $this;
    }

    public function getConsumableReplacementCost(): int
    {
        return $this->consumableReplacementCost;
    }

    public function setConsumableReplacementCost(int $consumableReplacementCost): static
    {
        $this->consumableReplacementCost = $consumableReplacementCost;

        return $this;
    }

    public function getAnnualConsumableCoverageLimit(): ?int
    {
        return $this->annualConsumableCoverageLimit;
    }

    public function setAnnualConsumableCoverageLimit(?int $annualConsumableCoverageLimit): static
    {
        $this->annualConsumableCoverageLimit = $annualConsumableCoverageLimit;

        return $this;
    }

    public function isPhoneSupportIncluded(): bool
    {
        return $this->phoneSupportIncluded;
    }

    public function setPhoneSupportIncluded(bool $phoneSupportIncluded): static
    {
        $this->phoneSupportIncluded = $phoneSupportIncluded;

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
            $siteContract->setContractPlan($this);
        }

        return $this;
    }

    public function removeSiteContract(SiteContract $siteContract): static
    {
        $this->siteContracts->removeElement($siteContract);

        return $this;
    }
}