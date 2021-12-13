<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\TermInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Term extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof TermInterface
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
                return true;
                break;
            case self::CREATE:
                return $this->permissionChecker->canCreateTerm(
                    $user,
                    $subject->getVocabulary()->getSchool()->getId()
                );
                break;
            case self::EDIT:
                return $this->permissionChecker->canUpdateTerm(
                    $user,
                    $subject->getVocabulary()->getSchool()->getId()
                );
                break;
            case self::DELETE:
                return $this->permissionChecker->canDeleteTerm(
                    $user,
                    $subject->getVocabulary()->getSchool()->getId()
                );
                break;
        }

        return false;
    }
}
