<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserEntitySubscriber implements EventSubscriber
{

    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $entities = array_merge($uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates());
        foreach ($entities as $entity) {
            if (!$entity instanceof UserInterface)
            {
                return;
            }

            /** @var User $entity */
            $entity->setPassword(
                $this->encoder->encodePassword($entity, $entity->getPassword())
            );

            $uow->recomputeSingleEntityChangeSet(
                $em->getClassMetadata(User::class),
                $entity
            );
        }

    }
}
