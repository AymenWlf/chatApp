<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: conversation::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private $id_conversation;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private $id_user;

    #[ORM\Column(type: 'text')]
    private $content;

    #[ORM\Column(type: 'datetime')]
    private $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdConversation(): ?conversation
    {
        return $this->id_conversation;
    }

    public function setIdConversation(?conversation $id_conversation): self
    {
        $this->id_conversation = $id_conversation;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->id_user;
    }

    public function setIdUser(?User $id_user): self
    {
        $this->id_user = $id_user;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
