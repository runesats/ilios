<?php

namespace Ilios\AuthenticationBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

use Ilios\CoreBundle\Entity\AuthenticationInterface as AuthenticationEntityInterface;
use Ilios\CoreBundle\Entity\Manager\AuthenticationManagerInterface;
use Ilios\CoreBundle\Entity\UserInterface;
use Ilios\AuthenticationBundle\Traits\AuthenticationService;

class FormAuthentication implements AuthenticationInterface
{
    use AuthenticationService;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authManager;
    
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;
    
    /**
     * @var JsonWebTokenManager
     */
    protected $jwtManager;
    
    /**
    * Constructor
    * @param AuthenticationManagerInterface $authManager
    * @param UserPasswordEncoderInterface   $encoder
    * @param TokenStorageInterface          $tokenStorage
    * @param JsonWebTokenManager            $jwtManager
    */
    public function __construct(
        AuthenticationManagerInterface $authManager,
        UserPasswordEncoderInterface $encoder,
        TokenStorageInterface $tokenStorage,
        JsonWebTokenManager $jwtManager
    ) {
        $this->authManager = $authManager;
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
        $this->jwtManager = $jwtManager;
    }
    
    /**
     * Login a user using a username and password
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $errors = [];
        if (!$username) {
            $errors[] = 'missingUsername';
        }
        if (!$password) {
            $errors[] = 'missingPassword';
        }
        
        if ($username && $password) {
            $authEntity = $this->authManager->findAuthenticationByUsername($username);
            if ($authEntity) {
                $user = $authEntity->getUser();
                $passwordValid = $this->encoder->isPasswordValid($user, $password);
                if ($passwordValid) {
                    $this->updateLegacyPassword($authEntity, $password);
                    $jwt = $this->jwtManager->createJwtFromUser($user);
                    
                    return $this->createSuccessResponseFromJWT($jwt);
                }
            }
            $errors[] = 'badCredentials';
        }
        
        

        return new JsonResponse(array(
            'status' => 'error',
            'errors' => $errors,
            'jwt' => null,
        ), JsonResponse::HTTP_BAD_REQUEST);
    }
    
    /**
     * Update users to the new password encoding when they login
     * @param  AuthenticationEntityInterface $authEntity
     * @param  string         $password
     */
    protected function updateLegacyPassword(AuthenticationEntityInterface $authEntity, $password)
    {
        if ($authEntity->isLegacyAccount()) {
            //we have to have a valid token to update the user because the audit log requires it
            $authenticatedToken = new PreAuthenticatedToken(
                $authEntity->getUser(),
                'fakekey',
                'fakeProvider'
            );
            $authenticatedToken->setAuthenticated(true);
            $this->tokenStorage->setToken($authenticatedToken);
            
            $authEntity->setPasswordSha256(null);
            $encodedPassword = $this->encoder->encodePassword($authEntity->getUser(), $password);
            $authEntity->setPasswordBcrypt($encodedPassword);
            $this->authManager->updateAuthentication($authEntity);
        }
    }
}
