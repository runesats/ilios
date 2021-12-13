<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\CurriculumInventorySequenceBlockInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CurriculumInventorySequenceBlock extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof CurriculumInventorySequenceBlockInterface
            && in_array($attribute, [self::CREATE, self::VIEW, self::EDIT, self::DELETE]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof SessionUserInterface) {
            return false;
        }

        if (self::VIEW === $attribute) {
            return true;
        }

        if ($subject->getReport()->getExport()) {
            return false;
        }

        if ($user->isRoot()) {
            return true;
        }

        return $this->permissionChecker->canUpdateCurriculumInventoryReport(
            $user,
            $subject->getReport()->getId(),
            $subject->getReport()->getSchool()->getId()
        );
    }
}
