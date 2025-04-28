<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\Component\Payment\Model;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Payment\Model\GatewayConfig;
use Sylius\Component\Payment\Model\GatewayConfigInterface;

final class GatewayConfigTest extends TestCase
{
    public function testItImplementsGatewayConfigInterface(): void
    {
        $gatewayConfig = new GatewayConfig();
        $this->assertInstanceOf(GatewayConfigInterface::class, $gatewayConfig);
    }

    public function testItsGatewayNameIsMutable(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName('Offline');
        $this->assertSame('Offline', $gatewayConfig->getGatewayName());
    }

    public function testItGetsFactoryNameFromConfigIfVariableNotSet(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig(['factory' => 'Offline']);
        $this->assertSame('Offline', $gatewayConfig->getFactoryName());
    }

    public function testItsFactoryNameIsMutable(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setFactoryName('Offline');
        $this->assertSame('Offline', $gatewayConfig->getFactoryName());
    }

    public function testItsConfigIsMutable(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig(['key' => '123']);
        $this->assertSame(['key' => '123'], $gatewayConfig->getConfig());
    }
}
