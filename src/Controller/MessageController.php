<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Conversation;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/messages', name: 'messages.')]
class MessageController extends AbstractController
{
    const ATTRIBUTES_TO_SERIALISE = ['id','content','mine','createdAt'];

    private $messageRepository;
    
    private $em;

    public function __construct(MessageRepository $messageRepository,EntityManagerInterface $em)
    {
        $this->messageRepository = $messageRepository;
        $this->em = $em;
    }

    #[Route('/{id}', name: 'getMessages', methods:'GET')]
    public function index(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('view',$conversation);

        $messages = $this->messageRepository->findMessagesByConversationId($conversation->getId());

        // array_map(function($message)
        // {
        //     $message->setMine(
        //         ($this->getUser() === $message->getUser()) ? true : false
        //     );
            
        // },$messages);

        foreach($messages as $message)
        {
            $message->setMine(
                ($this->getUser() === $message->getUser()) ? true : false
            );
        }
        
        return $this->json($messages,Response::HTTP_OK,[],[
            'attributes' => self::ATTRIBUTES_TO_SERIALISE
        ]);
    }

    #[Route('/{id}', name: 'newMessage', methods:'POST')]
    public function newMessage(Conversation $conversation,Request $request,UserRepository $userRepository): Response
    {
        //Not same conversation issue
        $currentUser = $this->getUser();
        $userTest = $userRepository->findOneBy(['id' => 3]);//for postMAN
        $content = $request->get('content',null);

        $message = new Message();
        $message->setContent($content);
        $message->setUser($userTest);
        $message->setMine(true);

        $conversation->addMessage($message);
        $conversation->setLastMessage($message);

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($message);
            $this->em->persist($message);

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }


        return $this->json($message,Response::HTTP_CREATED,[],[
            'attributes' => self::ATTRIBUTES_TO_SERIALISE
        ]);
    }
}
