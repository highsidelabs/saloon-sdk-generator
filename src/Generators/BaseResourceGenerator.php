<?php

declare(strict_types=1);

namespace Crescat\SaloonSdkGenerator\Generators;

use Crescat\SaloonSdkGenerator\Data\Generator\ApiSpecification;
use Crescat\SaloonSdkGenerator\Generator;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Saloon\Http\Connector;

class BaseResourceGenerator extends Generator
{
    public static string $baseClsName = 'BaseResource';

    public function generate(ApiSpecification $specification): PhpFile|array
    {
        $classType = new ClassType(static::$baseClsName);
        $classType
            ->addMethod('__construct')
            ->addPromotedParameter('connector')
            ->setType(Connector::class)
            ->setProtected();

        $classFile = new PhpFile();
        $namespace = $this->config->baseFilesNamespace();
        $classFile->addNamespace($namespace)
            ->addUse(Connector::class)
            ->add($classType);

        return $classFile;
    }
}
