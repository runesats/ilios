<?php

namespace Ilios\AuthenticationBundle\Voter\DTO;

use Ilios\CoreBundle\Entity\DTO\LearnerGroupDTO;
use Ilios\AuthenticationBundle\Voter\AbstractVoter;
use Ilios\CoreBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class LearnerGroupDTOVoter
 * @package Ilios\AuthenticationBundle\Voter\DTO
 */
class LearnerGroupDTOVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof LearnerGroupDTO && in_array($attribute, array(self::VIEW));
    }

    /**
     * @param string $attribute
     * @param LearnerGroupDTO $learnerGroupDTO
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $learnerGroupDTO, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return true;
                break;
        }
        return false;
    }
}
