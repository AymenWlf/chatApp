<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function index(Request $request,UserPasswordHasherInterface $hasher,EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class,$user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) 
        { 
            $user = $form->getData();
            $password = $form->getData()->getPassword();
            $hashedPassword = $hasher->hashPassword($user,$password);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

        }
        
        return $this->render('register/index.html.twig',[
            'form' => $form->createView(),
        ]);
    }
}
