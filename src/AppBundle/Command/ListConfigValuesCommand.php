<?php

namespace AppBundle\Command;

use AppBundle\Entity\ApplicationConfig;
use AppBundle\Entity\Manager\ApplicationConfigManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Get an application configuration value from the DB
 *
 * Class ListConfigValuesCommand
 * @package AppBundle\Command
 */
class ListConfigValuesCommand extends Command
{
    /**
     * @var ApplicationConfigManager
     */
    protected $applicationConfigManager;

    /**
     * RolloverCourseCommand constructor.
     * @param ApplicationConfigManager $applicationConfigManager
     */
    public function __construct(ApplicationConfigManager $applicationConfigManager)
    {
        $this->applicationConfigManager = $applicationConfigManager;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('ilios:maintenance:list-config-values')
            ->setDescription('Read configuration values from the DB');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ApplicationConfig[] $configs */
        $configs = $this->applicationConfigManager->findBy([], ['name' => 'asc']);
        if (empty($configs)) {
            $output->writeln('<error>There are no configuration values in the database.</error>');
        } else {
            $table = new Table($output);
            $table->setHeaders(array('Name', 'Value'))
                ->setRows(array_map(function (ApplicationConfig $config) {
                    return [$config->getName(), $config->getValue()];
                }, $configs));
            $table->render();
        }
    }
}
