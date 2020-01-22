<?php

declare(strict_types=1);

namespace App\Controller;

use App\Classes\SessionUserInterface;
use App\Service\Index\Users;
use App\Service\PermissionChecker;
use App\Service\Search as SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class SearchController
 *
 * Search Ilios
 */
class Search extends AbstractController
{
    /**
     * @var SearchService
     */
    protected $search;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var PermissionChecker
     */
    protected $permissionChecker;

    /**
     * @var Users
     */
    protected $userIndex;

    public function __construct(
        SearchService $search,
        Users $userIndex,
        TokenStorageInterface $tokenStorage,
        PermissionChecker $permissionChecker
    ) {
        $this->search = $search;
        $this->tokenStorage = $tokenStorage;
        $this->permissionChecker = $permissionChecker;
        $this->userIndex = $userIndex;
    }

    public function curriculumSearch(Request $request)
    {
        /** @var SessionUserInterface $sessionUser */
        $sessionUser = $this->tokenStorage->getToken()->getUser();
        if (! $this->permissionChecker->canSearchCurriculum($sessionUser)) {
            throw new AccessDeniedException();
        }

        $query = $request->get('q');

        $onlySuggest = (bool) $request->get('onlySuggest');

        $result = $this->search->curriculumSearch($query, $onlySuggest);

        return new JsonResponse(['results' => $result]);
    }

    public function userSearch(Request $request)
    {
        /** @var SessionUserInterface $sessionUser */
        $sessionUser = $this->tokenStorage->getToken()->getUser();
        if (! $this->permissionChecker->canSearchUsers($sessionUser)) {
            throw new AccessDeniedException();
        }

        $query = $request->get('q');

        $onlySuggest = (bool) $request->get('onlySuggest');
        $size = $request->get('size');

        if ($size === null) {
            $size = 100;
        }

        $result = $this->userIndex->search($query, $size, $onlySuggest);

        return new JsonResponse(['results' => $result]);
    }
}
