<?php

namespace Ilios\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

use Ilios\CoreBundle\Traits\IdentifiableEntity;

/**
 * Class CurriculumInventorySequenceBlockSession
 * @package Ilios\CoreBundle\Entity
 *
 * @ORM\Table(name="curriculum_inventory_sequence_block_session",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="report_session", columns={"sequence_block_id", "session_id"})
 *   },
 *   indexes={
 *     @ORM\Index(name="fkey_curriculum_inventory_sequence_block_session_session_id", columns={"session_id"}),
 *     @ORM\Index(name="IDX_CF8E4F1261D1D223", columns={"sequence_block_id"})
 *   }
 * )
 * @ORM\Entity
 *
 * @JMS\ExclusionPolicy("all")
 */
class CurriculumInventorySequenceBlockSession implements CurriculumInventorySequenceBlockSessionInterface
{
//    use IdentifiableEntity;

    /**
     * @deprecated To be removed in 3.1, replaced by ID by enabling trait.
     * @var int
     *
     * @ORM\Column(name="sequence_block_session_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="count_offerings_once", type="boolean")
     */
    protected $countOfferingsOnce;

    /**
     * @var CurriculumInventorySequenceBlockInterface
     *
     * @ORM\ManyToOne(targetEntity="CurriculumInventorySequenceBlock", inversedBy="sessions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sequence_block_id", referencedColumnName="sequence_block_id")
     * })
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("sequenceBlock")
     */
    protected $sequenceBlock;

    /**
     * @var SessionInterface
     *
     * @ORM\ManyToOne(targetEntity="Session")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="session_id", referencedColumnName="session_id")
     * })
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $session;

    public function __construct()
    {
        //defaults
        $this->countOfferingsOnce = true;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->sequenceBlockSessionId = $id;
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return ($this->id === null) ? $this->sequenceBlockSessionId : $this->id;
    }

    /**
     * @param boolean $countOfferingsOnce
     */
    public function setCountOfferingsOnce($countOfferingsOnce)
    {
        $this->countOfferingsOnce = $countOfferingsOnce;
    }

    /**
     * @return boolean
     */
    public function hasCountOfferingsOnce()
    {
        return $this->countOfferingsOnce;
    }

    /**
     * @param CurriculumInventorySequenceBlockInterface $sequenceBlock
     */
    public function setSequenceBlock(CurriculumInventorySequenceBlockInterface $sequenceBlock)
    {
        $this->sequenceBlock = $sequenceBlock;
    }

    /**
     * @return CurriculumInventorySequenceBlockInterface
     */
    public function getSequenceBlock()
    {
        return $this->sequenceBlock;
    }

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }
}
