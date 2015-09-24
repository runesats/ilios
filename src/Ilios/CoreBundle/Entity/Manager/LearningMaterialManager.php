<?php

namespace Ilios\CoreBundle\Entity\Manager;

use Doctrine\ORM\Id\AssignedGenerator;
use Ilios\CoreBundle\Entity\LearningMaterialInterface;

/**
 * Class LearningMaterialManager
 * @package Ilios\CoreBundle\Entity\Manager
 */
class LearningMaterialManager extends AbstractManager implements LearningMaterialManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function findLearningMaterialBy(
        array $criteria,
        array $orderBy = null
    ) {
        return $this->getRepository()->findOneBy($criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function findLearningMaterialsBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function updateLearningMaterial(
        LearningMaterialInterface $learningMaterial,
        $andFlush = true,
        $forceId = false
    ) {
        $this->em->persist($learningMaterial);

        if ($forceId) {
            $metadata = $this->em->getClassMetaData(get_class($learningMaterial));
            $metadata->setIdGenerator(new AssignedGenerator());
        }

        if ($andFlush) {
            $this->em->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteLearningMaterial(
        LearningMaterialInterface $learningMaterial
    ) {
        $this->em->remove($learningMaterial);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function createLearningMaterial()
    {
        $class = $this->getClass();
        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function findFileLearningMaterials()
    {
        return $this->getRepository()->findFileLearningMaterials();
    }
}
