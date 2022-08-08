<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use App\Entity\LoggableEntityInterface;
use App\Service\LoggerQueue;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Doctrine event listener.
 * Listen for every change to an entity and log it.
 *
 * Class LogEntityChanges
 */
class LogEntityChanges
{
    //We have to inject the container to avoid a circular service reference
    use ContainerAwareTrait;

    /**
     * Get all the entities that have changed and create log entries for them
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $objectManager = $eventArgs->getObjectManager();
        $uow = $objectManager->getUnitOfWork();
        $actions = [];

        $actions['create'] = $uow->getScheduledEntityInsertions();
        $actions['update'] = $uow->getScheduledEntityUpdates();
        $actions['delete'] = $uow->getScheduledEntityDeletions();

        $updates = [];
        foreach ($actions as $action => $entities) {
            foreach ($entities as $entity) {
                if ($entity instanceof LoggableEntityInterface) {
                    $changes = $uow->getEntityChangeSet($entity);
                    $updates[$entity::class] = [
                        'entity' => $entity,
                        'action' => $action,
                        'changes' => array_keys($changes)
                    ];
                }
            }
        }

        $collections = $uow->getScheduledCollectionUpdates();
        foreach ($collections as $col) {
            /** @var PersistentCollection $col */
            $entity = $col->getOwner();
            $change = $col->getTypeClass()->name;
            if ($entity instanceof LoggableEntityInterface) {
                $entityClass = $entity::class;
                if (!array_key_exists($entityClass, $updates)) {
                    $updates[$entityClass] = [
                        'entity' => $entity,
                        'action' => 'update',
                        'changes' => []
                    ];
                }
                $ref = new ReflectionClass($change);
                $updates[$entityClass]['changes'][] = 'Ref:' . $ref->getShortName();
            }
        }
        $collections = $uow->getScheduledCollectionDeletions();
        foreach ($collections as $col) {
            /** @var PersistentCollection $col */
            $entity = $col->getOwner();
            $change = $col->getTypeClass()->name;
            if ($entity instanceof LoggableEntityInterface) {
                $entityClass = $entity::class;
                if (!array_key_exists($entityClass, $updates)) {
                    $updates[$entityClass] = [
                        'entity' => $entity,
                        'action' => 'update',
                        'changes' => []
                    ];
                }
                $ref = new ReflectionClass($change);
                $updates[$entityClass]['changes'][] = 'Ref:' . $ref->getShortName();
            }
        }
        $loggerQueue = $this->container->get(LoggerQueue::class);
        foreach ($updates as $arr) {
            $valuesChanged = implode(',', $arr['changes']);
            $entityName = $objectManager->getMetadataFactory()->getMetadataFor($arr['entity']::class)->getName();
            $loggerQueue->add($arr['action'], $arr['entity'], $entityName, $valuesChanged);
        }
    }
}
