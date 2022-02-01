<?php

namespace App\Controller;

use Exception;
use App\Entity\Message;
use App\Entity\Conversation;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/messages', name: 'messages.')]
class MessageController extends AbstractController
{
    const ATTRIBUTES_TO_SERIALISE = ['id','content','mine','createdAt'];

    private $messageRepository;
    
    private $participantRepository;

    private $hub;
    
    private $em;

    public function __construct(MessageRepository $messageRepository,EntityManagerInterface $em,ParticipantRepository $participantRepository,HubInterface  $hub)
    {
        $this->messageRepository = $messageRepository;
        $this->participantRepository = $participantRepository;
        $this->em = $em;
        $this->hub = $hub;
    }

    #[Route('/{id}', name: 'index')]
    public function index(): Response
    {
        if($this->getUser() === null)
        {
            new Exception("You have to be connected !");
        }

        return $this->redirectToRoute('index');
    }


    #[Route('/{id}', name: 'getMessages', methods:'GET')]
    public function getMessages(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('view',$conversation);

        $messages = $this->messageRepository->findMessagesByConversationId($conversation->getId());

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
    public function newMessage(Conversation $conversation,Request $request,UserRepository $userRepository,SerializerInterface $serializer): Response
    {
        $currentUser = $this->getUser(); // 0

        $recipient = $this->participantRepository->findParticipantByConversationIdAndCurrentUserId(
            $conversation->getId(),
            // $this->getUser()->getId()
            $currentUser->getId() //0
        );         

        $content = $request->get('content',null);

        $message = new Message();
        $message->setContent($content);
        $message->setUser($$currentUser);
        $message->setUser($currentUser); // 0

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

        //salit lvidio wlkn ma bghach i t updata  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $message->setMine(false);
        $messageSerialized = $serializer->serialize($message,'json',[
            'attributes' => ['id','content','mine','createdAt','conversation' => ['id']]
            // 'attributes' => self::ATTRIBUTES_TO_SERIALISE
        ]);

        $update = new Update( //update data for the currentuser ans the other user !important to follow
            [
                sprintf("/conversations/%s",$conversation->getId()), //Why ?
                sprintf("/conversations/%s",$recipient->getUser()->getUserIdentifier()) //How ?
            ],
            $messageSerialized,
            true
        );

        $this->hub->publish($update);
        
        $message->setMine(true);

        return $this->json($message,Response::HTTP_CREATED,[],[
            'attributes' => self::ATTRIBUTES_TO_SERIALISE
        ]);
    }
}
