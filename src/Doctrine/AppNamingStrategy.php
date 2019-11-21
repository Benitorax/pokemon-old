<?php
namespace App\Doctrine;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

class AppNamingStrategy extends UnderscoreNamingStrategy
{
    private $case;

    /**
     * Underscore naming strategy construct.
     *
     * @param integer $case CASE_LOWER | CASE_UPPER
     */
    public function __construct($case = CASE_LOWER)
    {
        $this->case = $case;
    }
    
    public function classToTableName($className): string
    {
        if (strpos($className, '\\') !== false) {
            $className = 'pokemon_' . substr($className, strrpos($className, '\\') + 1);
        } else {
            $className = 'pokemon_' . $className;
        }
    
        return $this->underscore($className);
    }

    private function underscore($string)
    {
        $string = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string);

        if ($this->case === CASE_UPPER) {
            return strtoupper($string);
        }

        return strtolower($string);
    }
}