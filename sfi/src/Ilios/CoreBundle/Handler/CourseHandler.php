<?php

namespace Ilios\CoreBundle\Handler;

use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManager;

use Ilios\CoreBundle\Exception\InvalidFormException;
use Ilios\CoreBundle\Form\CourseType;
use Ilios\CoreBundle\Entity\Manager\CourseManager;
use Ilios\CoreBundle\Entity\CourseInterface;

class CourseHandler extends CourseManager
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @param EntityManager $em
     * @param string $class
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(EntityManager $em, $class, FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        parent::__construct($em, $class);
    }

    /**
     * @param array $parameters
     *
     * @return CourseInterface
     */
    public function post(array $parameters)
    {
        $course = $this->createCourse();

        return $this->processForm($course, $parameters, 'POST');
    }

    /**
     * @param CourseInterface $course
     * @param array $parameters
     *
     * @return CourseInterface
     */
    public function put(CourseInterface $course, array $parameters)
    {
        return $this->processForm($course, $parameters, 'PUT');
    }

    /**
     * @param CourseInterface $course
     * @param array $parameters
     *
     * @return CourseInterface
     */
    public function patch(CourseInterface $course, array $parameters)
    {
        return $this->processForm($course, $parameters, 'PATCH');
    }

    /**
     * @param CourseInterface $course
     * @param array $parameters
     * @param string $method
     * @throws InvalidFormException when invalid form data is passed in.
     *
     * @return CourseInterface
     */
    protected function processForm(CourseInterface $course, array $parameters, $method = "PUT")
    {
        $form = $this->formFactory->create(new CourseType(), $course, array('method' => $method));
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {
            $course = $form->getData();
            $this->updateCourse($course, true);

            return $course;
        }

        throw new InvalidFormException('Invalid submitted data', $form);
    }
}
