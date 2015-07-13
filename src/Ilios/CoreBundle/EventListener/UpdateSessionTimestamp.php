<?php
namespace Ilios\CoreBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * UpdateSessionTimestamp event listener
 * To correctly set the session last_updated timestamp we have to listen for updates to the session as well as
 * all the related entities
 *
 * The Doctrine built in LifeCycle Callbacks were not able to handle this correctly,
 * or else I was never able to write them correctly
 * */
class UpdateSessionTimestamp
{
    public function getSubscribedEvents()
    {
        return [
            'onFlush'
        ];
    }

    /**
    * Grab all of the entities that have a relationship with session and update the session
    * they are associated with
    *
    * We have to do this operation usign onFlush so we can catch inserts, updated
    * and deletes for all associations
    *
    * @param OnFlushEventArgs $eventArgs
    */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $uow = $entityManager->getUnitOfWork();
        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions()
        );
        $sessionMetadata = $entityManager->getClassMetadata('IliosCoreBundle:Session');
        $sessions = [];
        foreach ($entities as $entity) {
            switch (get_class($entity)) {
                case 'Ilios\CoreBundle\Entity\IlmSession':
                    $session = $entity->getSession();
                    if (! empty($session)) {
                        $sessions[] = $session;
                    }
                    break;
            }
        }
        $sessions = array_unique($sessions);
        foreach ($sessions as $session) {
            if (!$uow->isScheduledForDelete($session)) {
                $session->stampUpdate();
                if ($uow->isScheduledForUpdate($session) or $uow->isScheduledForInsert($session)) {
                    $uow->recomputeSingleEntityChangeSet($sessionMetadata, $session);
                } else {
                    $entityManager->persist($session);
                    $uow->computeChangeSet($sessionMetadata, $session);
                }
            }
        }
    }
}
