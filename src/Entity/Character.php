<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CharacterRepository::class)
 * @ORM\Table(name="`character`")
 */
class Character
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
     * @ORM\ManyToMany(targetEntity=DiscordRole::class, inversedBy="characters")
     */
    private $Roles;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $DiscordId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $DiscordName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $DiscordMail;

    public function __construct()
    {
        $this->Roles = new ArrayCollection();
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
     * @return Collection|DiscordRole[]
     */
    public function getRoles(): Collection
    {
        return $this->Roles;
    }

    public function addRole(DiscordRole $role): self
    {
        if (!$this->Roles->contains($role)) {
            $this->Roles[] = $role;
        }

        return $this;
    }

    public function removeRole(DiscordRole $role): self
    {
        if ($this->Roles->contains($role)) {
            $this->Roles->removeElement($role);
        }

        return $this;
    }

    public function getDiscordId(): ?string
    {
        return $this->DiscordId;
    }

    public function setDiscordId(?string $DiscordId): self
    {
        $this->DiscordId = $DiscordId;

        return $this;
    }

    public function getDiscordName(): ?string
    {
        return $this->DiscordName;
    }

    public function setDiscordName(?string $DiscordName): self
    {
        $this->DiscordName = $DiscordName;

        return $this;
    }

    public function getDiscordMail(): ?string
    {
        return $this->DiscordMail;
    }

    public function setDiscordMail(?string $DiscordMail): self
    {
        $this->DiscordMail = $DiscordMail;

        return $this;
    }
}
