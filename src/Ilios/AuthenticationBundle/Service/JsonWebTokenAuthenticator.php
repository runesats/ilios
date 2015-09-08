<?php

namespace Ilios\AuthenticationBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;

class JsonWebTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var JsonWebTokenManager
     */
    protected $jwtManager;
    
    /**
    * Constructor
    * @param UserManagerInterface $userManager
    * @param JsonWebTokenManager            $jwtManager
    * @param string $secretKey injected kernel secret key
    */
    public function __construct(
        JsonWebTokenManager $jwtManager
    ) {
        $this->jwtManager = $jwtManager;
    }
    
    public function createToken(Request $request, $providerKey)
    {
        $jwt = false;

        $authorizationHeader = $request->headers->get('X-JWT-Authorization');
        if (preg_match('/^Token (.*)$/', $authorizationHeader, $matches)) {
            $jwt = $matches[1];
        }
        if (!$jwt) {
            throw new AuthenticationCredentialsNotFoundException('No JSON Web Token was found in the request');
        }

        return new PreAuthenticatedToken(
            'anon.',
            $jwt,
            $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof EntityUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of EntityUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }
        
        try {
            $jwt = $token->getCredentials();
            $username = $this->jwtManager->getUserIdFromToken($jwt);
            $issuedAt = $this->jwtManager->getIssuedAtFromToken($jwt);
        } catch (\UnexpectedValueException $e) {
            throw new BadCredentialsException('Invalid JSON Web Token: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new BadCredentialsException('Invalid JSON Web Token');
        }
        
        $user = $userProvider->loadUserByUsername($username);
        $authentication = $user->getAuthentication();
        if ($authentication) {
            $tokenNotValidBefore = $authentication->getInvalidateTokenIssuedBefore();
            if ($tokenNotValidBefore) {
                if ($tokenNotValidBefore > $issuedAt) {
                    throw new BadCredentialsException(
                        'Invalid JSON Web Token: Not issued before ' . $tokenNotValidBefore->format('c')
                    );
                }
            }
        }
        
        $authenticatedToken = new PreAuthenticatedToken(
            $user,
            $jwt,
            $providerKey
        );
        $authenticatedToken->setAuthenticated(true);
        
        return $authenticatedToken;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
    
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response("Authentication Failed. " . $exception->getMessage(), 401);
    }
}
