<?php

declare(strict_types=1);

namespace PhpUniter\PhpUniterPackage\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpUniter\PhpUniterPackage\Exceptions\ClassNotFound;
use PhpUniter\PhpUniterPackage\PhpUnitTestHelper;
use PhpUniter\PhpUniterPackage\Tests\Unit\Fixtures\MethodAccess;

/**
 * Class PhpUnitTestHelperTest.
 */
class PhpUnitTestHelperTest extends TestCase
{
    /**
     * @covers \PhpUnitTestHelper::makeAllMethodsPublic
     */
    public function testMakeAllMethodsPublic(): void
    {
        $className = PhpUnitTestHelper::makeAllMethodsPublic(MethodAccess::class);

        $this->assertEquals('a', (new $className())->publicFunction('a'));
        $this->assertEquals('a', (new $className())->protectedFunction('a'));
        $this->assertEquals('a', (new $className())->privateFunction('a'));

        $this->assertEquals('a', $className::publicStaticFunction('a'));
        $this->assertEquals('a', $className::protectedStaticFunction('a'));
        $this->assertEquals('a', $className::privateStaticFunction('a'));
    }

    /**
     * @covers \PhpUnitTestHelper::getProxyClassName
     */
    public function testGetProxyClassName(): void
    {
        $className = PhpUnitTestHelper::makeAllMethodsPublic(PhpUnitTestHelper::class);

        $this->assertEquals('a\b\c', $className::getProxyClassName(['a', 'b'], 'c'));
    }

    /**
     * @covers \PhpUnitTestHelper::getClassBody
     */
    public function testGetClassBody(): void
    {
        $className = PhpUnitTestHelper::makeAllMethodsPublic(PhpUnitTestHelper::class);

        $this->assertNotEmpty($className::getClassBody(MethodAccess::class));

        $this->expectException(ClassNotFound::class);
        $className::getClassBody('NotExistsClass');
    }
}
