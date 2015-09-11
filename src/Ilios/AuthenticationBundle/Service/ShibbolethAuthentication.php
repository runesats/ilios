<?php

namespace Ilios\AuthenticationBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ilios\CoreBundle\Entity\Manager\AuthenticationManagerInterface;
use Ilios\AuthenticationBundle\Traits\AuthenticationService;
use Ilios\CoreBundle\Entity\UserInterface;

class ShibbolethAuthentication implements AuthenticationInterface
{
    use AuthenticationService;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authManager;
    
    /**
     * @var JsonWebTokenManager
     */
    protected $jwtManager;
    
    
    /**
     * Constructor
     * @param AuthenticationManagerInterface $authManager
     * @param JsonWebTokenManager            $jwtManager
     */
    public function __construct(
        AuthenticationManagerInterface $authManager,
        JsonWebTokenManager $jwtManager
    ) {
        $this->authManager = $authManager;
        $this->jwtManager = $jwtManager;
    }
    
    /**
     * Authenticate a user from shibboleth
     *
     * If the user is not yet logged in send a redirect Request
     * If the user is logged in, but no account exists send an error
     * If the user is authenticated send a JWT
     * @param Request $request
     *
     * @throws \Exception when the shibboleth attributes do not contain an eppn
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $applicationId = $request->server->get('Shib-Application-ID');
        if (!$applicationId) {
            return new JsonResponse(array(
                'status' => 'redirect',
                'errors' => [],
                'jwt' => null,
            ), JsonResponse::HTTP_OK);
        }
        $eppn = $request->server->get('eppn');
        if (!$eppn) {
            throw new \Exception(
                "No 'eepn' found for authenticated user.  Dump of SERVER global: " .
                var_export($_SERVER, true)
            );
        }
        $authEntity = $this->authManager->findAuthenticationBy(array('eppn' => $eppn));
        if (!$authEntity) {
            return new JsonResponse(array(
                'status' => 'noAccountExists',
                'eppn' => $eppn,
                'errors' => [],
                'jwt' => null,
            ), JsonResponse::HTTP_BAD_REQUEST);
        }
        $jwt = $this->jwtManager->createJwtFromUser($authEntity->getUser());
        
        return $this->createSuccessResponseFromJWT($jwt);
    }
    
    /**
     * @inheritdoc
     */
    public function setupNewUser(array $directoryInformation, UserInterface $user)
    {
        if (!array_key_exists('eppn', $directoryInformation) ||
            empty($directoryInformation['eppn'])
        ) {
            throw new \Exception(
                "No 'eepn' was found for the user.  Values: " .
                var_export($directoryInformation, true)
            );
        }

        $authentication = $this->authManager->createAuthentication();
        $authentication->setUser($user);
        $user->setAuthentication($authentication);
        $authentication->setEppn($directoryInformation['eppn']);
        $this->authManager->updateAuthentication($authentication, false);
    }
    
    /**
     * @inheritdoc
     */
    public function syncUser(array $directoryInformation, UserInterface $user)
    {
        if (!array_key_exists('eppn', $directoryInformation) ||
            empty($directoryInformation['eppn'])
        ) {
            throw new \Exception(
                "No 'eepn' was found for the user.  Values: " .
                var_export($directoryInformation, true)
            );
        }

        $authentication = $user->getAuthentication();
        $authentication->setEppn($directoryInformation['eppn']);
        $this->authManager->updateAuthentication($authentication, false);
    }
}
