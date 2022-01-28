<?php

namespace App\Controller;

use Exception;
use App\Entity\Participant;
use App\Entity\Conversation;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('conversations',name:'conversations')]
class ConversationController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    #[Route('/{id}', name: 'getConversations')]
    public function index(Request $request,UserRepository $userRepository,int $id): Response
    {
        $currentUser = $this->getUser();
        $checkOtherUserId = $request->get('otherUser',0);
        $otherUser = $userRepository->findOneBy(["id" => $id]);

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
        $conversation =$this->em->getRepository(Conversation::class)->findByParticipants($currentUser->getId(),$otherUser->getId());//will create function later

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
}
