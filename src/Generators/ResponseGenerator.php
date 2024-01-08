<?php

declare(strict_types=1);

namespace Crescat\SaloonSdkGenerator\Generators;

use Crescat\SaloonSdkGenerator\BaseResponse;
use Crescat\SaloonSdkGenerator\Data\Generator\ApiSpecification;
use Crescat\SaloonSdkGenerator\Data\Generator\Schema;
use Crescat\SaloonSdkGenerator\Enums\SimpleType;
use Crescat\SaloonSdkGenerator\Generator;
use Crescat\SaloonSdkGenerator\Helpers\MethodGeneratorHelper;
use Crescat\SaloonSdkGenerator\Helpers\NameHelper;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;

class ResponseGenerator extends Generator
{
    public function generate(ApiSpecification $specification): PhpFile|array
    {
        $classes = [];

        foreach ($specification->responses as $response) {
            $classes[] = $this->generateResponseClass($response);
        }

        return $classes;
    }

    public function generateResponseClass(Schema $schema): PhpFile
    {
        $className = NameHelper::responseClassName($schema->name);
        [$classFile, $namespace, $classType] = $this->makeClass($className, $this->config->responseNamespaceSuffix);

        $namespace->addUse(BaseResponse::class);

        $classType
            ->setFinal()
            ->setExtends(BaseResponse::class);

        $classConstructor = $classType->addMethod('__construct');

        $dtoNamespace = $this->config->dtoNamespace();
        $complexArrayTypes = [];

        if ($schema->type === SimpleType::ARRAY->value) {
            $schema->items->name = NameHelper::safeVariableName($schema->name);
            MethodGeneratorHelper::addParameterToMethod(
                $classConstructor,
                $schema,
                namespace: $dtoNamespace,
                promote: true,
                visibility: 'public',
                readonly: true,
            );

            $safeName = NameHelper::safeVariableName($schema->name);
            $complexArrayTypes[$safeName] = NameHelper::dtoClassName($schema->items->type);
        } else {
            foreach ($schema->properties as $parameterName => $property) {
                MethodGeneratorHelper::addParameterToMethod(
                    $classConstructor,
                    $property,
                    namespace: $dtoNamespace,
                    promote: true,
                    visibility: 'public',
                    readonly: true,
                );

                $type = $property->type;
                if (! SimpleType::tryFrom($type)) {
                    $safeType = NameHelper::dtoClassName($type);
                    $type = "{$dtoNamespace}\\{$safeType}";
                    $namespace->addUse($type);
                }

                $safeName = NameHelper::safeVariableName($parameterName);
                if ($property->type === SimpleType::ARRAY && $property->items) {
                    $complexArrayTypes[$safeName] = NameHelper::dtoClassName($property->items->type);
                }
            }
        }

        if (count($complexArrayTypes) > 0) {
            foreach ($complexArrayTypes as $name => $type) {
                $dtoFQN = "{$dtoNamespace}\\{$type}";
                $namespace->addUse($dtoFQN);

                $literalType = new Literal(sprintf('%s::class', $type));
                $complexArrayTypes[$name] = [$literalType];
            }
            $classType->addProperty('complexArrayTypes', $complexArrayTypes)
                ->setStatic()
                ->setType('array')
                ->setProtected();
        }

        return $classFile;
    }
}
