<?php

namespace Ilios\CoreBundle\Form\Type;

use Ilios\CoreBundle\Form\DataTransformer\RemoveMarkupTransformer;
use Ilios\CoreBundle\Form\Type\AbstractType\SingleRelatedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CurriculumInventoryReportType
 * @package Ilios\CoreBundle\Form\Type
 */
class CurriculumInventoryReportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['required' => false, 'empty_data' => null])
            ->add('description', null, ['required' => false, 'empty_data' => null])
            ->add('year')
            ->add('startDate', DateTimeType::class, array(
                'widget' => 'single_text',
            ))
            ->add('endDate', DateTimeType::class, array(
                'widget' => 'single_text',
            ))
            ->add('export', SingleRelatedType::class, [
                'required' => false,
                'entityName' => "IliosCoreBundle:CurriculumInventoryExport"
            ])
            ->add('sequence', SingleRelatedType::class, [
                'required' => false,
                'entityName' => "IliosCoreBundle:CurriculumInventorySequence"
            ])
            ->add('program', SingleRelatedType::class, [
                'required' => false,
                'entityName' => "IliosCoreBundle:Program"
            ])
        ;
        $transformer = new RemoveMarkupTransformer();
        foreach (['name', 'description'] as $element) {
            $builder->get($element)->addViewTransformer($transformer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ilios\CoreBundle\Entity\CurriculumInventoryReport'
        ));
    }
}
