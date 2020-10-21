<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle\Tests\DependencyInjection;

use BoShurik\ApiBundle\DependencyInjection\BoShurikApiExtension;
use BoShurik\ApiBundle\Model\ArgumentResolver\ModelArgumentResolver;
use BoShurik\ApiBundle\Validator\EventSubscriber\ValidationExceptionSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BoShurikApiExtensionTest extends TestCase
{
    public function testLoad()
    {
        $extension = new BoShurikApiExtension();
        $container = new ContainerBuilder();

        $extension->load([], $container);

        $this->assertTrue($container->has(ModelArgumentResolver::class));
        $this->assertTrue($container->has(ValidationExceptionSubscriber::class));

        $argumentResolvers = $container->findTaggedServiceIds('controller.argument_value_resolver');
        $this->assertArrayHasKey(ModelArgumentResolver::class, $argumentResolvers);

        $eventSubscribers = $container->findTaggedServiceIds('kernel.event_subscriber');
        $this->assertArrayHasKey(ValidationExceptionSubscriber::class, $eventSubscribers);
    }
}
