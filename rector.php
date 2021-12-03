<?php

declare(strict_types=1);

use Rector\Set\ValueObject\SetList;
use Rector\Core\Configuration\Option;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
   // get parameters
   $parameters = $containerConfigurator->parameters();
   $parameters->set(Option::PATHS, [
       __DIR__ . '/src'
   ]);
   $parameters->set(
       Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
       __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'
   );

   // Define what rule sets will be applied
   $containerConfigurator->import(SetList::DEAD_CODE);
   $containerConfigurator->import(SetList::CODE_QUALITY);

   $containerConfigurator->import(SymfonySetList::SYMFONY_52);
   $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
   $containerConfigurator->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
};
