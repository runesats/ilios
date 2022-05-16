<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ApplicationConfig;
use App\Repository\ApplicationConfigRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Easy interface for setting up authentication parameters
 *
 * Class SetupAuthenticationCommand
 * @package App\Command
 */
class SetupAuthenticationCommand extends Command
{
    public function __construct(protected ApplicationConfigRepository $applicationConfigRepository)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ilios:setup-authentication')
            ->setAliases(['ilios:setup:authentication'])
            ->setDescription('Sets up authentication.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'How will your users authentication to Ilios (defaults to form)?: ',
            ['form', 'cas', 'ldap', 'shibboleth'],
            0
        );
        $question->setErrorMessage('Authentication %s is invalid.');
        $authType = $helper->ask($input, $output, $question);
        $parameters = [];
        switch ($authType) {
            case 'form':
                $parameters = $this->setupForm();
                break;
            case 'cas':
                $parameters = $this->setupCas($input, $output);
                break;
            case 'ldap':
                $parameters = $this->setupLdap($input, $output);
                break;
            case 'shibboleth':
                $parameters = $this->setupShib($input, $output);
                break;
        }

        foreach ($parameters as $name => $value) {
            /** @var ApplicationConfig $config */
            $config = $this->applicationConfigRepository->findOneBy(['name' => $name]);
            if (!$config) {
                $config = $this->applicationConfigRepository->create();
                $config->setName($name);
            }
            $config->setValue($value);
            $this->applicationConfigRepository->update($config, false);
        };

        $this->applicationConfigRepository->flush();

        $output->writeln('<info>Authentication Setup Successfully!</info>');

        return 0;
    }

    protected function setupForm(): array
    {
        return [
            'authentication_type' => 'form'
        ];
    }

    protected function setupCas(InputInterface $input, OutputInterface $output): array
    {
        $parameters = [
            'authentication_type' => 'cas'
        ];
        $helper = $this->getHelper('question');
        $question = new Question('What is the url for you CAS server?: ');
        $parameters['cas_authentication_server'] = $helper->ask($input, $output, $question);

        $question = new ChoiceQuestion(
            'What version of CAS do you want to use (defaults to 3)?: ',
            ['1', '2', '3'],
            '3'
        );
        $question->setErrorMessage('Version %s is invalid.');
        $parameters['cas_authentication_version'] = $helper->ask($input, $output, $question);
        $parameters['cas_authentication_verify_ssl'] = 'true';
        $output->writeln(
            "<info>If necessary set the 'cas_authentication_verify_ssl' " .
            "and 'cas_authentication_certificate_path' variables as well.</info>"
        );

        return $parameters;
    }

    protected function setupLdap(InputInterface $input, OutputInterface $output): array
    {
        $parameters = [
            'authentication_type' => 'ldap'
        ];
        $helper = $this->getHelper('question');
        $question = new Question('What is the url for you LDAP server? ');
        $parameters['ldap_authentication_host'] = $helper->ask($input, $output, $question);

        $question = new Question('What is the port for you LDAP server? (defaults to 636): ', 636);
        $parameters['ldap_authentication_port'] = $helper->ask($input, $output, $question);

        $question = new Question(
            'What is the bind template for your LDAP users?  (defaults to uid=%s,cn=users,dc=domain,dc=edu)',
            'uid=%s,cn=users,dc=domain,dc=edu'
        );
        $parameters['ldap_authentication_bind_template'] = $helper->ask($input, $output, $question);

        return $parameters;
    }

    protected function setupShib(InputInterface $input, OutputInterface $output): array
    {
        $parameters = [
            'authentication_type' => 'shibboleth'
        ];
        $helper = $this->getHelper('question');
        $question = new Question(
            'What is the login path for the service provider? (defaults to /Shibboleth.sso/Login): ',
            '/Shibboleth.sso/Login'
        );
        $parameters['shibboleth_authentication_login_path'] = $helper->ask($input, $output, $question);
        $question = new Question(
            'What is the logout path for the service provider? (defaults to /Shibboleth.sso/Logout): ',
            '/Shibboleth.sso/Logout'
        );
        $parameters['shibboleth_authentication_logout_path'] = $helper->ask($input, $output, $question);
        $question = new Question(
            'What field contains the Ilios user id? (defaults to eppn): ',
            'eppn'
        );
        $parameters['shibboleth_authentication_user_id_attribute'] = $helper->ask($input, $output, $question);

        return $parameters;
    }
}
