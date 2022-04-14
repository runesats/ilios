<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\CalendarEvent;
use App\Classes\SchoolEvent as Event;
use App\Classes\SessionUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class SchoolEvent
 */
class SchoolEvent extends AbstractCalendarEvent
{
    public const VIEW_VIRTUAL_LINK = 'view_virtual_link';

    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof Event
            && in_array($attribute, [self::VIEW, self::VIEW_DRAFT_CONTENTS, self::VIEW_VIRTUAL_LINK]);
    }

    /**
     * @param string $attribute
     * @param Event $event
     * @param TokenInterface $token
     */
    protected function voteOnAttribute($attribute, $event, TokenInterface $token): bool
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
                // if the event is published and the it's owned by the current user's
                // primary school, then it can be viewed.
                if ($event->isPublished && $user->getSchoolId() === $event->school) {
                    return true;
                }

                // if the current user is associated with the given event
                // in a directing/administrating/instructing capacity via the event's
                // owning school/course/session/ILM/offering context,
                // then it can be viewed, even if it is not published.
                return $this->isUserAdministratorDirectorsOrInstructorOfEvent($user, $event);

            case self::VIEW_DRAFT_CONTENTS:
                // can't view draft data on events, unless
                // the event is being instructed/directed/administered by the current user.
                return $this->isUserAdministratorDirectorsOrInstructorOfEvent($user, $event);
            case self::VIEW_VIRTUAL_LINK:
                return $this->isUserAdministratorDirectorsOrInstructorOfEvent($user, $event)
                    || $this->isUserLearnerInEvent($user, $event);
            default:
                return false;
        }
    }

    /**
     * @param SessionUserInterface $user
     * @param Event $event
     * @return bool
     */
    protected function isUserLearnerInEvent(SessionUserInterface $user, Event $event)
    {
        $offeringId = $event->offering;
        $ilmId = $event->ilmSession;

        return ($ilmId && $user->isLearnerInIlm($ilmId))
            || ($offeringId && $user->isLearnerInOffering($offeringId));
    }
}
