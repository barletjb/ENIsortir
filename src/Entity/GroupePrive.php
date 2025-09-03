<?php

namespace App\Entity;

use App\Repository\GroupePriveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupePriveRepository::class)]
#[UniqueEntity(fields: ['nom'], message: 'Ce nom de groupe n\'est pas disponible')]
class GroupePrive
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 55, unique: true)]
    #[Assert\NotBlank(message: 'Vous devez indiquer un nom de groupe')]
    private ?string $nom = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'groupesPrives')]
    private Collection $user;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'groupePrive')]
    private Collection $sortie;


    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $chefGroupe = null;

    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->sortie = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->user->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortie(): Collection
    {
        return $this->sortie;
    }

    public function addSortie(Sortie $sortie): static
    {
        if (!$this->sortie->contains($sortie)) {
            $this->sortie->add($sortie);
            $sortie->setGroupePrive($this);
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): static
    {
        if ($this->sortie->removeElement($sortie)) {
            // set the owning side to null (unless already changed)
            if ($sortie->getGroupePrive() === $this) {
                $sortie->setGroupePrive(null);
            }
        }

        return $this;
    }

    public function getChefGroupe(): ?User
    {
        return $this->chefGroupe;
    }

    public function setChefGroupe(?User $chefGroupe): self
    {
        $this->chefGroupe = $chefGroupe;
        return $this;
    }
}
