<?php

namespace App\GraphQl\Resolver;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;

class UserResolver implements ResolverInterface, AliasedInterface
{

    private $em;
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
    }

    public function getUser(Argument $args)
    {
        $raw_args = $args->getRawArguments();
        $criteria = [ array_keys($raw_args)[0] ?? null => array_shift($raw_args) ];
        if( !array_key_exists('id', $criteria) && !array_key_exists('username', $criteria))
        {
            throw new UserError("Field 'user' is missing required argument: id or username");
        }

        $user = $this->em->getRepository(User::class)->findOneBy($criteria);
        if(!$user) {
            throw new UserError("No user has been found");
        }

        return [
            'id' => $user->getId(),
            'username' => $user->getUsername()
        ];
    }

    public static function getAliases()
    {
        return [
            'getUser' => 'get_user',
        ];
    }
}
