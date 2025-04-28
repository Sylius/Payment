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

namespace Tests\Sylius\Component\Payment\Resolver;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolver;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

final class PaymentMethodsResolverTest extends TestCase
{
    private MockObject $methodRepositoryMock;

    private PaymentMethodsResolver $paymentMethodsResolver;

    protected function setUp(): void
    {
        $this->methodRepositoryMock = $this->createMock(RepositoryInterface::class);
        $this->paymentMethodsResolver = new PaymentMethodsResolver($this->methodRepositoryMock);
    }

    public function testImplementsMethodsResolverInterface(): void
    {
        $this->assertInstanceOf(PaymentMethodsResolverInterface::class, $this->paymentMethodsResolver);
    }

    public function testReturnsAllMethodsEnabledForGivenPayment(): void
    {
        $paymentMock = $this->createMock(PaymentInterface::class);
        $method1Mock = $this->createMock(PaymentMethodInterface::class);
        $method2Mock = $this->createMock(PaymentMethodInterface::class);

        $this->methodRepositoryMock->expects($this->once())
                                   ->method('findBy')
                                   ->with(['enabled' => true])
                                   ->willReturn([$method1Mock, $method2Mock]);

        $this->assertSame(
            [$method1Mock, $method2Mock],
            $this->paymentMethodsResolver->getSupportedMethods($paymentMock)
        );
    }

    public function testSupportsEveryPayment(): void
    {
        $paymentMock = $this->createMock(PaymentInterface::class);
        $this->assertTrue($this->paymentMethodsResolver->supports($paymentMock));
    }
}
