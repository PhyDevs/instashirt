<?php

namespace App\GraphQl\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class ShirtConnectionResolver implements ResolverInterface, AliasedInterface
{
    private $em;
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
    }

    private function getShirtsInf($offset, $limit)
    {
        $orderBy = "s.published_date";
        $dql = "select s, partial u.{id, username} from App\Entity\Shirt s JOIN s.author u ORDER BY ".$orderBy." DESC";
        $query = $this->em->createQuery($dql)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $shirts = $query->getArrayResult();

        return $shirts;
    }

    public function getShirts(Argument $args)
    {
        $paginator = new Paginator(function ($offset, $limit) {
            return $this->getShirtsInf($offset, $limit);
        });

        $shirts = $paginator->auto($args, function () {
            return $this->em->createQuery("select COUNT(s.id) from App\Entity\Shirt s")->getSingleScalarResult();
        });

        return $shirts;
    }

    static public function getAliases()
    {
        return [
            'getShirts' => 'get_shirts'
        ];
    }
}
