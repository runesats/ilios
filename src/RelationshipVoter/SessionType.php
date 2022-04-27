<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\SessionTypeInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SessionType extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof SessionTypeInterface
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
        return match ($attribute) {
            self::VIEW => true,
            self::CREATE => $this->permissionChecker->canCreateSessionType($user, $subject->getSchool()->getId()),
            self::EDIT => $this->permissionChecker->canUpdateSessionType(
                $user,
                $subject->getSchool()->getId()
            ),
            self::DELETE => $this->permissionChecker->canDeleteSessionType(
                $user,
                $subject->getSchool()->getId()
            ),
            default => false,
        };
    }
}
