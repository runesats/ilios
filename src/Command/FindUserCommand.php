<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use App\Service\Directory;

/**
 * Find a user in the directory
 *
 * Class FindUserCommand
 */
class FindUserCommand extends Command
{
    public function __construct(
        protected Directory $directory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('ilios:find-user')
            ->setAliases(['ilios:directory:find-user'])
            ->setDescription('Find a user in the directory.')
            ->addArgument(
                'searchTerms',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'What to search for (separate multiple names with a space).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $searchTerms = $input->getArgument('searchTerms');
        $userRecords = $this->directory->find($searchTerms);
        if (!$userRecords) {
            $output->writeln('<error>Unable to find anyone matching those terms in the directory.</error>');
            return 0;
        }

        $rows = array_map(fn($arr) => [
            $arr['campusId'],
            $arr['firstName'],
            $arr['lastName'],
            $arr['email'],
            $arr['telephoneNumber']
        ], $userRecords);
        $table = new Table($output);
        $table
            ->setHeaders(['Campus ID', 'First', 'Last', 'Email', 'Phone Number'])
            ->setRows($rows)
        ;
        $table->render();

        return 0;
    }
}
