<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\DTO\LearningMaterialDTO;
use App\Entity\LearningMaterial;
use App\Service\CurriculumInventoryReportDecoratorFactory;
use App\Service\LearningMaterialDecoratorFactory;
use Exception;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Applies a factory to decorate the entity or DTO before it is sent
 */
class FactoryNormalizer implements ContextAwareNormalizerInterface, NormalizationAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'FACTORY_NORMALIZER_ALREADY_CALLED';
    protected LearningMaterialDecoratorFactory $learningMaterialDecoratorFactory;
    protected CurriculumInventoryReportDecoratorFactory $curriculumInventoryReportDecoratorFactory;

    public function __construct(
        LearningMaterialDecoratorFactory $learningMaterialDecoratorFactory,
        CurriculumInventoryReportDecoratorFactory $curriculumInventoryReportDecoratorFactory
    ) {
        $this->learningMaterialDecoratorFactory = $learningMaterialDecoratorFactory;
        $this->curriculumInventoryReportDecoratorFactory = $curriculumInventoryReportDecoratorFactory;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $class = get_class($object);
        switch ($class) {
            case LearningMaterial::class:
            case LearningMaterialDTO::class:
                $object = $this->learningMaterialDecoratorFactory->create($object);
                break;
            default:
                throw new Exception("${class} fell through switch statement, should it have been decorated?");
        }

        $context[self::ALREADY_CALLED] = true;
        return $this->normalizer->normalize($object, $format, $context);
    }

    /*
     * Since we call upon the normalizer chain here we have to avoid recursion by examining
     * the context to avoid calling ourselves again.
     */
    public function supportsNormalization($classNameOrObject, string $format = null, array $context = [])
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        if (!in_array($format, ['json', 'json-api'])) {
            return false;
        }

        $decoratedTypes = [
            LearningMaterial::class,
            LearningMaterialDTO::class,
        ];
        $class = is_object($classNameOrObject) ? get_class($classNameOrObject) : $classNameOrObject;
        return in_array($class, $decoratedTypes);
    }
}
