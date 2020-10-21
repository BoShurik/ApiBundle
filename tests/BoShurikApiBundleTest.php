<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle\Tests;

use BoShurik\ApiBundle\BoShurikApiBundle;
use BoShurik\ApiBundle\DependencyInjection\BoShurikApiExtension;
use PHPUnit\Framework\TestCase;

class BoShurikApiBundleTest extends TestCase
{
    public function testGetContainerExtension(): void
    {
        $bundle = new BoShurikApiBundle();

        $this->assertInstanceOf(BoShurikApiExtension::class, $bundle->getContainerExtension());
        $this->assertSame('boshurik_api', $bundle->getContainerExtension()->getAlias());
    }
}
