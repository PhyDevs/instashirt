<?php

namespace App\GraphQl\Mutation;

use App\Entity\User;
use App\Security\Authenticator;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;

class SecurityMutation implements MutationInterface, AliasedInterface
{
    private $authenticator;

    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function login(Argument $value)
    {
        $args = $value->getRawArguments();
        if(!isset($args['username']) && !isset($args['email']))
        {
            throw new UserError("Field 'login' is missing required argument: username or email");
        }

        $user = new User();
        $user->setPassword($args['password'] ?? '');
        isset($args['username']) ?
            $user->setUsername($args['username']) :
            $user->setEmail($args['email']);

        $user_res = $this->authenticator->authenticate($user);
        if(!$user_res) {
            throw new UserError("invalid credentials");
        }

        return [
            'id' => $user_res->getId(),
            'username' => $user_res->getUsername(),
        ];
    }

    public function logout(?string $clientMutationId)
    {
        $this->authenticator->removeToken();

        return [
            'clientMutationId' => $clientMutationId ?? "true"
        ];
    }

    static public function getAliases()
    {
        return [
            'login' => 'login',
            'logout' => 'logout'
        ];
    }
}
