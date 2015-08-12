<?php

namespace Ilios\AuthenticationBundle\Voter;

use Ilios\CoreBundle\Entity\Manager\UserManagerInterface;
use Ilios\CoreBundle\Entity\UserInterface;
use Ilios\CoreBundle\Classes\UserEvent;

/**
 * Class SchoolVoter
 * @package Ilios\AuthenticationBundle\Voter
 */
class UsereventVoter extends AbstractVoter
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @param UserManagerInterface $userManager
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }
    /**
     * {@inheritdoc}
     */
    protected function getSupportedAttributes()
    {
        return array(self::VIEW);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return array('Ilios\CoreBundle\Classes\UserEvent');
    }

    /**
     * @param string $attribute
     * @param UserEvent $event
     * @param UserInterface|null $user
     * @return bool
     */
    protected function isGranted($attribute, $event, $user = null)
    {
        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                // check if the event-owning user is the given user
                $eventOwningUser = $this->userManager->findUserBy(['user' => $event->user]);
                return (
                    empty($eventOwningUser)
                    && $user->getId() === $eventOwningUser->getId()
                );
                break;
        }

        return false;
    }
}
