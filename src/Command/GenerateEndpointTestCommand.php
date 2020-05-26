<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\EntityMetadata;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Inflector\Inflector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

/**
 * Generates the test for an endpoint
 *
 * Class GenerateEndpointTestCommand
 */
class GenerateEndpointTestCommand extends Command
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var EntityMetadata
     */
    protected $entityMetadata;

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     *
     * @param Environment $twig
     * @param ManagerRegistry $registry
     * @param EntityMetadata $entityMetadata
     */
    public function __construct(
        Environment $twig,
        ManagerRegistry $registry,
        EntityMetadata $entityMetadata,
        Inflector $inflector
    ) {
        parent::__construct();
        $this->twig = $twig;
        $this->registry   = $registry;
        $this->entityMetadata   = $entityMetadata;
        $this->inflector = $inflector;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ilios:generate:endpoint-test')
            ->setHidden(true)
            ->setDescription('Creates basic test for an endpoint.')
            ->addArgument(
                'entityShortcut',
                InputArgument::REQUIRED,
                'The name of an entity e.g. App\Entity\Session.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shortCut = $input->getArgument('entityShortcut');

        $manager = $this->registry->getManagerForClass($shortCut);
        $class = $manager->getClassMetadata($shortCut)->getName();
        if (!$this->entityMetadata->isAnIliosEntity($class)) {
            throw new \Exception("Sorry. {$shortCut} is not an Ilios entity.");
        }
        $reflection = new \ReflectionClass($class);
        $entity = $reflection->getShortName();


        $mapProperties = function (\ReflectionProperty $property) {
            return [
                'name' => $property->getName(),
                'type' => $this->entityMetadata->getTypeOfProperty($property)
            ];
        };

        $writableProperties = $this->entityMetadata->extractWritableProperties($reflection);
        $puts = array_map($mapProperties, $writableProperties);

        $propertyReflection = $this->entityMetadata->extractExposedProperties($reflection);
        $filters = array_map($mapProperties, $propertyReflection);

        $propertyReflection = $this->entityMetadata->extractReadOnlyProperties($reflection);
        $readOnlies = array_map($mapProperties, $propertyReflection);

        $plural = $this->inflector->pluralize($entity);
        $endpoint = strtolower($plural);
        $template = 'generate/endpointTest.php.twig';
        $groupNumber = rand(1, 2);

        $content = $this->twig->render($template, [
            'entity' => $entity,
            'plural' => $plural,
            'endpoint' => $endpoint,
            'filters' => $filters,
            'puts' => $puts,
            'readOnlies' => $readOnlies,
            'groupNumber' => $groupNumber,
        ]);

        print $content;

        return 0;
    }
}
