<?php

namespace App\GraphQl\Mutation;

use App\Entity\Shirt;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Error\UserErrors;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShirtMutation implements MutationInterface, AliasedInterface
{
    private $em;
    private $slugify;
    private $validator;
    private $shirt_repository;

    public function __construct(
        EntityManagerInterface $manager,
        SlugifyInterface $slugify,
        ValidatorInterface $validator
    )
    {
        $this->em = $manager;
        $this->slugify = $slugify;
        $this->validator = $validator;
        $this->shirt_repository = $this->em->getRepository(Shirt::class);
    }

    public function createShirt(Argument $value)
    {
        $args = $value->getRawArguments();
        $shirt = new Shirt();
        $shirt->setTitle(trim( $args["title"] ));
        $shirt->setDescription($args["description"] ?? "");
        $shirt->setFrontPath($args["front_path"]);
        $shirt->setBackPath($args["back_path"]);

        $slug = $this->generateSlug($shirt->getTitle());
        $shirt->setSlug($slug);

        $this->validateShirt($shirt);
        $this->em->persist($shirt);
        $this->em->flush();

        return $this->shirtToArr($shirt);
    }

    public function updateShirt(Argument $value)
    {
        $args = $value->getRawArguments();
        $shirt = $this->shirt_repository->find( $args['id'] ?? '');
        if(!$shirt) {
            throw new UserError(sprintf("No Shirt found with id : %s", $args['id'] ?? null));
        }
        if(isset($args['title']) && trim($args['title']) !== $shirt->getTitle()) {
            $shirt->setTitle(trim($args['title']));
            $slug = $this->generateSlug($shirt->getTitle());
            $shirt->setSlug($slug);
        }

        $shirt->setDescription($args['description'] ?? $shirt->getDescription());
        $shirt->setFrontPath($args['front_path'] ?? $shirt->getFrontPath());
        $shirt->setBackPath($args['back_path'] ?? $shirt->getBackPath());

        $this->em->flush();

        return $this->shirtToArr($shirt);
    }

    public function deleteShirt(Argument $value)
    {
        $args = $value->getRawArguments();
        $criteria = [ array_keys($args)[0] ?? null => array_shift($args) ];
        if( !array_key_exists('id', $criteria) && !array_key_exists('slug', $criteria))
        {
            throw new UserError("Field 'shirt' is missing required argument: id or slug");
        }

        $shirt = $this->shirt_repository->findOneBy($criteria);
        if (!$shirt) {
            throw new UserError("No shirt has been found");
        }
        $id = $shirt->getId();

        $this->em->remove($shirt);
        $this->em->flush();

        return [
            'id' => $id
        ];
    }

    private function generateSlug(?string $title)
    {
        $slug = $this->slugify->slugify($title);
        if( empty($slug) ) {
            throw new UserError(sprintf("The title value is not valid."));
        }

        $counter = 1;
        while( $this->shirt_repository->findOneBy(["slug" => $slug]) )
        {
            $slug = ($counter > 1) ?
                substr_replace($slug, $counter, -strlen((string) $counter-1)) :
                $slug.'-'.$counter;
            $counter++;
        }
        return $slug;
    }

    private function validateShirt(Shirt $shirt)
    {
        $errors = $this->validator->validate($shirt);
        if (count($errors) > 0)
        {
            $shirt_errors = [];
            foreach ($errors as $error)
            {
                $msg = (string) $error->getMessage();
                $property=  (string) $error->getPropertyPath();
                $shirt_errors[] = new UserError(sprintf("%s: %s", $property, $msg));
            }
            throw new UserErrors($shirt_errors);
        }
    }

    private function shirtToArr(Shirt $shirt)
    {
        return [
            "id" => $shirt->getId() ?? "",
            "title" => $shirt->getTitle(),
            "slug" => $shirt->getSlug(),
            "description" => $shirt->getDescription(),
            "front_path" => $shirt->getFrontPath(),
            "back_path" => $shirt->getBackPath(),
            "published_date" => $shirt->getPublishedDate() ?? new \DateTime('now'),
            "author" => [
                "id" => $shirt->getAuthor() ? $shirt->getAuthor()->getId() : null,
                "username" => $shirt->getAuthor() ? $shirt->getAuthor()->getUsername() : null,
            ],
        ];
    }

    public static function getAliases()
    {
        return [
            'createShirt' => 'create_shirt',
            'updateShirt' => 'update_shirt',
            'deleteShirt' => 'delete_shirt'
        ];
    }
}
