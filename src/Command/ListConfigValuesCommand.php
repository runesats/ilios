<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ApplicationConfig;
use App\Repository\ApplicationConfigRepository;
use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get an application configuration value from the DB
 *
 * Class ListConfigValuesCommand
 * @package App\Command
 */
class ListConfigValuesCommand extends Command
{
    /**
     * RolloverCourseCommand constructor.
     * @param ApplicationConfigRepository $applicationConfigRepository
     * @param string $environment
     * @param string $kernelSecret
     * @param string $databaseUrl
     */
    public function __construct(
        protected ApplicationConfigRepository $applicationConfigRepository,
        protected $environment,
        protected $kernelSecret,
        protected $databaseUrl
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('ilios:list-config-values')
            ->setAliases(['ilios:maintenance:list-config-values'])
            ->setDescription('Read configuration values from the DB');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            /** @var ApplicationConfig[] $configs */
            $configs = $this->applicationConfigRepository->findBy([], ['name' => 'asc']);
        } catch (ConnectionException $e) {
            $output->writeln('<error>Unable to connect to database.</error>');
            $output->writeln($e->getMessage());
        }
        if (empty($configs)) {
            $output->writeln('<error>There are no configuration values in the database.</error>');
        } else {
            $table = new Table($output);
            $table->setHeaderTitle('Database Values');
            $table->setHeaders(['Name', 'Value'])->setRows(
                array_map(fn(ApplicationConfig $config) => [$config->getName(), $config->getValue()], $configs)
            );
            $table->render();
        }

        $rows = [
          ['Environment', $this->environment],
          ['Kernel Secret', $this->kernelSecret],
          ['Database URL', $this->databaseUrl],
        ];
        foreach ($_ENV as $key => $value) {
            if (str_starts_with($key, "ILIOS_")) {
                $rows[] = [$key, $value];
            }
        }
        $table = new Table($output);
        $table->setHeaderTitle('Environment Values');
        $table->setHeaders(['Name', 'Value']);
        $table->setRows($rows);
        $table->render();

        return 0;
    }
}
