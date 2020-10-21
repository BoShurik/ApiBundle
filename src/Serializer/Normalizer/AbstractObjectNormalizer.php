<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle\Serializer\Normalizer;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

abstract class AbstractObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const FULL_OBJECTS = 'full_objects';

    private ObjectManager $objectManager;
    private ObjectNormalizer $objectNormalizer;
    private array $defaultObjectNormalizerContext;

    public function __construct(ObjectManager $objectManager, ObjectNormalizer $objectNormalizer, array $defaultObjectNormalizerContext = [])
    {
        $this->objectManager = $objectManager;
        $this->objectNormalizer = $objectNormalizer;
        $this->defaultObjectNormalizerContext = $defaultObjectNormalizerContext;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if (\in_array($this->getClass(), (array) ($context[self::FULL_OBJECTS] ?? []))) {
            return $this->objectNormalizer->normalize($object, $format, array_merge($this->defaultObjectNormalizerContext, $context));
        }

        return $object->{$this->identifierGetter()}();
    }

    /**
     * @psalm-suppress InvalidReturnStatement, InvalidReturnType
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->objectManager->getRepository($this->getClass())->find($data);
    }

    public function supportsNormalization($data, string $format = null)
    {
        $class = $this->getClass();

        return $data instanceof $class;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === $this->getClass() && \is_string($data);
    }

    protected function identifierGetter(): string
    {
        return 'getId';
    }

    /**
     * @psalm-return class-string
     */
    abstract protected function getClass(): string;
}
