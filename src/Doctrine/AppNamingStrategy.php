<?php
namespace App\Doctrine;

use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

class AppNamingStrategy implements NamingStrategy
{
    public function classToTableName($className)
    {
        return 'pokemon_' . substr($className, strrpos($className, '\\') + 1);
    }

    public function propertyToColumnName($propertyName, $className = null)
    {
        return $propertyName;
    }

    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null)
    {
        return $propertyName.'_'.$embeddedColumnName;
    }

    public function referenceColumnName()
    {
        return 'id';
    }

    public function joinColumnName($propertyName, $className = null)
    {
        return $propertyName . '_' . $this->referenceColumnName();
    }

    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        return strtolower($this->classToTableName($sourceEntity) . '_' .
                $this->classToTableName($targetEntity));
    }

    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        return strtolower($this->classToTableName($entityName) . '_' .
                ($referencedColumnName ?: $this->referenceColumnName()));
    }
}