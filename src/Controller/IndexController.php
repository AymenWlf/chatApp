<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        if($this->getUser())
        {
            $username = $this->getUser()->getUserIdentifier();
            $mercure_secret_key = $this->getParameter('mercure_secret_key');

            $config = Configuration::forSymmetricSigner(new Sha256(),InMemory::plainText($mercure_secret_key));
            $token = $config->builder()
                ->withClaim('mercure',['subscribe' => [sprintf("/%s",$username)]])  //think: mercure = rel of link header 
                ->getToken($config->signer(), $config->signingKey());
                ; 
            
            $response = $this->render('index/index.html.twig');

            $cookie = Cookie::create(
                'mercureAuthorization',
                $token->toString(),
                (new DateTime())->add(new DateInterval('PT2H')),
                '/.well-known/mercure',
                null,
                false,
                true,
                false,
                'strict',
            );
            
            $response->headers->setCookie($cookie);
            return $response;
        }

        return $this->render('index/index.html.twig');
        
    }
}
