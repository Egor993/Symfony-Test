<?php

namespace App\Entity;

use App\Repository\SportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SportRepository::class)
 */
class Sport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=TeamPlayer::class, mappedBy="sport")
     */
    private $teamPlayer;

    public function __construct()
    {
        $this->teamPlayer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|TeamPlayer[]
     */
    public function getTeamPlayer(): Collection
    {
        return $this->teamPlayer;
    }

    public function addTeamPlayer(TeamPlayer $teamPlayer): self
    {
        if (!$this->teamPlayer->contains($teamPlayer)) {
            $this->teamPlayer[] = $teamPlayer;
            $teamPlayer->setSport($this);
        }

        return $this;
    }

    public function removeTeamPlayer(TeamPlayer $teamPlayer): self
    {
        if ($this->teamPlayer->removeElement($teamPlayer)) {
            // set the owning side to null (unless already changed)
            if ($teamPlayer->getSport() === $this) {
                $teamPlayer->setSport(null);
            }
        }

        return $this;
    }
}
