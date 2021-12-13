<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class User extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof UserInterface
            && in_array($attribute, [self::CREATE, self::VIEW, self::EDIT, self::DELETE]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
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
                return $user->isTheUser($subject) || $user->performsNonLearnerFunction();
                break;
            case self::CREATE:
                return $this->permissionChecker->canCreateUser($user, $subject->getSchool()->getId());
                break;
            case self::EDIT:
                return $this->permissionChecker->canUpdateUser(
                    $user,
                    $subject->getSchool()->getId()
                );
                break;
            case self::DELETE:
                return $this->permissionChecker->canDeleteUser(
                    $user,
                    $subject->getSchool()->getId()
                );
                break;
        }

        return false;
    }
}
