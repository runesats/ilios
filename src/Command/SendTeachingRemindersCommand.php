<?php

namespace App\Command;

use App\Entity\Manager\OfferingManager;
use App\Entity\OfferingInterface;
use App\Entity\SchoolInterface;
use App\Entity\UserInterface;
use App\Service\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

/**
 * Sends teaching reminders to educators for their upcoming session offerings.
 *
 * Class SendTeachingRemindersCommand
 */
class SendTeachingRemindersCommand extends Command
{
    /**
     * @var string
     */
    public const DEFAULT_TEMPLATE_NAME = 'teachingreminder.text.twig';

    /**
     * @var string
     */
    public const DEFAULT_MESSAGE_SUBJECT = 'Upcoming Teaching Session';

    /**
     * @var OfferingManager
     */
    protected $offeringManager;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $kernelProjectDir;

    /**
     * SendTeachingRemindersCommand constructor.
     * @param OfferingManager $offeringManager
     * @param Environment $twig
     * @param \Swift_Mailer $mailer
     * @param Config $config
     * @param Filesystem $fs
     * @param string $kernelProjectDir
     */
    public function __construct(
        OfferingManager $offeringManager,
        Environment $twig,
        \Swift_Mailer $mailer,
        Config $config,
        Filesystem $fs,
        string $kernelProjectDir
    ) {
        parent::__construct();
        $this->offeringManager = $offeringManager;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->config = $config;
        $this->fs = $fs;
        $this->kernelProjectDir = $kernelProjectDir;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ilios:send-teaching-reminders')
            ->setAliases(['ilios:messaging:send-teaching-reminders'])
            ->setDescription('Sends teaching reminders to educators.')
            ->addArgument(
                'sender',
                InputArgument::REQUIRED,
                'Email address to send reminders from.'
            )
            ->addArgument(
                'base_url',
                InputArgument::REQUIRED,
                'The base URL of your Ilios instance.'
            )
            ->addOption(
                'days',
                null,
                InputOption::VALUE_OPTIONAL,
                'How many days in advance of teaching events reminders should be sent.',
                7
            )
            ->addOption(
                'subject',
                null,
                InputOption::VALUE_OPTIONAL,
                'The subject line of reminder emails.',
                self::DEFAULT_MESSAGE_SUBJECT
            )
            ->addOption(
                'sender_name',
                null,
                InputOption::VALUE_OPTIONAL,
                "The name of the reminder's sender."
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Prints out notification instead of emailing it. Useful for testing/debugging purposes.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // input validation
        $errors = $this->validateInput($input);
        if (! empty($errors)) {
            foreach ($errors as $error) {
                $output->writeln("<error>{$error}</error>");
            }
            return 1;
        }

        $daysInAdvance = $input->getOption('days');
        $sender = $input->getArgument('sender');
        $baseUrl = rtrim($input->getArgument('base_url'), '/');
        $subject = $input->getOption('subject');
        $isDryRun = $input->getOption('dry-run');
        $senderName = $input->getOption('sender_name');
        $from = $sender;
        if ($senderName) {
            $from = [$sender => $senderName];
        }

        // get all applicable offerings.
        $offerings = $this->offeringManager->getOfferingsForTeachingReminders($daysInAdvance);

        if ($offerings->isEmpty()) {
            $output->writeln('<info>No offerings with pending teaching reminders found.</info>');
            return 0;
        }

        // mail out a reminder per instructor per offering.
        $templateCache = [];
        $iterator = $offerings->getIterator();
        $i = 0;

        /** @var OfferingInterface $offering */
        foreach ($iterator as $offering) {
            $deleted = ! $offering->getSession()
                || ! $offering->getSession()->getCourse()
                || ! $offering->getSchool();

            if ($deleted) {
                continue;
            }

            $school = $offering->getSchool();
            if (! array_key_exists($school->getId(), $templateCache)) {
                $template = $this->getTemplatePath($school);
                $templateCache[$school->getId()] = $template;
            }
            $template = $templateCache[$school->getId()];

            $instructors = $offering->getAllInstructors()->toArray();
            $timezone = $this->config->get('timezone');


            /** @var UserInterface $instructor */
            foreach ($instructors as $instructor) {
                $i++;
                $messageBody = $this->twig->render($template, [
                    'base_url' => $baseUrl,
                    'instructor' => $instructor,
                    'offering' => $offering,
                    'timezone' => $timezone
                ]);
                $email = $instructor->getPreferredEmail();
                if (empty($email)) {
                    $email = $instructor->getEmail();
                }
                $message = (new \Swift_Message($subject))
                    ->setFrom($from)
                    ->setTo($email)
                    ->setCharset('UTF-8')
                    ->setContentType('text/plain')
                    ->setBody($messageBody)
                    ->setMaxLineLength(998);
                if ($isDryRun) {
                    $output->writeln($message->getHeaders()->toString());
                    $output->writeln($message->getBody());
                } else {
                    $this->mailer->send($message);
                }
            }
        }

        $output->writeln("<info>Sent {$i} teaching reminders.</info>");

        return 0;
    }

    /**
     * Locates the applicable message template for a given school and returns its path.
     * @param SchoolInterface $school
     * @return string The template path.
     */
    protected function getTemplatePath(SchoolInterface $school)
    {
        $prefix = $school->getTemplatePrefix();
        if ($prefix) {
            $path = 'email/' . basename($prefix . '_' . self::DEFAULT_TEMPLATE_NAME);
            if ($this->fs->exists($this->kernelProjectDir . '/custom/templates/' . $path)) {
                return $path;
            }
        }

        return 'email/' . self::DEFAULT_TEMPLATE_NAME;
    }

    /**
     * Validates user input.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return array A list of validation error message. Empty if no validation errors occurred.
     */
    protected function validateInput(InputInterface $input)
    {
        $errors = [];

        $daysInAdvance = intval($input->getOption('days'), 10);
        if (0 > $daysInAdvance) {
            $errors[] = "Invalid value '{$daysInAdvance}' for '--days' option. Must be greater or equal to 0.";
        }
        $sender = $input->getArgument('sender');
        if (! filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid value '{$sender}' for '--sender' option. Must be a valid email address.";
        }

        return $errors;
    }
}
