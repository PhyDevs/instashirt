<?php

namespace App\GraphQl\Mutation;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Error\UserErrors;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserMutation implements MutationInterface, AliasedInterface
{
    private $em;
    private $validator;
    private $user_repository;

    public function __construct(EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        $this->em = $manager;
        $this->validator = $validator;
        $this->user_repository = $this->em->getRepository(User::class);
    }

    public function createUser(Argument $value)
    {
        $args = $value->getRawArguments();
        $user = new User();
        $user->setUsername($args['username']);
        $user->setEmail($args['email']);
        $user->setPassword($args['password']);

        $this->validateUser($user);
        $this->em->persist($user);
        $this->em->flush();

        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'clientMutationId' => $args['clientMutationId'] ?? null
        ];
    }

    public function updateUser(Argument $value)
    {
        $args = $value->getRawArguments();
        $user = $this->user_repository->findOneBy(['username' => $args['username'] ?? null]);
        if(!$user) {
            throw new UserError(sprintf("No User found with username : %s", $args['username'] ?? null));
        }
        if(isset($args['password'])) {
            $user->setPassword($args['password']);
        }
        $user->setEmail($args['email'] ?? $user->getEmail());

        $this->validateUser($user);
        $this->em->flush();

        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'clientMutationId' => $args['clientMutationId'] ?? null
        ];
    }

    public function deleteUser(Argument $value)
    {
        $args = $value->getRawArguments();
        $user = $this->user_repository->findOneBy(['username' => $args['username'] ?? null]);
        if(!$user) {
            throw new UserError(sprintf("No User found with username : %s", $args['username'] ?? null));
        }
        $id = $user->getId();

        $this->em->remove($user);
        $this->em->flush();

        return [
            'id' => $id,
            'clientMutationId' => $args['clientMutationId'] ?? null
        ];
    }

    private function validateUser(User $user)
    {
        $errors = $this->validator->validate($user);
        if(count($errors) > 0)
        {
            $user_errors = [];
            foreach ($errors as $error)
            {
                $msg = (string) $error->getMessage();
                $property=  (string) $error->getPropertyPath();
                $user_errors[] = new UserError(sprintf("%s: %s", $property, $msg));
            }
            throw new UserErrors($user_errors);
        }
    }

    public static function getAliases()
    {
        return [
            'createUser' => 'create_user',
            'updateUser' => 'update_user',
            'deleteUser' => 'delete_user'
        ];
    }
}
