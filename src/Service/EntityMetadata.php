<?php

declare(strict_types=1);

namespace App\Service;

use App\Annotation\DTO;
use App\Annotation\Id;
use App\Annotation\Related;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use App\Annotation\ReadOnly;
use App\Annotation\Type;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;
use ReflectionClass;
use ReflectionProperty;
use is_null;

class EntityMetadata
{
    private const CACH_KEY_PREFIX = 'ilios-entity-metadata-';

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var array
     */
    private $exposedPropertiesForClass;

    /**
     * @var array
     */
    private $typeForClasses;

    /**
     * @var array
     */
    private $idForClasses;

    /**
     * @var array
     */
    private $relatedForClass;

    /**
     * @var array
     */
    private $iliosEntities;

    /**
     * @var array
     */
    private $iliosDtos;

    /**
     * EntityMetadata constructor
     *
     * Build and cache all of the entity and dto class names so
     * we don't have to constantly run expensive class_exists
     * and annotation inspection tasks
     *
     * @param Cache $cache
     * @param KernelInterface $kernel
     */
    public function __construct(Cache $cache, KernelInterface $kernel)
    {
        $this->exposedPropertiesForClass = [];
        $this->typeForClasses = [];
        $this->idForClasses = [];
        $this->relatedForClass = [];

        $this->annotationReader = new CachedReader(
            new AnnotationReader(),
            $cache,
            $debug = $kernel->getEnvironment() !== 'prod'
        );

        $entityKey = self::CACH_KEY_PREFIX . 'entities';
        if (!$cache->contains($entityKey) || !$entities = $cache->fetch($entityKey)) {
            $entities = $this->findIliosEntities($kernel);
            $cache->save($entityKey, $entities);
        }

        $this->iliosEntities = $entities;

        $dtoKey = self::CACH_KEY_PREFIX . 'dtos';
        if (!$cache->contains($dtoKey) || !$dtos = $cache->fetch($dtoKey)) {
            $dtos = $this->findIliosDtos($kernel);
            $cache->save($dtoKey, $dtos);
        }

        $this->iliosDtos = $dtos;
    }

    /**
     * Check if an object or className has the Entity annotation
     *
     * @param $classNameOrObject
     *
     * @return bool
     */
    public function isAnIliosEntity($classNameOrObject)
    {
        if ($this->isAStringOrClass($classNameOrObject)) {
            $className = $this->getClassName($classNameOrObject);

            if (in_array($className, $this->iliosEntities)) {
                return true;
            }

            if (strpos($className, 'Proxies') !== false) {
                $reflection = new ReflectionClass($classNameOrObject);
                if ($reflection->implementsInterface('Doctrine\Common\Persistence\Proxy')) {
                    $reflection = $reflection->getParentClass();
                    $className = $reflection->getName();

                    return in_array($className, $this->iliosEntities);
                }
            }
        }

        return false;
    }

    /**
     * Check if an object or class name has the DTO annotation
     *
     * @param $classNameOrObject
     *
     * @return bool
     */
    public function isAnIliosDto($classNameOrObject)
    {
        if ($this->isAStringOrClass($classNameOrObject)) {
            $className = $this->getClassName($classNameOrObject);

            return in_array($className, $this->iliosDtos);
        }

        return false;
    }

    /**
     * Checks to see if what we have been passed is a string or a class
     * @param $classNameOrObject
     * @return bool
     */
    protected function isAStringOrClass($classNameOrObject)
    {
        return is_string($classNameOrObject) || is_object($classNameOrObject);
    }

    /**
     * Gets the name of a class
     *
     * @param $classNameOrObject
     * @return string
     */
    protected function getClassName($classNameOrObject)
    {
        return is_object($classNameOrObject) ? get_class($classNameOrObject) : $classNameOrObject;
    }

    /**
     * Get all of the properties of a call which are
     * marked with the Exposed annotation
     *
     * @param ReflectionClass $reflection
     *
     * @return mixed
     */
    public function extractExposedProperties(ReflectionClass $reflection)
    {
        $className = $reflection->getName();
        if (!array_key_exists($className, $this->exposedPropertiesForClass)) {
            $properties = $reflection->getProperties();

            $exposed =  array_filter($properties, function (\ReflectionProperty $property) {
                $annotation = $this->annotationReader->getPropertyAnnotation(
                    $property,
                    'App\Annotation\Expose'
                );

                return !is_null($annotation);
            });

            $exposedProperties = [];
            foreach ($exposed as $property) {
                $exposedProperties[$property->name] = $property;
            }

            $this->exposedPropertiesForClass[$className] = $exposedProperties;
        }

        return $this->exposedPropertiesForClass[$className];
    }

    /**
     * Get the ID property for a class
     */
    public function extractId(ReflectionClass $reflection): string
    {
        $className = $reflection->getName();
        if (!array_key_exists($className, $this->idForClasses)) {
            $properties = $reflection->getProperties();

            $ids = array_filter($properties, function (ReflectionProperty $property) {
                $annotation = $this->annotationReader->getPropertyAnnotation(
                    $property,
                    Id::class
                );

                return !is_null($annotation);
            });
            if (!$ids) {
                throw new \Exception("${className} has no property annotated with @Id");
            }

            $this->idForClasses[$className] = array_values($ids)[0]->getName();
        }

        return $this->idForClasses[$className];
    }

    /**
     * Get the ID property for a class
     */
    public function extractRelated(ReflectionClass $reflection): array
    {
        $className = $reflection->getName();
        if (!array_key_exists($className, $this->relatedForClass)) {
            $properties = $reflection->getProperties();

            $relatedProperties = [];

            foreach ($properties as $property) {
                $annotation = $this->annotationReader->getPropertyAnnotation(
                    $property,
                    Related::class
                );

                if ($annotation) {
                    $relatedProperties[$property->getName()] = $annotation->value;
                }
            }

            $this->relatedForClass[$className] = $relatedProperties;
        }

        return $this->relatedForClass[$className];
    }

    /**
     * Get the JSON:API type of an object
     */
    public function extractType(ReflectionClass $reflection): string
    {
        $className = $reflection->getName();
        if (!array_key_exists($className, $this->typeForClasses)) {
            $annotation = $this->annotationReader->getClassAnnotation(
                $reflection,
                DTO::class
            );


            $this->typeForClasses[$className] = $annotation->value;
        }

        return $this->typeForClasses[$className];
    }

    /**
     * Get all of the properties of a class which are
     * not annotated as ReadOnly
     *
     * @param ReflectionClass $reflection
     *
     * @return array
     */
    public function extractWritableProperties(ReflectionClass $reflection)
    {
        $exposedProperties = $this->extractExposedProperties($reflection);

        return array_filter($exposedProperties, function (\ReflectionProperty $property) {
            return !$this->isPropertyReadOnly($property);
        });
    }

    /**
     * Get all of the annotated ReadOnly properties of a class
     *
     * @param ReflectionClass $reflection
     *
     * @return array
     */
    public function extractReadOnlyProperties(ReflectionClass $reflection)
    {
        $exposedProperties = $this->extractExposedProperties($reflection);

        return array_filter($exposedProperties, function (\ReflectionProperty $property) {
            return $this->isPropertyReadOnly($property);
        });
    }

    /**
     * Get the Type annotation of a property
     *
     * @param \ReflectionProperty $property
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getTypeOfProperty(\ReflectionProperty $property)
    {
        /** @var Type $typeAnnotation */
        $typeAnnotation = $this->annotationReader->getPropertyAnnotation(
            $property,
            'App\Annotation\Type'
        );

        if (is_null($typeAnnotation)) {
            throw new \Exception(
                "Missing Type annotation on {$property->class}::{$property->getName()}"
            );
        }

        return $typeAnnotation->value;
    }

    /**
     * Check if a property has the ReadOnly annotation
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isPropertyReadOnly(\ReflectionProperty $property)
    {
        /** @var ReadOnly $annotation */
        $annotation = $this->annotationReader->getPropertyAnnotation(
            $property,
            'App\Annotation\ReadOnly'
        );

        return !is_null($annotation);
    }

    /**
     * Check if a property has the RemoveMarkup annotation
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isPropertyRemoveMarkup(\ReflectionProperty $property)
    {
        /** @var ReadOnly $annotation */
        $annotation = $this->annotationReader->getPropertyAnnotation(
            $property,
            'App\Annotation\RemoveMarkup'
        );

        return !is_null($annotation);
    }

    /**
     * Load Entities by scanning the file system
     * then use that list to discover those which have the
     * correct annotation
     *
     * @param KernelInterface $kernel
     *
     * @return array
     */
    protected function findIliosEntities(KernelInterface $kernel)
    {
        $path = $kernel->getProjectDir() . '/src/Entity';
        $finder = new Finder();
        $files = $finder->in($path)->files()->depth("== 0")->notName('*Interface.php')->sortByName();

        $list = [];
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $class = 'App\\Entity' . '\\' . $file->getBasename('.php');
            $annotation = $this->annotationReader->getClassAnnotation(
                new ReflectionClass($class),
                'App\Annotation\Entity'
            );
            if (null !== $annotation) {
                $list[] = $class;
            }
        }

        return $list;
    }

    /**
     * Load classes by scanning directories then use
     * that list to discover classes which have the DTO annotation
     *
     * @param KernelInterface $kernel
     *
     * @return array
     */
    protected function findIliosDtos(KernelInterface $kernel)
    {
        $dtoPath = $path = $kernel->getProjectDir() . '/src/Entity/DTO';
        $finder = new Finder();
        $files = $finder->in($dtoPath)->files()->depth("== 0")->sortByName();

        $list = [];
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $class = 'App\\Entity\\DTO' . '\\' . $file->getBasename('.php');
            $annotation = $this->annotationReader->getClassAnnotation(
                new ReflectionClass($class),
                'App\Annotation\DTO'
            );
            if (null !== $annotation) {
                $list[] = $class;
            }
        }

        $classPath = $kernel->getProjectDir() . '/src/Classes';
        $finder = new Finder();
        $files = $finder->in($classPath)->files()->depth("== 0")->sortByName();

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $class = 'App\\Classes' . '\\' . $file->getBasename('.php');
            $annotation = $this->annotationReader->getClassAnnotation(
                new ReflectionClass($class),
                'App\Annotation\DTO'
            );
            if (null !== $annotation) {
                $list[] = $class;
            }
        }

        return $list;
    }
}
