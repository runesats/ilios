<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Sends a test email
 *
 * Class SendTestEmailCommand
 */
class SendTestEmailCommand extends Command
{
    public function __construct(protected MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('ilios:send-test-email')
            ->setDescription('Sends out a test email, useful for ensuring email is working.')
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'The email address to send from'
            )
            ->addArgument(
                'to',
                InputArgument::REQUIRED,
                'The email address to send to'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $to = $input->getArgument('to');
        $from = $input->getArgument('from');

        $message = (new Email())
            ->to($to)
            ->from($from)
            ->subject('Ilios Test Email')
            ->text('This is a test email from your ilios system.');
        $this->mailer->send($message);

        return Command::SUCCESS;
    }
}
