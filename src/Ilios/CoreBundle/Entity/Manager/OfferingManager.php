<?php

namespace Ilios\CoreBundle\Entity\Manager;


use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Id\AssignedGenerator;
use Ilios\CoreBundle\Entity\OfferingInterface;

/**
 * Class OfferingManager
 * @package Ilios\CoreBundle\Entity\Manager
 */
class OfferingManager extends AbstractManager implements OfferingManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOfferingBy(
        array $criteria,
        array $orderBy = null
    ) {
        $criteria['deleted'] = false;
        return $this->getRepository()->findOneBy($criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function findOfferingsBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $criteria['deleted'] = false;
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function updateOffering(
        OfferingInterface $offering,
        $andFlush = true,
        $forceId = false
    ) {
        $this->em->persist($offering);

        if ($forceId) {
            $metadata = $this->em->getClassMetaData(get_class($offering));
            $metadata->setIdGenerator(new AssignedGenerator());
        }

        if ($andFlush) {
            $this->em->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOffering(
        OfferingInterface $offering
    ) {
        $offering->setDeleted(true);
        $this->updateOffering($offering);
    }

    /**
     * {@inheritdoc}
     */
    public function createOffering()
    {
        $class = $this->getClass();
        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function getOfferingsForTeachingReminders($daysInAdvance)
    {
        $now = time();
        $startDate = new \DateTime();
        $startDate->setTimezone(new \DateTimeZone('UTC'));
        $startDate->setTimestamp($now);
        $startDate->modify("midnight +{$daysInAdvance} days");

        $daysInAdvance++;
        $endDate = new \DateTime();
        $endDate->setTimezone(new \DateTimeZone('UTC'));
        $endDate->setTimestamp($now);
        $endDate->modify("midnight +{$daysInAdvance} days");

        $criteria = Criteria::create();
        $expr = Criteria::expr();
        $criteria->where(
            $expr->andX(
                $expr->eq('deleted', 0),
                $expr->gte('startDate', $startDate),
                $expr->lt('startDate', $endDate)
            )
        );
        $offerings = $this->getRepository()->matching($criteria);

        // filter out any offerings belonging to unpublished events
        // and with deleted parentage
        $offerings->filter(function(OfferingInterface $offering) {
            $session = $offering->getSession();
            $course = $session->getCourse();
            $publishEvent = $offering->getSession()->getPublishEvent();
            $school = $course->getSchool();
            return (
                isset($publishEvent)
                && isset($school)
                && ! $session->isDeleted()
                && ! $course->isDeleted()
            );
        });

        return $offerings;
    }
}
