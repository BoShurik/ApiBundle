<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle\Tests\Serializer\Normalizer;

use BoShurik\ApiBundle\Serializer\Normalizer\AbstractObjectNormalizer;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AbstractObjectNormalizerTest extends TestCase
{
    /**
     * @var ObjectManager|MockObject
     */
    private $objectManager;

    /**
     * @var ObjectNormalizer|MockObject
     */
    private $objectNormalizer;

    /**
     * @var AbstractObjectNormalizer
     */
    private $normalizer;

    public function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->objectNormalizer = $this->createMock(ObjectNormalizer::class);

        $this->normalizer = new class($this->objectManager, $this->objectNormalizer, ['default-context' => 'default']) extends AbstractObjectNormalizer {
            protected function getClass(): string
            {
                return \stdClass::class;
            }
        };
    }

    /**
     * @dataProvider supportsNormalizationProvider
     */
    public function testSupportsNormalization($data, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data));
    }

    /**
     * @dataProvider supportsDenormalizationProvider
     */
    public function testSupportsDenormalization($data, $type, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $type));
    }

    public function testNormalizeFull()
    {
        $data = new \stdClass();
        $this->objectNormalizer
            ->method('normalize')
            ->with($data, 'format', [
                'default-context' => 'default',
                AbstractObjectNormalizer::FULL_OBJECTS => [\stdClass::class],
            ])
            ->willReturn('id')
        ;

        $result = $this->normalizer->normalize($data, 'format', [
            'default-context' => 'default',
            AbstractObjectNormalizer::FULL_OBJECTS => [\stdClass::class],
        ]);

        $this->assertSame('id', $result);
    }

    public function testNormalizeId()
    {
        $data = new class() extends \stdClass {
            public function getId()
            {
                return 'id';
            }
        };

        $result = $this->normalizer->normalize($data);

        $this->assertSame('id', $result);
    }

    public function testDenormalize()
    {
        $expected = new \stdClass();

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->method('find')
            ->with('id')
            ->willReturn($expected)
        ;

        $this->objectManager
            ->method('getRepository')
            ->with(\stdClass::class)
            ->willReturn($repository)
        ;

        $result = $this->normalizer->denormalize('id', \stdClass::class);

        $this->assertSame($expected, $result);
    }

    public function supportsNormalizationProvider()
    {
        yield [new \stdClass(), true];
        yield [null, false];
    }

    public function supportsDenormalizationProvider()
    {
        yield ['id', \stdClass::class, true];
        yield [[], \stdClass::class, false];
        yield ['id', 'Foo', false];
        yield [[], 'Foo', false];
    }
}
