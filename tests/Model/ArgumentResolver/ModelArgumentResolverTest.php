<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle\Tests\Model\ArgumentResolver;

use BoShurik\ApiBundle\Model\ArgumentResolver\ModelArgumentResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ModelArgumentResolverTest extends TestCase
{
    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var DenormalizerInterface|MockObject
     */
    private $denormalizer;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validator;

    /**
     * @var ModelArgumentResolver
     */
    private $argumentResolver;

    public function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->argumentResolver = new ModelArgumentResolver(
            $this->serializer,
            $this->denormalizer,
            $this->validator
        );
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports(Request $request, ArgumentMetadata $argument, bool $expected)
    {
        $this->assertSame($expected, $this->argumentResolver->supports($request, $argument));
    }

    public function supportsProvider()
    {
        yield [new Request(), $this->createArgumentMetadata('SomeModel'), true];
        yield [new Request(), $this->createArgumentMetadata('ModelModel'), true];
        yield [new Request(), $this->createArgumentMetadata('Model'), false];
        yield [new Request(), $this->createArgumentMetadata('SomeEntity'), false];
        yield [new Request(), $this->createArgumentMetadata(null), false];
    }

    private function createArgumentMetadata(?string $type): ArgumentMetadata
    {
        return new ArgumentMetadata(
            'name',
            $type,
            false,
            false,
            null
        );
    }
}
