<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\LearnerGroupInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LearnerGroup extends AbstractVoter
{
    protected function supports($attribute, $subject)
    {
        return $subject instanceof LearnerGroupInterface
            && in_array($attribute, [self::CREATE, self::VIEW, self::EDIT, self::DELETE]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof SessionUserInterface) {
            return false;
        }
        if ($user->isRoot()) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->permissionChecker->canViewLearnerGroup($user, $subject->getId());
                break;
            case self::CREATE:
                return $this->permissionChecker->canCreateLearnerGroup($user, $subject->getSchool()->getId());
                break;
            case self::EDIT:
                return $this->permissionChecker->canUpdateLearnerGroup(
                    $user,
                    $subject->getSchool()->getId()
                );
                break;
            case self::DELETE:
                return $this->permissionChecker->canDeleteLearnerGroup(
                    $user,
                    $subject->getSchool()->getId()
                );
                break;
        }

        return false;
    }
}
