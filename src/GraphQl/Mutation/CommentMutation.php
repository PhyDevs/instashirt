<?php

namespace App\GraphQl\Mutation;

use App\Entity\Comment;
use App\Entity\Shirt;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;

class CommentMutation implements MutationInterface, AliasedInterface
{
    private $em;
    private $comment_repository;
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
        $this->comment_repository = $this->em->getRepository(Comment::class);

    }

    public function createComment(Argument $value)
    {
        $args = $value->getRawArguments();

        $shirt = $this->em->getRepository(Shirt::class)->find($args['shirtID'] ?? '');
        if (!$shirt) {
            throw new UserError(sprintf("No shirt has been found with shirtID: %s", $args['shirtID'] ?? ''));
        }
        if (!isset($args['content']) || empty(trim($args['content']))) {
            throw new UserError("The content value should not be blank.");
        }
        $comment = new Comment();
        $comment->setContent(trim($args['content']));
        $comment->setShirt($shirt);

        $this->em->persist($comment);
        $this->em->flush();

        return $this->commentToArr($comment);
    }

    public function updateComment(Argument $value)
    {
        $args = $value->getRawArguments();

        $comment = $this->comment_repository->find($args['id'] ?? '');
        if(!$comment) {
            throw new UserError(sprintf("No comment has been found with id: %s", $args['id'] ?? ''));
        }

        if(isset($args['content']) && $args['content'] !== $comment->getContent()) {
            $comment->setContent($args['content']);
            $this->em->flush();
        }

        return $this->commentToArr($comment);
    }

    public function deleteComment(Argument $value)
    {
        $args = $value->getRawArguments();
        $comment = $this->comment_repository->find($args['id'] ?? '');
        if(!$comment) {
            throw new UserError(sprintf("No comment has benn found with id: %s", $args['id'] ?? ''));
        }
        $id = $comment->getId();

        $this->em->remove($comment);
        $this->em->flush();

        return [
            'id' => $id
        ];
    }

    private function commentToArr(Comment $comment)
    {
        $shirt = $comment->getShirt();
        return [
            'id' => $comment->getId() ?? "",
            'content' => $comment->getContent(),
            'published_date' => $comment->getPublishedDate() ?? new \DateTime('now'),
            'author' => [
                'id' => $comment->getAuthor() ? $comment->getAuthor()->getId() : null,
                'username' => $comment->getAuthor() ? $comment->getAuthor()->getUsername() : null
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
                    "id" => $shirt->getAuthor() ? $shirt->getAuthor()->getId() : null,
                    "username" => $shirt->getAuthor() ? $shirt->getAuthor()->getUsername() : null,
                ]
            ]
        ];
    }

    static public function getAliases()
    {
        return [
            'createComment' => 'create_comment',
            'updateComment' => 'update_comment',
            'deleteComment' => 'delete_comment'
        ];
    }
}
