<?php

declare(strict_types=1);

namespace PhpUniter\PhpUniterPackage;

use PhpUniter\PhpUniterPackage\Exceptions\ClassNotFound;

/**
 * Class PhpUnitTestHelper.
 */
class PhpUnitTestHelper
{
    /**
     * @param string $fullyQualifiedClassName Fully qualified class name with namespace
     *
     * @return string|null Fully qualified proxy class name with namespace or null
     */
    public static function makeAllMethodsPublic(string $fullyQualifiedClassName): ?string
    {
        $classNameExploded = explode('\\', $fullyQualifiedClassName);
        $className = array_pop($classNameExploded);

        $proxyClassName = "${className}".uniqid();

        try {
            $proxyClassBody = self::renderProxyClass($fullyQualifiedClassName, $className, $proxyClassName);

            self::loadClass($proxyClassName, $proxyClassBody);

            $fullyQualifiedProxyClassName = self::getProxyClassName($classNameExploded, $proxyClassName);
        } catch (ClassNotFound $exception) {
            return null;
        }

        return $fullyQualifiedProxyClassName;
    }

    /**
     * @throws ClassNotFound
     */
    private static function getClassBody(string $fullyQualifiedClassName): string
    {
        $loader = require 'vendor/autoload.php';

        if ($classFilePath = $loader->findFile($fullyQualifiedClassName)) {
            if ($classBody = file_get_contents($classFilePath)) {
                return $classBody;
            }
        }

        throw new ClassNotFound("Class {$fullyQualifiedClassName} not found or not available by path $classFilePath");
    }

    /**
     * @param string $proxyFileName
     * @param string $proxyClassBody
     *
     * @psalm-suppress UnresolvableInclude
     */
    private static function loadClass(string $proxyFileName, string $proxyClassBody): void
    {
        $fileName = __DIR__."/${proxyFileName}.php";

        file_put_contents($fileName, $proxyClassBody);

        include $fileName;

        unlink($fileName);
    }

    /**
     * @param array $classNameExploded
     * @param string $proxyClassName
     * @return string
     */
    private static function getProxyClassName(array $classNameExploded, string $proxyClassName): string
    {
        array_push($classNameExploded, $proxyClassName);

        return implode('\\', $classNameExploded);
    }

    /**
     * @param string $fullyQualifiedClassName
     * @param string $className
     * @param string $proxyClassName
     * @return string
     * @throws ClassNotFound
     */
    private static function renderProxyClass(string $fullyQualifiedClassName, string $className, string $proxyClassName): string
    {
        $classBody = self::getClassBody($fullyQualifiedClassName);

        return preg_replace(
            ["/class\s+${className}/i", '/(|public|private|protected)\s+(static\s+)?function/i'],
            ["class $proxyClassName", 'public $2function'],
            $classBody
        );
    }
}
