<?php

namespace Uniter1;

class ClassHelper
{
    public static function makeAllMethodsPublic(string $fullyQualifiedClassName): string
    {
        $classNameExploded = explode('\\', $fullyQualifiedClassName);
        $className = array_pop($classNameExploded);

        $loader = require './vendor/autoload.php';
        $classFilePath = $loader->findFile($fullyQualifiedClassName);

        $classBody = file_get_contents($classFilePath);

        $proxyFileName = "${className}" . uniqid();

        $proxyClassBody = preg_replace(
            ["/class\s+${className}/i", '/private\s+function/i'],
            ["class $proxyFileName",    'public function'],
            $classBody
        );

        $fileName = __DIR__ . "/${proxyFileName}.php";

        file_put_contents($fileName, $proxyClassBody);

        include $fileName;

        unlink($fileName);

        array_push($classNameExploded, $proxyFileName);

        return implode('\\', $classNameExploded);
    }
}