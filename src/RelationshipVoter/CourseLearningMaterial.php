<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\CourseLearningMaterialInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CourseLearningMaterial extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof CourseLearningMaterialInterface
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
            self::VIEW => $user->performsNonLearnerFunction(),
            self::EDIT, self::CREATE, self::DELETE => $this->permissionChecker->canUpdateCourse(
                $user,
                $subject->getCourse()
            ),
            default => false,
        };
    }
}
