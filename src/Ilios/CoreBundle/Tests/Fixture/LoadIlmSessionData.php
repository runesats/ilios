<?php

namespace Ilios\CoreBundle\Tests\Fixture;

use Ilios\CoreBundle\Entity\IlmSessionFacet;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadIlmSessionData extends AbstractFixture implements
    FixtureInterface,
    DependentFixtureInterface,
    ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $data = $this->container
            ->get('ilioscore.dataloader.ilmSession')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new IlmSessionFacet();
            $entity->setId($arr['id']);
            $entity->setHours($arr['hours']);
            $entity->setDueDate(new \DateTime($arr['dueDate']));
            foreach ($arr['instructors'] as $id) {
                $entity->addInstructor($this->getReference('users' . $id));
            }
            foreach ($arr['instructorGroups'] as $id) {
                $entity->addInstructorGroup($this->getReference('instructorGroups' . $id));
            }
            foreach ($arr['learnerGroups'] as $id) {
                $entity->addLearnerGroup($this->getReference('learnerGroups' . $id));
            }
            foreach ($arr['learners'] as $id) {
                $entity->addLearner($this->getReference('users' . $id));
            }
            $manager->persist($entity);
            $this->addReference('ilmSessions' . $arr['id'], $entity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            'Ilios\CoreBundle\Tests\Fixture\LoadUserData',
            'Ilios\CoreBundle\Tests\Fixture\LoadInstructorGroupData',
            'Ilios\CoreBundle\Tests\Fixture\LoadLearnerGroupData',
        );
    }
}
