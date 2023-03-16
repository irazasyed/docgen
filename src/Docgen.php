<?php

declare(strict_types=1);

namespace Docgen;

use Illuminate\Support\Facades\Facade;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use RuntimeException;

final class Docgen
{
    private string $class;

    private string $docBlock = '';

    public function __construct(
        private readonly array $classes,
        private readonly array $globalExcludedMethods = []
    ) {
    }

    public static function generate(string|array $classes, array $globalExcludedMethods = []): Docgen
    {
        return (new self((array) $classes, $globalExcludedMethods))->makeDocBlock();
    }

    private function makeDocBlock(): Docgen
    {
        $docBlock = "/**\n";

        foreach ($this->classes as $class => $excludedMethods) {
            if (is_int($class)) {
                $class = $excludedMethods;
                $excludedMethods = [];
            }

            if (! class_exists($class)) {
                throw new RuntimeException("Class $class does not exist.");
            }

            if ($this->isFacade($class)) {
                $class = $class::getFacadeRoot()::class;
            }

            $docBlock .= " * @see \\$class\n";
            $docBlock .= " *\n";
            $docBlock .= $this->makeMethodTags($class, $excludedMethods);
            $docBlock .= " *\n";
        }
        $docBlock .= ' */';

        $this->docBlock = $docBlock;

        return $this;
    }

    /**
     * Make method tags for the PHPDoc for all the methods of the given class.
     *
     * @param  string  $className The name of the class to generate method tags for.
     * @return string The method tags for PHPDoc block for all the methods of the given class.
     */
    private function makeMethodTags(string $className, array $excludedMethods = []): string
    {
        $this->class = $className;
        $excludedMethods = $this->globalExcludedMethods + $excludedMethods;

        $class = new ReflectionClass($className);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        $methodTags = '';
        foreach ($methods as $method) {
            if ($this->isSkippableMethod($method)) {
                continue;
            }

            if ($this->isExcludedMethod($method, $excludedMethods)) {
                continue;
            }

            $paramStrings = $this->getParamStrings($method);
            $returnString = $this->getReturnString($method);

            $methodTags .= " * @method static $returnString {$method->getName()}($paramStrings)\n";
        }

        return $methodTags;
    }

    private function isSkippableMethod(ReflectionMethod $method): bool
    {
        if ($method->isConstructor()) {
            return true;
        }

        return \str_starts_with($method->getName(), '__');
        // Magic methods
    }

    private function isExcludedMethod(ReflectionMethod $method, array $excludedMethods): bool
    {
        return in_array($method->getName(), $excludedMethods, true);
    }

    /**
     * Get the PHPDoc string for the parameters of a method.
     *
     * @param  ReflectionMethod  $method The method to get the PHPDoc string for.
     * @return string The PHPDoc string for the parameters of the method.
     */
    private function getParamStrings(ReflectionMethod $method): string
    {
        $params = $method->getParameters();
        $paramStrings = [];

        foreach ($params as $param) {
            $paramString = '';
            if (($paramType = $param->getType()) !== null) {
                $paramString .= $this->getTypeName($paramType).' ';
            }

            $paramString .= '$'.$param->getName();

            if ($param->isOptional() && $param->isDefaultValueAvailable()) {
                $defaultValue = $param->getDefaultValue();
                $paramString .= ' = '.json_encode($defaultValue, JSON_THROW_ON_ERROR);
            }

            $paramStrings[] = $paramString;
        }

        return implode(', ', $paramStrings);
    }

    /**
     * Get the name of a type, including union types.
     *
     * @param  ReflectionNamedType|ReflectionUnionType  $type The type to get the name of.
     * @return string The name of the type, including union types.
     */
    private function getTypeName(ReflectionNamedType|ReflectionUnionType $type, bool $isReturnType = false): string
    {
        if ($type instanceof ReflectionUnionType) {
            $typeNames = array_map(fn ($t): string => $this->getTypeName($t, $isReturnType), $type->getTypes());

            return implode('|', $typeNames);
        }

        $typeName = $type->getName();
        if (! $type->isBuiltin()) {
            if (in_array($typeName, ['self', 'static'], true)) {
                return '\\'.$this->class;
            }

            $typeName = '\\'.$typeName;
        }

        if ($typeName !== 'mixed' && $type->allowsNull() && ! str_starts_with($typeName, '?')) {
            return $isReturnType ? 'null|'.$typeName : '?'.$typeName;
        }

        return $typeName;
    }

    /**
     * Get the PHPDoc string for the return type of method.
     *
     * @param  ReflectionMethod  $method The method to get the PHPDoc string for.
     * @return string The PHPDoc string for the return type of the method.
     */
    private function getReturnString(ReflectionMethod $method): string
    {
        $returnString = '';

        if (($returnType = $method->getReturnType()) !== null) {
            $returnString .= $this->getTypeName($returnType, true);
        } else {
            $returnString .= 'void';
        }

        return $returnString;
    }

    public function getDocBlock(): string
    {
        return $this->docBlock;
    }

    public function apply(string $className = ''): bool
    {
        if ($className === '' || $className === '0') {
            $className = $this->classes[0];
        }

        if ($this->docBlock === '' || $this->docBlock === '0') {
            throw new RuntimeException('DocBlock is not generated yet. Call make() first.');
        }

        $reflector = new ReflectionClass($className);
        $filename = $reflector->getFileName();
        $existingDocBlock = $reflector->getDocComment();

        $contents = file_get_contents($filename);

        if ($existingDocBlock) {
            $newContents = str_replace($existingDocBlock, $this->docBlock, $contents);
        } else {
            $class = $reflector->getShortName();
            $newContents = preg_replace('/((final\s+)?class\s+'.$class.'\s+)/', $this->docBlock.PHP_EOL.'$1', $contents);
        }

        return file_put_contents($filename, $newContents) !== false;
    }

    private function isFacade(string $class): bool
    {
        return in_array(Facade::class, class_parents($class), true);
    }
}
