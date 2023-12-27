<?php

namespace Crescat\SaloonSdkGenerator\Generators;

use cebe\openapi\spec\Type;
use Crescat\SaloonSdkGenerator\Contracts\Deserializable;
use Crescat\SaloonSdkGenerator\Data\Generator\ApiSpecification;
use Crescat\SaloonSdkGenerator\Data\Generator\Schema;
use Crescat\SaloonSdkGenerator\Generator;
use Crescat\SaloonSdkGenerator\Helpers\NameHelper;
use Crescat\SaloonSdkGenerator\Traits\Deserializes;
use Nette\PhpGenerator\PhpFile;

class DtoGenerator extends Generator
{
    public function generate(ApiSpecification $specification): PhpFile|array
    {
        $classes = [];

        foreach ($specification->schemas as $schema) {
            if ($schema->type === Type::ARRAY || Type::isScalar($schema->type)) {
                continue;
            }
            $classes[] = $this->generateDtoClass($schema);
        }

        return $classes;
    }

    public function generateDtoClass(Schema $schema): PhpFile
    {
        $className = NameHelper::dtoClassName($schema->name);
        [$classFile, $namespace, $classType] = $this->makeClass($className, $this->config->dtoNamespaceSuffix);

        $namespace
            ->addUse(Deserializes::class)
            ->addUse(Deserializable::class);
        $classType
            ->setFinal()
            ->addImplement(Deserializable::class)
            ->addTrait(Deserializes::class);

        $classConstructor = $classType->addMethod('__construct');

        foreach ($schema->properties as $property) {
            $name = NameHelper::safeVariableName($property->name);
            $param = $classConstructor
                ->addComment(
                    trim(sprintf(
                        '@param %s $%s %s',
                        $property->getDocTypeString(),
                        $name,
                        $property->description
                    ))
                )
                ->addPromotedParameter($name);

            $type = $property->type;
            if (! Type::isScalar($type)) {
                $type = "{$namespace->getName()}\\{$type}";
            }
            $param
                ->setReadOnly()
                ->setType($type)
                ->setNullable($property->nullable)
                ->setPublic();

            if ($property->nullable) {
                $param->setDefaultValue(null);
            }
        }

        return $classFile;
    }
}
