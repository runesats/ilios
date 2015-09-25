<?php

namespace Ilios\CliBundle\Command;

use Ilios\CoreBundle\Entity\InstructorGroupInterface;
use Ilios\CoreBundle\Entity\Manager\OfferingManagerInterface;
use Ilios\CoreBundle\Entity\OfferingInterface;
use Ilios\CoreBundle\Entity\SchoolInterface;
use Ilios\CoreBundle\Entity\UserInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Sends teaching reminders to educators for their upcoming session offerings.
 *
 * Class SendTeachingRemindersCommand
 * @package Ilios\CliBUndle\Command
 */
class SendTeachingRemindersCommand extends Command
{
    /**
     * @var string
     */
    const DEFAULT_TEMPLATE_NAME = 'teachingreminder.text.twig';

    /**
     * @var OfferingManagerInterface
     */
    protected $offeringManager;

    /**
     * @var EngineInterface
     */
    protected $templatingEngine;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @param \Ilios\CoreBundle\Entity\Manager\OfferingManagerInterface $offeringManager
     * @param \Symfony\Component\Templating\EngineInterface
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        OfferingManagerInterface $offeringManager,
        EngineInterface $templatingEngine,
        $mailer
    ) {
        parent::__construct();
        $this->offeringManager = $offeringManager;
        $this->templatingEngine = $templatingEngine;
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ilios:messaging:send-teaching-reminders')
            ->setDescription('Sends teaching reminders to educators.')
            ->addOption(
                'base_url',
                null,
                InputOption::VALUE_REQUIRED,
                'The base URL of your Ilios instance.'
            )
            ->addOption(
                'sender',
                null,
                InputOption::VALUE_REQUIRED,
                'Email address to send reminders from.'
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
                'Upcoming Teaching Session'
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
            return;
        }

        $daysInAdvance = $input->getOption('days');
        $sender = $input->getOption('sender');
        $baseUrl = rtrim($input->getOption('base_url'), '/');
        $subject = $input->getOption('subject');
        $isDryRun = $input->getOption('dry-run');

        // get all applicable offerings.
        $offerings = $this->offeringManager->getOfferingsForTeachingReminders($daysInAdvance);

        if ($offerings->isEmpty()) {
            $output->writeln('<info>No offerings with pending teaching reminders found.</info>');
            return;
        }

        // mail out a reminder per instructor per offering.
        $templateCache = [];
        $iterator = $offerings->getIterator();
        $i = 0;

        /** @var OfferingInterface $offering */
        foreach ($iterator as $offering) {
            $school = $offering->getSession()->getCourse()->getSchool();
            if (! array_key_exists($school->getId(), $templateCache)) {
                $template = $this->getTemplatePath($school);
                $templateCache[$school->getId()] = $template;
            }
            $template = $templateCache[$school->getId()];

            $instructors = $this->getAllInstructorsForOffering($offering);

            /** @var UserInterface $instructor */
            foreach ($instructors as $instructor) {
                $i++;
                $messageBody = $this->templatingEngine->render($template, [
                    'base_url' => $baseUrl,
                    'instructor' => $instructor,
                    'offering' => $offering,
                ]);
                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($sender)
                    ->setTo($instructor->getEmail())
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
    }

    /**
     * Locates the applicable message template for a given school and returns its path.
     * @param SchoolInterface $school
     * @return string The template path.
     */
    protected function getTemplatePath(SchoolInterface $school)
    {
        $paths = [
            '@custom_email_templates/' . basename($school->getTemplatePrefix() . '_' . self::DEFAULT_TEMPLATE_NAME),
            '@custom_email_templates/' . self::DEFAULT_TEMPLATE_NAME,
        ];
        foreach ($paths as $path) {
            if ($this->templatingEngine->exists($path)) {
                return $path;
            }
        }
        return 'IliosCoreBundle:Email:' .self::DEFAULT_TEMPLATE_NAME;
    }


    /**
     * @param \Ilios\CoreBundle\Entity\OfferingInterface $offering
     * @return UserInterface[]
     */
    protected function getAllInstructorsForOffering(OfferingInterface $offering)
    {
        $rhett = [];
        $instructors = $offering->getInstructors();
        $iterator = $instructors->getIterator();
        /** @var UserInterface $instructor */
        foreach ($iterator as $instructor) {
            $rhett[$instructor->getId()] = $instructor;
        }

        $instructorGroups = $offering->getInstructorGroups();
        $iterator = $instructorGroups->getIterator();

        /** @var InstructorGroupInterface $instructorGroup */
        foreach ($iterator as $instructorGroup) {
            $instructors = $instructorGroup->getUsers();
            $iterator2 = $instructors->getIterator();
            /** @var UserInterface $instructor */
            foreach ($iterator2 as $instructor) {
                if (! array_key_exists($instructor->getId(), $rhett)) {
                    $rhett[$instructor->getId()] = $instructor;
                }
            }
        }
        return array_values($rhett);
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
        $sender = $input->getOption('sender');
        if (! filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid value '{$sender}' for '--sender' option. Must be a valid email address.";
        }

        return $errors;
    }
}
