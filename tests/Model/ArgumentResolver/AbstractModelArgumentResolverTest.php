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

use BoShurik\ApiBundle\Model\ArgumentResolver\AbstractModelArgumentResolver;
use BoShurik\ApiBundle\Model\ArgumentResolver\ModelArgumentResolver;
use BoShurik\ApiBundle\Model\Exception\UnexpectedValueException;
use BoShurik\ApiBundle\Validator\Exception\ValidationException;
use BoShurik\ApiBundle\Validator\ValidationGroupsAwareInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\UnexpectedValueException as SerializerUnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractModelArgumentResolverTest extends TestCase
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

        $this->argumentResolver = new class($this->serializer, $this->denormalizer, $this->validator) extends AbstractModelArgumentResolver {
            public function supports(Request $request, ArgumentMetadata $argument)
            {
                return true;
            }
        };
    }

    public function testResolveGet()
    {
        $expected = new \stdClass();

        $request = $this->createGetRequest([
            'query-field' => 'query-value',
        ], [
            'validation' => false,
        ]);

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with([
                'query-field' => 'query-value',
            ], \stdClass::class)
            ->willReturn($expected)
        ;

        $generator = $this->argumentResolver->resolve($request, $this->createArgumentMetadata());
        $model = $generator->current();
        $generator->next();

        $this->assertNull($generator->current());
        $this->assertSame($expected, $model);
    }

    public function testResolveForm()
    {
        $expected = new \stdClass();

        $request = $this->createFormRequest([
            'post-field' => 'post-value',
        ], [
            'validation' => false,
        ]);

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with([
                'post-field' => 'post-value',
            ], \stdClass::class)
            ->willReturn($expected)
        ;

        $generator = $this->argumentResolver->resolve($request, $this->createArgumentMetadata());
        $model = $generator->current();
        $generator->next();

        $this->assertNull($generator->current());
        $this->assertSame($expected, $model);
    }

    public function testResolveJson()
    {
        $expected = new \stdClass();

        $request = $this->createJsonRequest('{"content": "content-field"}', [
            'validation' => false,
        ]);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('{"content": "content-field"}', \stdClass::class)
            ->willReturn($expected)
        ;

        $generator = $this->argumentResolver->resolve($request, $this->createArgumentMetadata());
        $model = $generator->current();
        $generator->next();

        $this->assertNull($generator->current());
        $this->assertSame($expected, $model);
    }

    public function testNotResolvableValue()
    {
        $request = $this->createJsonRequest('', [
            'validation' => false,
        ]);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('', \stdClass::class, 'json')
            ->willThrowException(new SerializerUnexpectedValueException('message', 256))
        ;

        $this->expectExceptionObject(new UnexpectedValueException('message', 256));

        $generator = $this->argumentResolver->resolve($request, $this->createArgumentMetadata());
        $generator->current();
    }

    public function testModifiedId()
    {
        $expected = new \stdClass();
        $expected->id = null;

        $request = $this->createGetRequest([
            'query-field' => 'query-value',
        ], [
            'id' => 'id',
            'validation' => false,
        ]);

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with([
                'query-field' => 'query-value',
            ], \stdClass::class)
            ->willReturn($expected)
        ;

        $generator = $this->argumentResolver->resolve($request, $this->createArgumentMetadata());
        $model = $generator->current();
        $generator->next();

        $this->assertNull($generator->current());
        $this->assertSame($expected, $model);
        $this->assertSame('id', $model->id);
    }

    public function testNoValidation()
    {
        $expected = new \stdClass();

        $request = $this->createGetRequest([
            'query-field' => 'query-value',
        ], [
            'validation' => false,
        ]);

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with([
                'query-field' => 'query-value',
            ], \stdClass::class)
            ->willReturn($expected)
        ;

        $this->validator
            ->expects($this->never())
            ->method('validate')
        ;

        $generator = $this->argumentResolver->resolve($request, $this->createArgumentMetadata());
        $model = $generator->current();
        $generator->next();

        $this->assertNull($generator->current());
        $this->assertSame($expected, $model);
    }

    /**
     * @dataProvider validationProvider
     */
    public function testValidation(
        $model,
        array $arguments,
        ?array $expectedValidationGroups,
        ConstraintViolationListInterface $violations,
        bool $exception
    ) {
        $request = $this->createGetRequest([
            'query-field' => 'query-value',
        ], $arguments);

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with([
                'query-field' => 'query-value',
            ], \stdClass::class)
            ->willReturn($model)
        ;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($model, null, $expectedValidationGroups)
            ->willReturn($violations)
        ;

        if ($exception) {
            $this->expectException(ValidationException::class);
        }

        $generator = $this->argumentResolver->resolve($request, $this->createArgumentMetadata());
        $result = $generator->current();
        $generator->next();

        $this->assertNull($generator->current());
        $this->assertSame($model, $result);
    }

    public function validationProvider()
    {
        yield [new \stdClass(), [], null, $this->createViolations(), false];
        yield [new \stdClass(), [], null, $this->createViolations([
            $this->createMock(ConstraintViolationInterface::class),
        ]), true];
        yield [new class() implements ValidationGroupsAwareInterface {
            public function validationGroups(): array
            {
                return ['test'];
            }
        }, [], ['test'], $this->createViolations(), false];
        yield [new class() implements ValidationGroupsAwareInterface {
            public function validationGroups(): array
            {
                return ['test'];
            }
        }, [
            'validation_groups' => [
                'request',
                'test',
            ],
        ], ['request', 'test'], $this->createViolations(), false];
    }

    private function createArgumentMetadata(): ArgumentMetadata
    {
        return new ArgumentMetadata(
            'name',
            \stdClass::class,
            false,
            false,
            null
        );
    }

    private function createViolations(array $violations = []): ConstraintViolationListInterface
    {
        return new ConstraintViolationList($violations);
    }

    private function createGetRequest(array $query, array $attributes = [])
    {
        return new Request($query, [], $attributes, [], [], [
            'REQUEST_METHOD' => 'GET',
        ]);
    }

    private function createFormRequest(array $post, array $attributes = [])
    {
        return new Request([], $post, $attributes, [], [], [
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ], http_build_query($post));
    }

    private function createJsonRequest(string $content, array $attributes = [])
    {
        return new Request([], [], $attributes, [], [], [
            'REQUEST_METHOD' => 'POST',
        ], $content);
    }
}
