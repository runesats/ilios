<?php

declare(strict_types=1);

namespace App\Controller;

use App\Classes\CalendarEvent;
use App\Classes\SessionUserInterface;
use App\Entity\Manager\SessionManager;
use App\Entity\SessionInterface;
use App\RelationshipVoter\AbstractCalendarEvent;
use App\RelationshipVoter\AbstractVoter;
use App\Classes\SchoolEvent;
use App\Entity\Manager\SchoolManager;
use App\Exception\InvalidInputWithSafeUserMessageException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DateTime;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class SchooleventController
 *
 * Search for events happening in a school
 */
class SchooleventController extends AbstractController
{
    /**
     * @param string $version of the API requested
     * @param string $id of the school
     * @param Request $request
     * @param SchoolManager $schoolManager
     * @param SessionManager $sessionManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     * @param SerializerInterface $serializer
     *
     * @return Response
     * @throws \Exception
     */
    public function getAction(
        $version,
        $id,
        Request $request,
        SchoolManager $schoolManager,
        SessionManager $sessionManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer
    ) {
        $school = $schoolManager->findOneBy(['id' => $id]);

        if (!$school) {
            throw new NotFoundHttpException(sprintf('The school \'%s\' was not found.', $id));
        }

        if ($sessionId = $request->get('session')) {
            /** @var SessionInterface $session */
            $session = $sessionManager->findOneBy(['id' => $sessionId]);

            if (!$session) {
                throw new NotFoundHttpException(sprintf('The session \'%s\' was not found.', $id));
            }
            $events = $schoolManager->findSessionEventsForSchool($school->getId(), $session->getId());
        } else {
            $fromTimestamp = $request->get('from');
            $toTimestamp = $request->get('to');
            $from = DateTime::createFromFormat('U', $fromTimestamp);
            $to = DateTime::createFromFormat('U', $toTimestamp);

            if (!$from) {
                throw new InvalidInputWithSafeUserMessageException("?from is missing or is not a valid timestamp");
            }
            if (!$to) {
                throw new InvalidInputWithSafeUserMessageException("?to is missing or is not a valid timestamp");
            }
            $events = $schoolManager->findEventsForSchool($school->getId(), $from, $to);
        }



        $events = array_values(array_filter(
            $events,
            function ($event) use ($authorizationChecker) {
                return $authorizationChecker->isGranted(AbstractVoter::VIEW, $event);
            }
        ));

        /** @var SessionUserInterface $sessionUser */
        $sessionUser = $tokenStorage->getToken()->getUser();

        $events = $schoolManager->addPreAndPostRequisites($id, $events);

        // run pre-/post-requisite user events through the permissions checker
        for ($i = 0, $n = count($events); $i < $n; $i++) {
            /** @var SchoolEvent $event */
            $event = $events[$i];
            $event->prerequisites = array_values(
                array_filter(
                    $event->prerequisites,
                    function ($event) use ($authorizationChecker) {
                        return $authorizationChecker->isGranted(AbstractVoter::VIEW, $event);
                    }
                )
            );
            $event->postrequisites = array_values(
                array_filter(
                    $event->postrequisites,
                    function ($event) use ($authorizationChecker) {
                        return $authorizationChecker->isGranted(AbstractVoter::VIEW, $event);
                    }
                )
            );
        }

        // flatten out nested events, so that we can attach additional data points, and blank out data, in one go.
        $allEvents = [];
        /** @var SchoolEvent $event */
        foreach ($events as $event) {
            $allEvents[] = $event;
            $allEvents = array_merge($allEvents, $event->prerequisites);
            $allEvents = array_merge($allEvents, $event->postrequisites);
        }
        $allEvents = $schoolManager->addInstructorsToEvents($allEvents);
        $allEvents = $schoolManager->addMaterialsToEvents($allEvents);
        $allEvents = $schoolManager->addSessionDataToEvents($allEvents);

        /* @var SchoolEvent $event */
        foreach ($allEvents as $event) {
            if (! $authorizationChecker->isGranted(AbstractCalendarEvent::VIEW_DRAFT_CONTENTS, $event)) {
                $event->clearDataForUnprivilegedUsers();
            }
        }

        $response['events'] = $events ? array_values($events) : [];
        return new Response(
            $serializer->serialize($response, 'json'),
            Response::HTTP_OK,
            ['Content-type' => 'application/json']
        );
    }
}
