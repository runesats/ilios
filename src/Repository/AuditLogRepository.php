<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AuditLog;
use App\Traits\ManagerRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AuditLogRepository extends ServiceEntityRepository implements DTORepositoryInterface, RepositoryInterface
{
    use ManagerRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
    }

    public function findDTOsBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        throw new \Exception('DTOs for AuditLogs are not implemented yet');
    }

    /**
     * Returns all audit log entries in a given date/time range.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    public function findInRange(\DateTime $from, \DateTime $to)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a as log', 'u.id as userId')
            ->from('App\Entity\AuditLog', 'a')
            ->leftJoin('a.user', 'u')
            ->where(
                $qb->expr()->between(
                    'a.createdAt',
                    ':from',
                    ':to'
                )
            )
            ->setParameters(
                [
                    'from' => $from->format('Y-m-d H:i:s'),
                    'to' => $to->format('Y-m-d H:i:s:'),
                ]
            );

        $results = $qb->getQuery()->getArrayResult();
        $rhett = [];
        foreach ($results as $arr) {
            $combined = $arr['log'];
            $combined['userId'] = $arr['userId'];

            $rhett[] = $combined;
        }

        return $rhett;
    }

    /**
     * Deletes all audit log entries in a given date/time range.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     */
    public function deleteInRange(\DateTime $from, \DateTime $to)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->delete('App\Entity\AuditLog', 'a')
            ->add(
                'where',
                $qb->expr()->between(
                    'a.createdAt',
                    ':from',
                    ':to'
                )
            )
            ->setParameters(
                [
                    'from' => $from->format('Y-m-d H:i:s'),
                    'to' => $to->format('Y-m-d H:i:s:'),
                ]
            );
        $qb->getQuery()->execute();
    }

    /**
     * Write logs to the database
     *
     * We use the DBAL layer here so we can insert with the userId and
     * do not need to access the user entity
     *
     * @param array $entries
     *
     * @throws \Exception where there are issues with the passed data
     */
    public function writeLogs(array $entries)
    {
        $conn = $this->_em->getConnection();
        $now = new \DateTime();
        $timestamp = $now->format('Y-m-d H:i:s');
        $logs = array_map(function (array $entry) use ($timestamp) {
            $keys = ['action', 'objectId', 'objectClass', 'valuesChanged', 'userId'];
            $log = [];
            foreach ($keys as $key) {
                if (!array_key_exists($key, $entry)) {
                    throw new \Exception("Log entry missing required {$key} key: " . var_export($entry, true));
                }
            }
            $log['action'] = $entry['action'];
            $log['objectId'] = empty($entry['objectId']) ? 0 : $entry['objectId'];
            $log['objectClass'] = $entry['objectClass'];
            $log['valuesChanged'] = $entry['valuesChanged'];
            $log['user_id'] = $entry['userId'];
            $log['createdAt'] = $timestamp;

            return $log;
        }, $entries);

        foreach ($logs as $log) {
            $conn->insert('audit_log', $log);
        }
    }

    /**
     * Returns a list of field names of the corresponding entity.
     *
     * @return array
     *
     * @todo Refactor this out into a trait or stick it somewhere else. [ST 2015/09/02]
     */
    public function getFieldNames()
    {
        return $this->_em->getClassMetadata($this->getClassName())->getFieldNames();
    }
}
