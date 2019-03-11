<?php

namespace App\GraphQl\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class UserConnectionResolver implements ResolverInterface, AliasedInterface
{
    private $em;
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
    }

    private function getUsersInfo($offset, $limit)
    {
        $query = $this->em->createQuery("select u.id, u.username from App\Entity\User u")
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $users = $query->getArrayResult();

        return $users;
    }

    public function getUsers(Argument $args)
    {
        $paginator = new Paginator(function ($offset, $limit) {
            return $this->getUsersInfo($offset, $limit);
        });

        $users = $paginator->auto($args, function () {
            return $this->em->createQuery("select COUNT(u.id) from App\Entity\User u")->getSingleScalarResult();
        });

        return $users;
    }

    static public function getAliases()
    {
        return [
            'getUsers' => 'get_users'
        ];
    }
}
