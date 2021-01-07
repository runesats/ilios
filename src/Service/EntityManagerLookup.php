<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ManagerInterface;
use Exception;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityManagerLookup
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EndpointResponseNamer
     */
    protected $endpointResponseNamer;

    public function __construct(ContainerInterface $container, EndpointResponseNamer $endpointResponseNamer)
    {
        $this->container = $container;
        $this->endpointResponseNamer = $endpointResponseNamer;
    }

    public function getManagerForEndpoint(string $endPoint): ManagerInterface
    {
        $entityName = $this->getEntityName($endPoint);
        $name = sprintf('App\Repository\%sRepository', $entityName);
        if (!$this->container->has($name)) {
            throw new Exception(
                sprintf('The repository for \'%s\' does not exist. Is the service public?', $name)
            );
        }

        $manager = $this->container->get($name);

        if (!$manager instanceof ManagerInterface) {
            $class = $manager->getClass();
            throw new Exception("{$class} is not an Ilios Repository.");
        }

        return $manager;
    }

    public function getManagerForEntity(string $entityClass): ManagerInterface
    {
        $reflect = new ReflectionClass($entityClass);
        $entityName = $reflect->getShortName();
        $name = sprintf('App\Repository\%sRepository', $entityName);
        if (!$this->container->has($name)) {
            throw new Exception(
                sprintf('The repository for \'%s\' does not exist. Is the service public?', $name)
            );
        }

        $manager = $this->container->get($name);

        if (!$manager instanceof ManagerInterface) {
            throw new Exception("{$name} is not an Ilios Manager.");
        }

        return $manager;
    }

    /**
     * Get the Entity name for an endpoint
     *
     */
    protected function getEntityName(string $name): string
    {
        return ucfirst($this->endpointResponseNamer->getSingularName($name));
    }

    public function getDtoClassForEndpoint(string $endPoint): string
    {
        $entityName = $this->getEntityName($endPoint);
        $name = "App\\Entity\\DTO\\${entityName}DTO";
        if (!class_exists($name)) {
            throw new Exception(
                sprintf('The DTO \'%s\' does not exist.', $name)
            );
        }

        return $name;
    }
}
