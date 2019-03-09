<?php

namespace App\GraphQl\Resolver;

use App\Entity\Shirt;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;

class ShirtResolver implements ResolverInterface, AliasedInterface
{

    private $em;
    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
    }

    public function getShirt(Argument $args)
    {
        $raw_args = $args->getRawArguments();
        $criteria = [ array_keys($raw_args)[0] ?? null => array_shift($raw_args) ];
        if( !array_key_exists('id', $criteria) && !array_key_exists('slug', $criteria))
        {
            throw new UserError("Field 'shirt' is missing required argument: id or slug");
        }

        $shirt = $this->em->getRepository(Shirt::class)->findOneBy($criteria);
        if (!$shirt) {
            throw new UserError("No shirt has been found");
        }

        return [
            "id" => $shirt->getId(),
            "title" => $shirt->getTitle(),
            "slug" => $shirt->getSlug(),
            "description" => $shirt->getDescription(),
            "front_path" => $shirt->getFrontPath(),
            "back_path" => $shirt->getBackPath(),
            "published_date" => $shirt->getPublishedDate(),
            "author" => [
                "id" =>$shirt->getAuthor()->getId(),
                "username" => $shirt->getAuthor()->getUsername(),
            ]
        ];
    }

    public static function getAliases()
    {
        return [
            'getShirt' => 'get_shirt'
        ];
    }
}
