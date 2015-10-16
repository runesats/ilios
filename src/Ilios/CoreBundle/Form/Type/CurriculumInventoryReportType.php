<?php

namespace Ilios\CoreBundle\Form\Type;

use Ilios\CoreBundle\Form\DataTransformer\RemoveMarkupTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CurriculumInventoryReportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['required' => false])
            ->add('description', null, ['required' => false])
            ->add('year')
            ->add('startDate', 'datetime', array(
                'widget' => 'single_text',
            ))
            ->add('endDate', 'datetime', array(
                'widget' => 'single_text',
            ))
            ->add('export', 'tdn_single_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:CurriculumInventoryExport"
            ])
            ->add('sequence', 'tdn_single_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:CurriculumInventorySequence"
            ])
            ->add('sequenceBlocks', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:CurriculumInventorySequenceBlock"
            ])
            ->add('program', 'tdn_single_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:Program"
            ])
            ->add('academicLevels', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:CurriculumInventoryAcademicLevel"
            ])
        ;
        $transformer = new RemoveMarkupTransformer();
        foreach (['name', 'description'] as $element) {
            $builder->get($element)->addViewTransformer($transformer);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ilios\CoreBundle\Entity\CurriculumInventoryReport'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'curriculuminventoryreport';
    }
}
