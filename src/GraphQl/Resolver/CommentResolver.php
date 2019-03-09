<?php

namespace App\GraphQl\Resolver;


use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;

class CommentResolver implements ResolverInterface, AliasedInterface
{
    private $em;
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
    }

    public function getComment(?int $id)
    {
        $comment = $this->em->getRepository(Comment::class)->find($id);
        if(!$comment) {
            throw new UserError("No comment has been found");
        }
        return [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'published_date' => $comment->getPublishedDate(),
            'author' => [
                'id' => $comment->getAuthor()->getId(),
                'username' => $comment->getAuthor()->getUsername()
            ],
            'shirt' => [
                "id" => $comment->getShirt()->getId(),
                "title" => $comment->getShirt()->getTitle(),
                "slug" => $comment->getShirt()->getSlug(),
                "description" => $comment->getShirt()->getDescription(),
                "front_path" => $comment->getShirt()->getFrontPath(),
                "back_path" => $comment->getShirt()->getBackPath(),
                "published_date" => $comment->getShirt()->getPublishedDate(),
                "author" => [
                    "id" =>$comment->getShirt()->getAuthor()->getId(),
                    "username" => $comment->getShirt()->getAuthor()->getUsername(),
                ]
            ]
        ];
    }

    public static function getAliases()
    {
        return [
            'getComment' => 'get_comment',
        ];
    }
}
