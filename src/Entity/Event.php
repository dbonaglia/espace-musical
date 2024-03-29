<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @UniqueEntity(fields={"title"}, message="Le titre {{ value }} est déjà utilisé, veuillez en choisir un autre.")
 * @ApiResource(normalizationContext={"groups"={"event"}}, denormalizationContext={"groups"={"event"}}, collectionOperations={"get"}, itemOperations={"get"})
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"event", "user"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"event"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = 5, minMessage = "Le titre {{ value }} n'est pas valide. Votre titre doit contenir {{ limit }} caractères minimum.")
     * @Groups({"event", "user"})
     * @Assert\NotBlank(message= "Vous devez renseigner un titre d'évènement.")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"event"})
     * @Assert\NotBlank(message= "Vous devez renseigner au moins un artiste.")
     */
    private $artists;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"event"})
     * @Assert\NotBlank(message= "Vous devez renseigner un lieu.")
     */
    private $location;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min = 100, minMessage = "Votre description'est pas valide et doit contenir {{ limit }} caractères minimum.")
     * @Groups({"event"})
     * @Assert\NotBlank(message= "Vous devez renseigner au moins une description.")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"event"})
     * @Assert\LessThan(propertyPath="endDate", message="La date de début doit être antérieure à la date de fin")
     * @Assert\NotBlank(message= "Vous devez renseigner au moins une date de début.")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"event"})
     * @Assert\GreaterThan(propertyPath="startDate", message="La date de fin doit être après à la date de début")
     * @Assert\NotBlank(message= "Vous devez renseigner au moins une date de fin.")
     */
    private $endDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"event"})
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event"})
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="participatedEvents")
     * @Groups({"event"})
     */
    private $participants;

    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getArtists(): ?string
    {
        return $this->artists;
    }

    public function setArtists(string $artists): self
    {
        $this->artists = $artists;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
        }

        return $this;
    }
}
