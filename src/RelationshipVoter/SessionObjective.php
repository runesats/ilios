<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\SessionInterface;
use App\Entity\SessionObjectiveInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class Objective
 */
class SessionObjective extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof SessionObjectiveInterface && in_array($attribute, array(
                self::VIEW, self::CREATE, self::EDIT, self::DELETE
            ));
    }

    /**
     * @param string $attribute
     * @param SessionObjectiveInterface $objective
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $objective, TokenInterface $token)
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
            case self::EDIT:
            case self::DELETE:
                /* @var SessionInterface $session */
                $session = $objective->getSession();
                return $this->permissionChecker->canUpdateSession($user, $session);
                break;
        }

        return false;
    }
}
