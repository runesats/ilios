<?php

namespace Ilios\AuthenticationBundle\Voter;

use Ilios\CoreBundle\Entity\Manager\PermissionManagerInterface;
use Ilios\CoreBundle\Entity\ProgramYearStewardInterface;
use Ilios\CoreBundle\Entity\UserInterface;

/**
 * Class ProgramYearStewardVoter
 * @package Ilios\AuthenticationBundle\Voter
 */
class ProgramYearStewardVoter extends AbstractVoter
{
    /**
     * @var PermissionManagerInterface
     */
    protected $permissionManager;

    /**
     * @param PermissionManagerInterface $permissionManager
     */
    public function __construct(PermissionManagerInterface $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return array('Ilios\CoreBundle\Entity\ProgramYearStewardInterface');
    }

    /**
     * @param string $attribute
     * @param ProgramYearStewardInterface $steward
     * @param UserInterface|null $user
     * @return bool
     */
    protected function isGranted($attribute, $steward, $user = null)
    {
        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                // the given user is granted VIEW permissions on the given steward
                // when at least one of the following statements is true
                // 1. The user's primary school is the same as the parent program's owning school
                //    and the user has at least one of 'Course Director', 'Faculty' and 'Developer' role.
                // 2. The user has READ permissions on the parent program's owning school
                //    and the user has at least one of 'Course Director', 'Faculty' and 'Developer' role.
                // 3. The user's primary school matches the stewarding school
                //    and the user has at least one of 'Course Director', 'Faculty' and 'Developer' role.
                // 4. The user has READ permissions on the owning program.
                return (
                    ($this->userHasRole($user, ['Course Director', 'Developer', 'Faculty'])
                        && ($steward->getProgramYear()->getProgram()->getSchool()->getId()
                            === $user->getSchool()->getId()
                            || $this->permissionManager->userHasReadPermissionToSchool(
                                $user,
                                $steward->getProgramYear()->getProgram()->getSchool()
                            )
                            || $steward->getSchool()->getId() === $user->getSchool()->getId()
                        )
                    )
                    || $this->permissionManager->userHasReadPermissionToProgram(
                        $user,
                        $steward->getProgramYear()->getProgram()
                    )
                );
                break;
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                // the given user is granted CREATE, EDIT and DELETE permissions on the given steward
                // when at least one of the following statements is true
                // 1. The user's primary school is the same as the parent program's owning school
                //    and the user has at least one of 'Course Director' and 'Developer' role.
                // 2. The user has WRITE permissions on the parent program's owning school
                //    and the user has at least one of 'Course Director' and 'Developer' role.
                // 3. The user's primary school matches the stewarding school
                //    and the user has at least one of 'Course Director' and 'Developer' role.
                // 4. The user has WRITE permissions on the parent program.
                return (
                    ($this->userHasRole($user, ['Course Director', 'Developer'])
                        && ($steward->getProgramYear()->getProgram()->getSchool()->getId()
                            === $user->getSchool()->getId()
                            || $this->permissionManager->userHasWritePermissionToSchool(
                                $user,
                                $steward->getProgramYear()->getProgram()->getSchool()
                            )
                            || $steward->getSchool()->getId() === $user->getSchool()->getId()
                        )
                    )
                    || $this->permissionManager->userHasWritePermissionToProgram(
                        $user,
                        $steward->getProgramYear()->getProgram()
                    )
                );
                break;
        }

        return false;
    }
}
