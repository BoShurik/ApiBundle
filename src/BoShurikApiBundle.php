<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle;

use BoShurik\ApiBundle\DependencyInjection\BoShurikApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BoShurikApiBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new BoShurikApiExtension();
        }

        return $this->extension;
    }
}
