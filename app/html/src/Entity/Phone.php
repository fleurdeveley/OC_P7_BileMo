<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PhoneRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 */
class Phone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"phone:list", "phone:details"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="La marque du téléphone doit être remplie.")
     * @Assert\Length(min=3, minMessage="La marque de téléphone doit faire au minimum 3 caractères.")
     * @Groups({"phone:list", "phone:details"})
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le modèle du téléphone doit être rempli.")
     * @Assert\Length(min=3, minMessage="Le modèle du téléphone doit faire au minimum 3 caractères.")
     * @Groups({"phone:list", "phone:details"})
     */
    private $model;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="La description du téléphone doit être remplie.")
     * @Groups({"phone:list", "phone:details"})
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Le prix du téléphone doit être rempli.")
     * @Groups({"phone:list", "phone:details"})
     */
    private $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }
}
