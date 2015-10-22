<?php

namespace Ilios\CoreBundle\Form\Type;

use Ilios\CoreBundle\Form\DataTransformer\RemoveMarkupTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LearnerGroupType
 * @package Ilios\CoreBundle\Form\Type
 */
class LearnerGroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['empty_data' => null])
            ->add('location', null, ['required' => false, 'empty_data' => null])
            ->add('cohort', 'tdn_single_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:Cohort"
            ])
            ->add('parent', 'tdn_single_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:LearnerGroup"
            ])
            ->add('children', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:LearnerGroup"
            ])
            ->add('ilmSessions', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:IlmSession"
            ])
            ->add('offerings', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:Offering"
            ])
            ->add('instructorGroups', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:InstructorGroup"
            ])
            ->add('users', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:User"
            ])
            ->add('instructors', 'tdn_many_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:User"
            ])
        ;
        $transformer = new RemoveMarkupTransformer();
        foreach (['title', 'location'] as $element) {
            $builder->get($element)->addViewTransformer($transformer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ilios\CoreBundle\Entity\LearnerGroup'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'learnergroup';
    }
}
