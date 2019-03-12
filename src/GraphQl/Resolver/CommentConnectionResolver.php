<?php

namespace App\GraphQl\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class CommentConnectionResolver implements ResolverInterface, AliasedInterface
{
    private $em;
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
    }

    private function getCommentsInfo($offset, $limit)
    {
        $dql = "select c, partial u.{id, username}, s, partial a.{id, username} ";
        $dql.= "from App\Entity\Comment c JOIN c.author u JOIN c.shirt s JOIN s.author a ";
        $dql.= "ORDER BY c.published_date DESC";

        $query = $this->em->createQuery($dql)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $comments = $query->getArrayResult();

        return $comments;
    }

    public function getComments(Argument $args)
    {
        $paginator = new Paginator(function ($offset, $limit) {
            return $this->getCommentsInfo($offset, $limit);
        });

        return $paginator->forward($args);
    }

    static public function getAliases()
    {
        return [
            'getComments' => 'get_comments'
        ];
    }
}
