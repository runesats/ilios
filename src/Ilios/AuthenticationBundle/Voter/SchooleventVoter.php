<?php

namespace Ilios\AuthenticationBundle\Voter;

use Ilios\CoreBundle\Classes\SchoolEvent;
use Ilios\CoreBundle\Entity\Manager\PermissionManager;
use Ilios\CoreBundle\Entity\Manager\SchoolManager;
use Ilios\CoreBundle\Entity\SchoolInterface;
use Ilios\CoreBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class SchoolVoter
 * @package Ilios\AuthenticationBundle\Voter
 */
class SchooleventVoter extends AbstractVoter
{
    /**
     * @var PermissionManager
     */
    protected $permissionManager;

    /**
     * @var SchoolManager
     */
    protected $schoolManager;

    /**
     * @param PermissionManager $permissionManager
     * @param SchoolManager $schoolManager
     */
    public function __construct(PermissionManager $permissionManager, SchoolManager $schoolManager)
    {
        $this->permissionManager = $permissionManager;
        $this->schoolManager = $schoolManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof SchoolEvent && in_array($attribute, array(self::VIEW));
    }

    /**
     * @param string $attribute
     * @param SchoolEvent $event
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $event, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                // grant VIEW permissions if the event-owning school matches any of the given user's schools.
                // In addition, if the given user has NOT elevated privileges,
                // then do not grant access to view un-published events.
                /* @var SchoolInterface $eventOwningSchool */
                $eventOwningSchool = $this->schoolManager->findOneBy(['id' => $event->school]);
                if ($this->userHasRole($user, ['Faculty', 'Course Director', 'Developer'])) {
                    return $this->schoolsAreIdentical($eventOwningSchool, $user->getSchool())
                    || $this->permissionManager->userHasReadPermissionToSchool($user, $eventOwningSchool->getId());
                } else {
                    return ((
                            $this->schoolsAreIdentical($eventOwningSchool, $user->getSchool())
                            || $this->permissionManager->userHasReadPermissionToSchool(
                                $user,
                                $eventOwningSchool->getId()
                            )
                        ) && $event->isPublished);
                }
                break;
        }
        return false;
    }
}
