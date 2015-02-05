<?php

namespace Ilios\CoreBundle\Entity\LearningMaterials;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

use Ilios\CoreBundle\Entity\LearningMaterial;

/**
 * Class Citation
 * @package Ilios\CoreBundle\Entity\LearningMaterials
 *
 * @ORM\Entity
 *
 * @JMS\ExclusionPolicy("all")
 */
class Citation extends LearningMaterial implements CitationInterface
{
    /**
     * renamed from citation
     * @var string
     *
     * @ORM\Column(name="citation", type="string", length=512, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $text;

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->setType(self::TYPE_CITATION);
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
