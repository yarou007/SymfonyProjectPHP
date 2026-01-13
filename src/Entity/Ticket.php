<?php


namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $total = null;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: LigneTicket::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignes;


    public function __construct()
    {
        $this->lignes = new ArrayCollection();

    }

    // getters & setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): self
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return Collection<int, LigneTicket>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LigneTicket $ligne): self
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setTicket($this);
        }
        return $this;
    }

    public function removeLigne(LigneTicket $ligne): self
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getTicket() === $this) {
                $ligne->setTicket(null);
            }
        }
        return $this;
    }
}
