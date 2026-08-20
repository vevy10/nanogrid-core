<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\Length(max: 150)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(length: 100)]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private string $serialNumber;

    #[ORM\Column(length: 50)]
    #[Assert\Length(max: 50)]
    #[Assert\NotBlank]
    private string $type;

    #[ORM\Column(length: 30)]
    #[Assert\Length(max: 30)]
    #[Assert\NotBlank]
    private string $status;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $installedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSeenAt = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    #[ORM\JoinColumn(nullable: false)]
    private Site $site;

    /**
     * @var Collection<int, Incident>
     */
    #[ORM\OneToMany(targetEntity: Incident::class, mappedBy: 'equipment')]
    private Collection $incidents;

    public function __construct()
    {
        $this->incidents = new ArrayCollection();
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

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): static
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getInstalledAt(): ?\DateTimeImmutable
    {
        return $this->installedAt;
    }

    public function setInstalledAt(?\DateTimeImmutable $installedAt): static
    {
        $this->installedAt = $installedAt;

        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(?\DateTimeImmutable $lastSeenAt): static
    {
        $this->lastSeenAt = $lastSeenAt;

        return $this;
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function setSite(Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection<int, Incident>
     */
    public function getIncidents(): Collection
    {
        return $this->incidents;
    }

    public function addIncident(Incident $incident): static
    {
        if (!$this->incidents->contains($incident)) {
            $this->incidents->add($incident);
            $incident->setEquipment($this);
        }

        return $this;
    }

    public function removeIncident(Incident $incident): static
    {
        if ($this->incidents->removeElement($incident)) {
            // set the owning side to null (unless already changed)
            if ($incident->getEquipment() === $this) {
                $incident->setEquipment(null);
            }
        }

        return $this;
    }
}