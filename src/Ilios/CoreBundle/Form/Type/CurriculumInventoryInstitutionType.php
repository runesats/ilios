<?php

namespace Ilios\CoreBundle\Form\Type;

use Ilios\CoreBundle\Form\DataTransformer\RemoveMarkupTransformer;
use Ilios\CoreBundle\Form\Type\AbstractType\SingleRelatedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CurriculumInventoryInstitutionType
 * @package Ilios\CoreBundle\Form\Type
 */
class CurriculumInventoryInstitutionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new RemoveMarkupTransformer();
        $elements = [
            'name',
            'aamcCode',
            'addressStreet',
            'addressCity',
            'addressStateOrProvince',
            'addressZipCode',
            'addressCountryCode',
        ];
        foreach ($elements as $element) {
            $builder->add($element, null, ['empty_data' => null]);
            $builder->get($element)->addViewTransformer($transformer);
        }
        $builder
            ->add('school', SingleRelatedType::class, [
                'required' => true,
                'entityName' => "IliosCoreBundle:School"
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ilios\CoreBundle\Entity\CurriculumInventoryInstitution'
        ));
    }
}
