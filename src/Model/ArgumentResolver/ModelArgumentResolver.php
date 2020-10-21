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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ModelArgumentResolver extends AbstractModelArgumentResolver
{
    private string $suffix;

    public function __construct(
        SerializerInterface $serializer,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        string $suffix = 'Model'
    ) {
        parent::__construct($serializer, $denormalizer, $validator);

        $this->suffix = $suffix;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (!$type = $argument->getType()) {
            return false;
        }

        $parts = explode('\\', $type);
        $class = array_pop($parts);

        $pos = strrpos($class, $this->suffix);

        return $pos !== 0 && $pos !== false;
    }
}
