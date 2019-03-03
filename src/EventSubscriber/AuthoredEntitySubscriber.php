<?php

namespace App\EventSubscriber;

use App\Entity\AuthoredEntityInterface;
use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;


class AuthoredEntitySubscriber implements EventSubscriber
{
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

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof AuthoredEntityInterface)
            {
                return;
            }

            // Just A Test Author
            /** @var  User $author */
            $author = $em->getRepository(User::class)->findOneBy(['username' => 'u_user']);

            /** @var AuthoredEntityInterface $entity */
            $entity->setAuthor($author);
            $entity->setPublishedDate( new \DateTime() );

            $entityClassName = (string) get_class ($entity);
            $uow->recomputeSingleEntityChangeSet(
                $em->getClassMetadata($entityClassName),
                $entity
            );
        }
    }

}
