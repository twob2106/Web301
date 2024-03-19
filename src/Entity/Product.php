<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
        // ...

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price;

    /**
     * @ORM\Column(type="date")
     */
    private $date_release;

        /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderMap", mappedBy="product")
     */
    private $orderMap;

    public function __construct()
    {
        $this->sales = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDateRelease(): ?\DateTimeInterface
    {
        return $this->date_release;
    }

    public function setDateRelease(\DateTimeInterface $date_release): self
    {
        $this->date_release = $date_release;

        return $this;
    }

    public function getOrderMap(): Collection
    {
        return $this->orderMap;
    }

    public function addOrderMap(OrderMap $orderMap): self
    {
        if (!$this->orderMap->contains($orderMap)) {
            $this->orderMap[] = $orderMap;
            $orderMap->setProduct($this);
        }

        return $this;
    }

    public function removeOrderMap(OrderMap $orderMap): self
    {
        if ($this->orderMap->removeElement($orderMap)) {
            // set the owning side to null (unless already changed)
            if ($orderMap->getProduct() === $this) {
                $orderMap->setProduct(null);
            }
        }

        return $this;
    }

}
