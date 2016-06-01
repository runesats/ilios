<?php

namespace Ilios\AuthenticationBundle\Voter;

use Ilios\CoreBundle\Entity\InstructorGroupInterface;
use Ilios\CoreBundle\Entity\Manager\PermissionManager;
use Ilios\CoreBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class InstructorGroupVoter
 * @package Ilios\AuthenticationBundle\Voter
 */
class InstructorGroupVoter extends AbstractVoter
{
    /**
     * @var PermissionManager
     */
    protected $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof InstructorGroupInterface && in_array($attribute, array(
            self::VIEW, self::CREATE, self::EDIT, self::DELETE
        ));
    }

    /**
     * @param string $attribute
     * @param InstructorGroupInterface $group
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $group, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                // unlike instructor details, instructor groups
                // should be visible to any authenticated user in the system.
                // do not enforce any special permissions for viewing them.
                return true;
                break;
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                // grant CREATE, EDIT and DELETE privileges if at least one of the following
                // statements is true:
                // 1. the user's primary school is the group's owning school
                //    and the user has at least one of the 'Course Director' and 'Developer' roles.
                // 2. the user has WRITE rights on the group's owning school via the permissions system
                //    and the user has at least one of the 'Course Director' and 'Developer' roles.
                return (
                    $this->userHasRole($user, ['Course Director', 'Developer'])
                    && (
                        $this->schoolsAreIdentical($user->getSchool(), $group->getSchool())
                        || $this->permissionManager->userHasWritePermissionToSchool($user, $group->getSchool()->getId())
                    )
                );
                break;
        }
        return false;
    }
}
