<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Authenticator
{
    private $em;
    private $encoder;
    private $container;
    private $JWTTokenManager;
    private $handler;

    public function __construct(
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder,
        ContainerInterface $container,
        JWTTokenManagerInterface $JWTTokenManager,
        AuthenticationSuccessHandler $handler
    )
    {
         $this->em = $manager;
         $this->encoder = $encoder;
         $this->container = $container;
         $this->JWTTokenManager = $JWTTokenManager;
         $this->handler = $handler;
    }

    /**
     * @param User $user
     * @return User|bool
     */
    public function authenticate(User $user)
    {
        if(!$user instanceof UserInterface) {
            return false;
        }

        $username = $user->getUsername() ?? null;
        $password = $user->getPassword() ?? '';
        $criteria = !is_null($username) ?
            ['username' => $user->getUsername()] :
            ['email' => $user->getEmail()];

        $user_res = $this->em->getRepository(User::class)->findOneBy($criteria);
        if(!$user_res) {
            return false;
        }
        if(!$this->encoder->isPasswordValid($user_res, $password)) {
            return false;
        }

        $token = $this->JWTTokenManager->create($user_res);
        $this->saveToken($user_res, $token);
        return $user_res;
    }

    /**
     * @param User $user
     * @param string $token
     */
    public function saveToken(User $user, string $token)
    {
        $expire = $this->container->getParameter('lexik_jwt_authentication.token_ttl') ?? 0;
        $expire+= time();
        $response = new Response();
        $tCookie = new Cookie('BEARER', $token, $expire, '/graphql');
        $response->headers->setCookie($tCookie);
        $response->send();

        $this->handler->handleAuthenticationSuccess($user, $token);
    }
}
