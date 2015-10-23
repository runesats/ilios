<?php

namespace Ilios\CoreBundle\Form\Type;

use Ilios\CoreBundle\Form\DataTransformer\RemoveMarkupTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MeshDescriptorType
 * @package Ilios\CoreBundle\Form\Type
 */
class MeshDescriptorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, ['empty_data' => null])
            ->add('name', null, ['empty_data' => null])
            ->add('annotation', null, ['required' => false, 'empty_data' => null])
            ->add('courses', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:Course"
            ])
            ->add('objectives', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:Objective"
            ])
            ->add('sessions', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:Session"
            ])
            ->add('concepts', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:MeshConcept"
            ])
            ->add('qualifiers', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:MeshQualifier"
            ])
            ->add('sessionLearningMaterials', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:SessionLearningMaterial"
            ])
            ->add('courseLearningMaterials', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:CourseLearningMaterial"
            ])
            ->add('previousIndexing', 'tdn_single_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:MeshPreviousIndexing"
            ])
        ;
        $transformer = new RemoveMarkupTransformer();
        foreach (['id', 'name', 'annotation'] as $element) {
            $builder->get($element)->addViewTransformer($transformer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ilios\CoreBundle\Entity\MeshDescriptor'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'meshdescriptor';
    }
}
