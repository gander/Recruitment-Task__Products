<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"api"})
     * @Assert\Length(max="255", groups={"api"})
     * @Assert\Type(type="string", groups={"api"})
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     * @Assert\NotBlank(groups={"api"})
     * @Assert\Regex(pattern="/^\d+(\.\d{1,2})?$/", groups={"api"})
     * @Assert\Length(max="8", groups={"api"})
     * @Assert\Positive(groups={"api"})
     */
    private $price;

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
}
