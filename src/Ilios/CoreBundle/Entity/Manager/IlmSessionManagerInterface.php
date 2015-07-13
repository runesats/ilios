<?php

namespace Ilios\CoreBundle\Entity\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Ilios\CoreBundle\Entity\IlmSessionInterface;

/**
 * Interface IlmSessionManagerInterface
 * @package Ilios\CoreBundle\Entity\Manager
 */
interface IlmSessionManagerInterface
{
    /**
     * @param array $criteria
     * @param array $orderBy
     *
     * @return IlmSessionInterface
     */
    public function findIlmSessionBy(
        array $criteria,
        array $orderBy = null
    );

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param integer $limit
     * @param integer $offset
     *
     * @return ArrayCollection|IlmSessionInterface[]
     */
    public function findIlmSessionsBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null
    );

    /**
     * @param IlmSessionInterface $ilmSession
     * @param bool $andFlush
     * @param bool $forceId
     *
     * @return void
     */
    public function updateIlmSession(
        IlmSessionInterface $ilmSession,
        $andFlush = true,
        $forceId = false
    );

    /**
     * @param IlmSessionInterface $ilmSession
     *
     * @return void
     */
    public function deleteIlmSession(
        IlmSessionInterface $ilmSession
    );

    /**
     * @return string
     */
    public function getClass();

    /**
     * @return IlmSessionInterface
     */
    public function createIlmSession();
}
