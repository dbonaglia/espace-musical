<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ApiResource(normalizationContext={"groups"={"user"}}, collectionOperations={"get"}, itemOperations={"get"})
 * @UniqueEntity(fields={"email"}, message="L'email' {{ value }} est déjà utilisée, veuillez en choisir une autre.")
 * @UniqueEntity(fields={"username"}, message="Le pseudo {{ value }} est déjà utilisé, veuillez en choisir un autre.")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"user", "message", "announcement", "event"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = 3, max = 30, minMessage = "Le pseudo {{ value }} n'est pas valide. Votre pseudo doit contenir {{ limit }} caractères minimum.", maxMessage = "otre pseudo doit contenir {{ limit }} caractères maximum.")
     * @Groups({"user", "announcement", "event"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message = "L'email {{ value }} n'est pas un email valide.")
     * @Groups({"user", "announcement", "event"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = 8, minMessage = "Le mot de passe n'est pas valide, il doit contenir {{ limit }} caractères minimum.")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user"})
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user"})
     */
    private $address;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Disk", inversedBy="users")
     * @Groups({"user"})
     */
    private $disks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Announcement", mappedBy="author", orphanRemoval=true)
     * @Groups({"user"})
     */
    private $announcements;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="author")
     * @Groups({"user"})
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="author")
     * @Groups({"user"})
     */
    private $events;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", mappedBy="participants")
     * @Groups({"user"})
     */
    private $participatedEvents;

    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->disks = new ArrayCollection();
        $this->announcements = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->participatedEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

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

    /**
     * @return Collection|Disk[]
     */
    public function getDisks(): Collection
    {
        return $this->disks;
    }

    public function addDisk(Disk $disk): self
    {
        if (!$this->disks->contains($disk)) {
            $this->disks[] = $disk;
        }

        return $this;
    }

    public function removeDisk(Disk $disk): self
    {
        if ($this->disks->contains($disk)) {
            $this->disks->removeElement($disk);
        }

        return $this;
    }

    /**
     * @return Collection|Announcement[]
     */
    public function getAnnouncements(): Collection
    {
        return $this->announcements;
    }

    public function addAnnouncement(Announcement $announcement): self
    {
        if (!$this->announcements->contains($announcement)) {
            $this->announcements[] = $announcement;
            $announcement->setAuthor($this);
        }

        return $this;
    }

    public function removeAnnouncement(Announcement $announcement): self
    {
        if ($this->announcements->contains($announcement)) {
            $this->announcements->removeElement($announcement);
            // set the owning side to null (unless already changed)
            if ($announcement->getAuthor() === $this) {
                $announcement->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setAuthor($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getAuthor() === $this) {
                $event->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getParticipatedEvents(): Collection
    {
        return $this->participatedEvents;
    }

    public function addParticipatedEvent(Event $participatedEvent): self
    {
        if (!$this->participatedEvents->contains($participatedEvent)) {
            $this->participatedEvents[] = $participatedEvent;
            $participatedEvent->addParticipant($this);
        }

        return $this;
    }

    public function removeParticipatedEvent(Event $participatedEvent): self
    {
        if ($this->participatedEvents->contains($participatedEvent)) {
            $this->participatedEvents->removeElement($participatedEvent);
            $participatedEvent->removeParticipant($this);
        }

        return $this;
    }

    public function getRoles() {
        return ['ROLE_USER'];
    }

    public function getSalt() {}
    public function eraseCredentials() {}
}
