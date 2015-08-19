<?php

namespace Ilios\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use Ilios\CoreBundle\Traits\IdentifiableEntity;
use Ilios\CoreBundle\Traits\TitledEntity;
use Ilios\CoreBundle\Traits\SessionsEntity;

/**
 * SessionType
 *
 * @ORM\Table(name="session_type",
 *   indexes={
 *     @ORM\Index(name="school_id", columns={"school_id"}),
 *     @ORM\Index(name="assessment_option_fkey", columns={"assessment_option_id"})
 *   }
 * )
 * @ORM\Entity
 *
 * @JMS\ExclusionPolicy("all")
 * @JMS\AccessType("public_method")
 */
class SessionType implements SessionTypeInterface
{
    use IdentifiableEntity;
    use TitledEntity;
    use SessionsEntity;

    /**
     * @var integer
     *
     * @ORM\Column(name="session_type_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Assert\Type(type="integer")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 100
     * )
     *
     * @JMS\Expose
     * @JMS\Type("string")
    */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="session_type_css_class", type="string", length=64, nullable=true)
     *
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 64
     * )
     */
    protected $sessionTypeCssClass;

    /**
     * @var boolean
     *
     * @ORM\Column(name="assessment", type="boolean")
     *
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     */
    protected $assessment;

    /**
     * @var AssessmentOptionInterface
     *
     * @ORM\ManyToOne(targetEntity="AssessmentOption", inversedBy="sessionTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="assessment_option_id", referencedColumnName="assessment_option_id")
     * })
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("assessmentOption")
     */
    protected $assessmentOption;

    /**
     * @var SchoolInterface
     *
     * @ORM\ManyToOne(targetEntity="School", inversedBy="sessionTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="school_id", referencedColumnName="school_id")
     * })
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("school")
     */
    protected $school;

    /**
     * @var ArrayCollection|AamcMethodInterface[]
     *
     * @ORM\ManyToMany(targetEntity="AamcMethod", inversedBy="sessionTypes")
     * @ORM\JoinTable(name="session_type_x_aamc_method",
     *   joinColumns={
     *     @ORM\JoinColumn(name="session_type_id", referencedColumnName="session_type_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="method_id", referencedColumnName="method_id")
     *   }
     * )
     *
     * @JMS\Expose
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("aamcMethods")
     */
    protected $aamcMethods;

    /**
     * @var ArrayCollection|SessionInterface[]
     *
     * @ORM\OneToMany(targetEntity="Session", mappedBy="sessionType")
     *
     * @JMS\Expose
     * @JMS\Type("array<string>")
     */
    protected $sessions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->aamcMethods = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->assessment = false;
    }

    /**
     * @param string $sessionTypeCssClass
     */
    public function setSessionTypeCssClass($sessionTypeCssClass)
    {
        $this->sessionTypeCssClass = $sessionTypeCssClass;
    }

    /**
     * @return string
     */
    public function getSessionTypeCssClass()
    {
        return $this->sessionTypeCssClass;
    }

    /**
     * Set assessment
     *
     * @param boolean $assessment
     */
    public function setAssessment($assessment)
    {
        $this->assessment = $assessment;
    }

    /**
     * Get assessment
     *
     * @return boolean
     */
    public function isAssessment()
    {
        return $this->assessment;
    }

    /**
     * @param AssessmentOptionInterface $assessmentOption
     */
    public function setAssessmentOption(AssessmentOptionInterface $assessmentOption)
    {
        $this->assessmentOption = $assessmentOption;
    }

    /**
     * @return AssessmentOptionInterface
     */
    public function getAssessmentOption()
    {
        return $this->assessmentOption;
    }

    /**
     * @param SchoolInterface $school
     */
    public function setSchool(SchoolInterface $school)
    {
        $this->school = $school;
    }

    /**
     * @return SchoolInterface
     */
    public function getSchool()
    {
        if ($this->school && !$this->school->isDeleted()) {
            return $this->school;
        }
        
        return null;
    }

    /**
     * @param Collection $aamcMethods
     */
    public function setAamcMethods(Collection $aamcMethods)
    {
        $this->aamcMethods = new ArrayCollection();

        foreach ($aamcMethods as $aamcMethod) {
            $this->addAamcMethod($aamcMethod);
        }
    }

    /**
     * @param AamcMethodInterface $aamcMethod
     */
    public function addAamcMethod(AamcMethodInterface $aamcMethod)
    {
        $this->aamcMethods->add($aamcMethod);
    }

    /**
     * @return ArrayCollection|AamcMethodInterface[]
     */
    public function getAamcMethods()
    {
        return $this->aamcMethods;
    }

    /**
    * @return string
    */
    public function __toString()
    {
        return (string) $this->id;
    }
}
