<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\SchoolConfigInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SchoolConfig extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof SchoolConfigInterface
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
            self::CREATE, self::EDIT, self::DELETE => $this->permissionChecker->canUpdateSchoolConfig(
                $user,
                $subject->getSchool()->getId()
            ),
            default => false,
        };
    }
}
