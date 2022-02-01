<?php

namespace App\Controller;

use Exception;
use App\Entity\Participant;
use App\Entity\Conversation;
use App\Repository\UserRepository;
use Symfony\Component\WebLink\Link;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\Discovery;

#[Route('conversations',name:'conversations.')]
class ConversationController extends AbstractController
{
    private $em;

    private $conversationRepository;

    public function __construct(EntityManagerInterface $em,ConversationRepository $conversationRepository)
    {
        $this->em = $em;
        $this->conversationRepository = $conversationRepository;
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        if($this->getUser() === null)
        {
            new Exception("You have to be connected !");
        }

        return $this->redirectToRoute('index');
    }

    
    #[Route('/', name: 'newConversation',methods:['POST'])]
    public function newConversation(Request $request,UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();
        $checkOtherUserId = $request->get('otherUser',0);
        $otherUser = $userRepository->findOneBy(["id" => $checkOtherUserId]);

        //Any user found
        if(is_null($otherUser))
        {
            throw new Exception("The other user was not found !");
        }

        //Cannot create a conversation with myself
        if($otherUser->getId() === $currentUser->getId()/*function works*/)
        {
            throw new Exception("You can't create a conversation with yourself !");
        }

        //check if conversation already exists
        $conversation =$this->conversationRepository->findConversationByParticipants($currentUser->getId(),$otherUser->getId());//will create function later

        if(count($conversation))
        {
            throw new Exception ("Conversation already Exists !");
        }

        $conversation = new Conversation();

        //Adding first participant
        $participant = new Participant();
        $participant->setUser($currentUser);
        $participant->setConversation($conversation);

        //Adding second participant
        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        $this->em->getConnection()->beginTransaction();

        try{
            $this->em->persist($participant);
            $this->em->persist($otherParticipant);
            $this->em->persist($conversation);

            $this->em->flush();
            $this->em->commit();
        }catch(Exception $e)
        {
            $this->em->rollback();
            throw $e;
        }

        return $this->json([
            'id' => $conversation->getId()
        ],Response::HTTP_CREATED);
    }

    #[Route('/', name:'getConversations', methods:'GET')]
    public function getConversations(Request $request,Discovery $discovery,Authorization $authorization):Response
    {
        $currentUserId = $this->getUser()->getId();
        $conversations = $this->conversationRepository->findConversationsByUser($currentUserId);

        $hubURL = $this->getParameter("mercure.default_hub");//"http://localhost:8000/.well-known/mercure"

        // $discovery->addLink($request,$hubURL);
        // dd($discovery);

        $this->addLink($request,new Link('mercure',$hubURL));

        return $this->json($conversations,Response::HTTP_CREATED);
    }

}
