<?php
namespace Ilios\CoreBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Ilios\CoreBundle\Service\Logger;
use Ilios\CoreBundle\Entity\LoggableEntityInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * LogEntityChanges event listener
 * Listen for every change to an entity and log it
 */
class LogEntityChanges
{
    use ContainerAwareTrait;

    public function getSubscribedEvents()
    {
        return [
            'onFlush'
        ];
    }

    /**
    * Get all the entities that have changed and create log entries for them
    *
    * @param OnFlushEventArgs $eventArgs
    */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $uow = $entityManager->getUnitOfWork();
        $actions = [];
        
        $actions['create'] = $uow->getScheduledEntityInsertions();
        $actions['update'] = $uow->getScheduledEntityUpdates();
        $actions['delete'] = $uow->getScheduledEntityDeletions();
        
        $auditLogMetaData = $entityManager->getClassMetadata('IliosCoreBundle:AuditLog');
        $logger = $this->container->get('ilioscore.logger');
        foreach ($actions as $action => $entities) {
            foreach ($entities as $entity) {
                if ($entity instanceof LoggableEntityInterface) {
                    $changeset = $uow->getEntityChangeSet($entity);
                    $valuesChanged = implode(array_keys($changeset), ',');
                    $id = (string) $entity;
                    $auditLog = $logger->log($action, $id, get_class($entity), $valuesChanged, false);
                    $uow->computeChangeSet($auditLogMetaData, $auditLog);
                }
            }
        }
    }
}
