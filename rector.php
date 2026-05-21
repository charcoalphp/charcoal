<?php

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\ValueObject\PhpVersion;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Doctrine\Set\DoctrineSetList;

return RectorConfig::configure()
    ->withComposerBased(phpunit: true, symfony: true)
    ->withPaths([
        __DIR__ . '/tests',
        __DIR__ . '/packages/*/src',
        __DIR__ . '/packages/*/tests',
    ])->withSkip([
        __DIR__ . '/packages/*/tests/*/*/Fixture/*',
    ])
//    ->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml')
    ->withSets([
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ])
    ->withAttributesSets(
        symfony: true,
        doctrine: true
    )
    ->withPhpVersion(PhpVersion::PHP_85)
    ->withSets([
        \Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_85,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::PHPUNIT_120
    ])
    ->withRules([
//        SerializableToSerializeRector::class
//        \Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector::class,
//        \Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,
    ])
    ->withConfiguredRule(\Rector\Removing\Rector\Class_\RemoveInterfacesRector::class, [
        'Serializable',
    ])
    ->withPreparedSets(typeDeclarations: true, deadCode: true, codeQuality: true)
    ->withPhpSets(php85: true);
