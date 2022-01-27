<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $messages_read_at;

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private $id_conversation;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private $id_user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessagesReadAt(): ?\DateTimeInterface
    {
        return $this->messages_read_at;
    }

    public function setMessagesReadAt(?\DateTimeInterface $messages_read_at): self
    {
        $this->messages_read_at = $messages_read_at;

        return $this;
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
}
