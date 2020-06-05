<?php

namespace App\Entity;

use App\Repository\DiscordRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DiscordRoleRepository::class)
 */
class DiscordRole
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\ManyToMany(targetEntity=Character::class, mappedBy="Roles")
     */
    private $characters;

    /**
     * @ORM\ManyToMany(targetEntity=Corporation::class, mappedBy="Roles")
     */
    private $corporations;

    /**
     * @ORM\ManyToMany(targetEntity=Alliance::class, mappedBy="Roles")
     */
    private $alliances;

    public function __construct()
    {
        $this->characters = new ArrayCollection();
        $this->corporations = new ArrayCollection();
        $this->alliances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    /**
     * @return Collection|Character[]
     */
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    public function addCharacter(Character $character): self
    {
        if (!$this->characters->contains($character)) {
            $this->characters[] = $character;
            $character->addRole($this);
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        if ($this->characters->contains($character)) {
            $this->characters->removeElement($character);
            $character->removeRole($this);
        }

        return $this;
    }

    /**
     * @return Collection|Corporation[]
     */
    public function getCorporations(): Collection
    {
        return $this->corporations;
    }

    public function addCorporation(Corporation $corporation): self
    {
        if (!$this->corporations->contains($corporation)) {
            $this->corporations[] = $corporation;
            $corporation->addRole($this);
        }

        return $this;
    }

    public function removeCorporation(Corporation $corporation): self
    {
        if ($this->corporations->contains($corporation)) {
            $this->corporations->removeElement($corporation);
            $corporation->removeRole($this);
        }

        return $this;
    }

    /**
     * @return Collection|Alliance[]
     */
    public function getAlliances(): Collection
    {
        return $this->alliances;
    }

    public function addAlliance(Alliance $alliance): self
    {
        if (!$this->alliances->contains($alliance)) {
            $this->alliances[] = $alliance;
            $alliance->addRole($this);
        }

        return $this;
    }

    public function removeAlliance(Alliance $alliance): self
    {
        if ($this->alliances->contains($alliance)) {
            $this->alliances->removeElement($alliance);
            $alliance->removeRole($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
