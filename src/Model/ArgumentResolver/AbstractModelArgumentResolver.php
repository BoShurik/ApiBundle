<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle\Model\ArgumentResolver;

use BoShurik\ApiBundle\Validator\Exception\ValidationException;
use BoShurik\ApiBundle\Validator\ValidationGroupsAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractModelArgumentResolver implements ArgumentValueResolverInterface
{
    private SerializerInterface $serializer;
    private DenormalizerInterface $denormalizer;
    private ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer, DenormalizerInterface $denormalizer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @psalm-var class-string */
        $type = $argument->getType();

        if ($request->getMethod() == 'GET') {
            $model = $this->denormalizer->denormalize($request->query->all(), $type);
        } else {
            if (!$content = $request->getContent()) {
                $model = $this->denormalizer->denormalize($request->request->all(), $type);
            } else {
                $model = $this->serializer->deserialize($content, $type, 'json');
            }
        }

        if (\is_object($model) && property_exists($model, 'id') && $request->attributes->has('id')) {
            $model->id = $request->attributes->get('id');
        }

        if (!$request->attributes->get('validation', true)) {
            yield $model;

            return;
        }

        /** @var string[] $validationGroups */
        $validationGroups = $request->attributes->get('validation_groups', []);
        if ($model instanceof ValidationGroupsAwareInterface) {
            $validationGroups = array_unique(array_merge($validationGroups, $model->validationGroups()));
        }
        if (empty($validationGroups)) {
            $validationGroups = null;
        }
        $violations = $this->validator->validate($model, null, $validationGroups);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        yield $model;
    }
}
