<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\CasAuthentication as Cas;
use App\Service\FormAuthentication as Form;
use App\Service\LdapAuthentication as Ldap;
use App\Service\ShibbolethAuthentication as Shibboleth;
use Exception;

class AuthenticationFactory
{
    public function __construct(
        protected Config $config,
        protected Cas $casAuthentication,
        protected Form $formAuthentication,
        protected Ldap $ldapAuthentication,
        protected Shibboleth $shibbolethAuthentication
    ) {
    }

    /**
     * Create the correct service for authentication
     * @throws Exception
     */
    public function createAuthenticationService(): Cas|Form|Ldap|Shibboleth
    {
        $authenticationType = $this->config->get('authentication_type');
        return match ($authenticationType) {
            'form' => $this->formAuthentication,
            'shibboleth' => $this->shibbolethAuthentication,
            'ldap' => $this->ldapAuthentication,
            'cas' => $this->casAuthentication,
            default => throw new Exception("{$authenticationType} is not a valid ilios authenticator"),
        };
    }
}
