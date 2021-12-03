<?php

namespace App\Doctrine;

use Doctrine\ORM\Mapping\NamingStrategy;

class AppNamingStrategy implements NamingStrategy
{
    /**
     * @param string $className
     */
    public function classToTableName($className): string
    {
        if (strpos($className, '\\') !== false) {
            $className = 'pokemon_' . substr($className, strrpos($className, '\\') + 1);
        } else {
            $className = 'pokemon_' . $className;
        }

        return $this->underscore($className);
    }

    /**
     * @param string $propertyName
     */
    public function propertyToColumnName($propertyName, $className = null): string
    {
        return $this->underscore($propertyName);
    }

    /**
     * @param string $propertyName
     * @param string $embeddedColumnName
     * @param string $className
     * @param string $embeddedClassName
     */
    public function embeddedFieldToColumnName(
        $propertyName,
        $embeddedColumnName,
        $className = null,
        $embeddedClassName = null
    ): ?string {
        return $this->underscore($propertyName) . '_' . $embeddedColumnName;
    }

    public function referenceColumnName(): string
    {
        return 'id';
    }

    /**
     * @param string $propertyName
     * @param string $className
     */
    public function joinColumnName($propertyName, $className = null): string
    {
        return $this->underscore($propertyName) . '_' . $this->referenceColumnName();
    }

    /**
     * @param string      $sourceEntity The source entity.
     * @param string      $targetEntity The target entity.
     * @param string|null $propertyName A property name.
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null): string
    {
        return strtolower($this->classToTableName($sourceEntity) . '_' .
                $this->classToTableName($targetEntity));
    }

    /**
     * @param string      $entityName           An entity.
     * @param string|null $referencedColumnName A property.
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null): string
    {
        return strtolower($this->classToTableName($entityName) . '_' .
                ($referencedColumnName ?: $this->referenceColumnName()));
    }

    /**
     * @param string $string
     * @return string
     */
    private function underscore($string): string
    {
        $string = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string);

        return strtolower((string) $string);
    }
}
