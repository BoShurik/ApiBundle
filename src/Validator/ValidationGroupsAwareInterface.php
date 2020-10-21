<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle\Validator;

interface ValidationGroupsAwareInterface
{
    /**
     * @return string[]
     */
    public function getValidationGroups(): array;
}
