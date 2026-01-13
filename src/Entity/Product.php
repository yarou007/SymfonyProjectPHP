<?php


namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;


    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?bool $isAvailable = true;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setProductName(string $name): static
    {
        $this->name = $name;
        return $this;
    }


     public function getProductName(): ?string
    {
        return $this->name;
    }

    public function setProductQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }
    public function getProductQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getProductUnitPrice(): ?string
    {
        return $this->price;
    }

    public function setProductUnitPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }


    public function isAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }
}