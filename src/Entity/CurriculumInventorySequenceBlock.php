<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Traits\SessionsEntity;
use App\Annotation as IS;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Traits\DescribableEntity;
use App\Traits\IdentifiableEntity;
use App\Traits\TitledEntity;
use App\Traits\StringableIdEntity;
use App\Repository\CurriculumInventorySequenceBlockRepository;

/**
 * Class CurriculumInventorySequenceBlock
 * @IS\Entity
 */
#[ORM\Table(name: 'curriculum_inventory_sequence_block')]
#[ORM\Entity(repositoryClass: CurriculumInventorySequenceBlockRepository::class)]
class CurriculumInventorySequenceBlock implements CurriculumInventorySequenceBlockInterface
{
    use IdentifiableEntity;
    use DescribableEntity;
    use TitledEntity;
    use StringableIdEntity;
    use SessionsEntity;

    /**
     * @var int
     * @Assert\Type(type="integer")
     * @IS\Expose
     * @IS\Type("integer")
     * @IS\ReadOnly
     */
    #[ORM\Column(name: 'sequence_block_id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 200
     * )
     * @IS\Expose
     * @IS\Type("string")
     */
    #[ORM\Column(type: 'string', length: 200)]
    protected $title;

    /**
     * @var string
     * @Assert\Type(type="string")
     * @Assert\AtLeastOneOf({
     *     @Assert\Blank,
     *     @Assert\Length(min=1,max=65000)
     * })
     * @IS\Expose
     * @IS\Type("string")
     */
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    protected $description;

    /**
     * @var int
     * @Assert\NotNull()
     * @Assert\Type(type="integer")
     * @IS\Expose
     * @IS\Type("integer")
     */
    #[ORM\Column(name: 'required', type: 'integer')]
    protected $required;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\Range(
     *      min = 1,
     *      max = 3,
     * )
     * @IS\Expose
     * @IS\Type("integer")
     */
    #[ORM\Column(name: 'child_sequence_order', type: 'smallint')]
    protected $childSequenceOrder;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @IS\Expose
     * @IS\Type("integer")
     */
    #[ORM\Column(name: 'order_in_sequence', type: 'integer')]
    protected $orderInSequence;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @IS\Expose
     * @IS\Type("integer")
     */
    #[ORM\Column(name: 'minimum', type: 'integer')]
    protected $minimum;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @IS\Expose
     * @IS\Type("integer")
     */
    #[ORM\Column(name: 'maximum', type: 'integer')]
    protected $maximum;

    /**
     * @var bool
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     * @IS\Expose
     * @IS\Type("boolean")
     * this field is currently tinyint data type in the db but used like a boolean
     */
    #[ORM\Column(name: 'track', type: 'boolean')]
    protected $track;

    /**
     * @var \DateTime
     * @IS\Expose
     * @IS\Type("dateTime")
     */
    #[ORM\Column(name: 'start_date', type: 'date', nullable: true)]
    protected $startDate;

    /**
     * @var \DateTime
     * @IS\Expose
     * @IS\Type("dateTime")
     */
    #[ORM\Column(name: 'end_date', type: 'date', nullable: true)]
    protected $endDate;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @IS\Expose
     * @IS\Type("integer")
     */
    #[ORM\Column(name: 'duration', type: 'integer')]
    protected $duration;

    /**
     * @var CurriculumInventoryAcademicLevelInterface
     *     name="academic_level_id",
     *     referencedColumnName="academic_level_id",
     *     nullable=false,
     *     onDelete="cascade"
     *   )
     * })
     * @IS\Expose
     * @IS\Type("entity")
     */
    #[ORM\ManyToOne(targetEntity: 'CurriculumInventoryAcademicLevel', inversedBy: 'sequenceBlocks')]
    #[ORM\JoinColumn(
        name: 'academic_level_id',
        referencedColumnName: 'academic_level_id',
        nullable: false,
        onDelete: 'cascade'
    )]
    protected $academicLevel;

    /**
     * @var CourseInterface
     * })
     * @IS\Expose
     * @IS\Type("entity")
     */
    #[ORM\ManyToOne(targetEntity: 'Course', inversedBy: 'sequenceBlocks')]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'course_id')]
    protected $course;

    /**
     * @var CurriculumInventorySequenceBlockInterface
     *     name="parent_sequence_block_id",
     *     referencedColumnName="sequence_block_id",
     *     onDelete="cascade"
     *   )
     * })
     * @IS\Expose
     * @IS\Type("entity")
     */
    #[ORM\ManyToOne(targetEntity: 'CurriculumInventorySequenceBlock', inversedBy: 'children')]
    #[ORM\JoinColumn(
        name: 'parent_sequence_block_id',
        referencedColumnName: 'sequence_block_id',
        onDelete: 'cascade'
    )]
    protected $parent;

    /**
     * @var ArrayCollection|CurriculumInventorySequenceBlockInterface[]
     * @IS\Expose
     * @IS\Type("entityCollection")
     */
    #[ORM\OneToMany(targetEntity: 'CurriculumInventorySequenceBlock', mappedBy: 'parent')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $children;

    /**
     * @var CurriculumInventoryReportInterface
     * @Assert\NotNull()
     * })
     * @IS\Expose
     * @IS\Type("entity")
     */
    #[ORM\ManyToOne(targetEntity: 'CurriculumInventoryReport', inversedBy: 'sequenceBlocks')]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'report_id', onDelete: 'cascade')]
    protected $report;

    /**
     * @var ArrayCollection|SessionInterface[]
     * @IS\Expose
     * @IS\Type("entityCollection")
     */
    #[ORM\ManyToMany(targetEntity: 'Session', inversedBy: 'sequenceBlocks')]
    #[ORM\JoinTable('curriculum_inventory_sequence_block_x_session')]
    #[ORM\JoinColumn(name: 'sequence_block_id', referencedColumnName: 'sequence_block_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'session_id', referencedColumnName: 'session_id', onDelete: 'CASCADE')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $sessions;

    /**
     * @var ArrayCollection|SessionInterface[]
     * @IS\Expose
     * @IS\Type("entityCollection")
     */
    #[ORM\ManyToMany(targetEntity: 'Session', inversedBy: 'excludedSequenceBlocks')]
    #[ORM\JoinTable('curriculum_inventory_sequence_block_x_excluded_session')]
    #[ORM\JoinColumn(name: 'sequence_block_id', referencedColumnName: 'sequence_block_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'session_id', referencedColumnName: 'session_id', onDelete: 'CASCADE')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $excludedSessions;


    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->excludedSessions = new ArrayCollection();
        $this->required = self::OPTIONAL;
        $this->track = false;
    }

    /**
     * @param int $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return int
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param int $childSequenceOrder
     */
    public function setChildSequenceOrder($childSequenceOrder)
    {
        $this->childSequenceOrder = $childSequenceOrder;
    }

    /**
     * @return int $childSequenceOrder
     */
    public function getChildSequenceOrder()
    {
        return $this->childSequenceOrder;
    }

    /**
     * @param int $orderInSequence
     */
    public function setOrderInSequence($orderInSequence)
    {
        $this->orderInSequence = $orderInSequence;
    }

    /**
     * @return int
     */
    public function getOrderInSequence()
    {
        return $this->orderInSequence;
    }

    /**
     * @param int $minimum
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;
    }

    /**
     * @return int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * @param int $maximum
     */
    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;
    }

    /**
     * @return int
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * @param bool $track
     */
    public function setTrack($track)
    {
        $this->track = $track;
    }

    /**
     * @return bool
     */
    public function hasTrack()
    {
        return $this->track;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param CurriculumInventoryAcademicLevelInterface $academicLevel
     */
    public function setAcademicLevel(CurriculumInventoryAcademicLevelInterface $academicLevel)
    {
        $this->academicLevel = $academicLevel;
    }

    /**
     * @return CurriculumInventoryAcademicLevelInterface
     */
    public function getAcademicLevel()
    {
        return $this->academicLevel;
    }

    /**
     * @inheritdoc
     */
    public function setCourse(CourseInterface $course = null)
    {
        $this->course = $course;
    }

    /**
     * @inheritdoc
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param CurriculumInventorySequenceBlockInterface $parent
     */
    public function setParent(CurriculumInventorySequenceBlockInterface $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return CurriculumInventorySequenceBlockInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Collection $children
     */
    public function setChildren(Collection $children)
    {
        $this->children = new ArrayCollection();

        foreach ($children as $child) {
            $this->addChild($child);
        }
    }

    /**
     * @param CurriculumInventorySequenceBlockInterface $child
     */
    public function addChild(CurriculumInventorySequenceBlockInterface $child)
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
        }
    }

    /**
     * @param CurriculumInventorySequenceBlockInterface $child
     */
    public function removeChild(CurriculumInventorySequenceBlockInterface $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * @return ArrayCollection|CurriculumInventorySequenceBlockInterface[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param CurriculumInventoryReportInterface $report
     */
    public function setReport(CurriculumInventoryReportInterface $report)
    {
        $this->report = $report;
    }

    /**
     * @return CurriculumInventoryReportInterface
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenAsSortedList()
    {
        $children = $this->getChildren()->toArray();
        $sortStrategy = $this->getChildSequenceOrder();
        switch ($sortStrategy) {
            case self::ORDERED:
                usort($children, [__CLASS__, 'compareSequenceBlocksWithOrderedStrategy']);
                break;
            case self::UNORDERED:
            case self::PARALLEL:
            default:
                usort($children, [__CLASS__, 'compareSequenceBlocksWithDefaultStrategy']);
                break;
        }
        return $children;
    }

    /**
     * Callback function for comparing sequence blocks.
     * The applied criterion for comparison is the </pre>"orderInSequence</pre> property.
     *
     * @param CurriculumInventorySequenceBlockInterface $a
     * @param CurriculumInventorySequenceBlockInterface $b
     * @return int One of -1, 0, 1.
     */
    public static function compareSequenceBlocksWithOrderedStrategy(
        CurriculumInventorySequenceBlockInterface $a,
        CurriculumInventorySequenceBlockInterface $b
    ) {
        if ($a->getOrderInSequence() === $b->getOrderInSequence()) {
            return 0;
        }
        return ($a->getOrderInSequence() > $b->getOrderInSequence()) ? 1 : -1;
    }

    /**
     * Callback function for comparing sequence blocks.
     * The applied, ranked criteria for comparison are:
     * 1. "academic level"
     *      Numeric sort, ascending.
     * 2. "start date"
     *      Numeric sort on timestamps, ascending. NULL values will be treated as unix timestamp 0.
     * 3. "title"
     *    Alphabetical sort.
     * 4. "sequence block id"
     *    A last resort. Numeric sort, ascending.
     *
     * @param CurriculumInventorySequenceBlockInterface $a
     * @param CurriculumInventorySequenceBlockInterface $b
     * @return int One of -1, 0, 1.
     */
    public static function compareSequenceBlocksWithDefaultStrategy(
        CurriculumInventorySequenceBlockInterface $a,
        CurriculumInventorySequenceBlockInterface $b
    ) {
        // 1. academic level id
        if ($a->getAcademicLevel()->getLevel() > $b->getAcademicLevel()->getLevel()) {
            return 1;
        } elseif ($a->getAcademicLevel()->getLevel() < $b->getAcademicLevel()->getLevel()) {
            return -1;
        }

        // 2. start date
        $startDateA = $a->getStartDate() ? $a->getStartDate()->getTimestamp() : 0;
        $startDateB = $b->getStartDate() ? $b->getStartDate()->getTimestamp() : 0;

        if ($startDateA > $startDateB) {
            return 1;
        } elseif ($startDateA < $startDateB) {
            return -1;
        }

        // 3. title comparison
        $n = strcasecmp($a->getTitle(), $b->getTitle());
        if ($n) {
            return $n > 0 ? 1 : -1;
        }

        // 4. sequence block id comparison
        if ($a->getId() > $b->getId()) {
            return 1;
        } elseif ($a->getId() < $b->getId()) {
            return -1;
        }
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function setExcludedSessions(Collection $sessions)
    {
        $this->excludedSessions = new ArrayCollection();

        foreach ($sessions as $session) {
            $this->addExcludedSession($session);
        }
    }

    /**
     * @inheritdoc
     */
    public function addExcludedSession(SessionInterface $session)
    {
        if (!$this->excludedSessions->contains($session)) {
            $this->excludedSessions->add($session);
        }
    }

    /**
     * @inheritdoc
     */
    public function removeExcludedSession(SessionInterface $session)
    {
        $this->excludedSessions->removeElement($session);
    }

    /**
     * @inheritdoc
     */
    public function getExcludedSessions()
    {
        return $this->excludedSessions;
    }
}
