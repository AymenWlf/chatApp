<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/messages', name: 'messages.')]
class MessageController extends AbstractController
{
    const ATTRIBUTES_TO_SERIALISE = ['id','content','created_at','mine'];

    private $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    #[Route('/{id}', name: 'getMessages', methods:'GET')]
    public function index(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('view',$conversation);

        $messages = $this->messageRepository->findMessagesByConversationId($conversation->getId());

        array_map(function($message)
        {
            $message->setMine(
                ($this->getUser() === $message->getUser()) ? true : false
            );
            
        },$messages);
        
        return $this->json($messages,Response::HTTP_OK,[],[
            'attributes' => self::ATTRIBUTES_TO_SERIALISE
        ]);
    }
}
