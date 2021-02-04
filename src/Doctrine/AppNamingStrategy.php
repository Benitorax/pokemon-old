<?php
namespace App\Doctrine;

use Doctrine\ORM\Mapping\NamingStrategy;

class AppNamingStrategy implements NamingStrategy
{
    public function classToTableName($className)
    {
        if (strpos($className, '\\') !== false) {
            $className = 'pokemon_' . substr($className, strrpos($className, '\\') + 1);
        } else {
            $className = 'pokemon_' . $className;
        }
    
        return $this->underscore($className);
    }

    public function propertyToColumnName($propertyName, $className = null)
    {
        return $this->underscore($propertyName);
    }

    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null)
    {
        return $this->underscore($propertyName).'_'.$embeddedColumnName;
    }

    public function referenceColumnName()
    {
        return 'id';
    }

    public function joinColumnName($propertyName, $className = null)
    {
        return $this->underscore($propertyName). '_' . $this->referenceColumnName();
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

    // TODO 
    private function underscore($string)
    {
        $string = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string);

        return strtolower($string);
    }
}