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
use Sylius\Component\Payment\Resolver\CompositeMethodsResolver;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\Component\Registry\PrioritizedServiceRegistryInterface;

final class CompositeMethodsResolverTest extends TestCase
{
    private MockObject $resolversRegistryMock;

    private CompositeMethodsResolver $compositeMethodsResolver;

    protected function setUp(): void
    {
        $this->resolversRegistryMock = $this->createMock(PrioritizedServiceRegistryInterface::class);
        $this->compositeMethodsResolver = new CompositeMethodsResolver($this->resolversRegistryMock);
    }

    public function testImplementsSyliusPaymentMethodsResolverInterface(): void
    {
        $this->assertInstanceOf(PaymentMethodsResolverInterface::class, $this->compositeMethodsResolver);
    }

    public function testUsesRegistryToProvidePaymentMethodsForPayment(): void
    {
        $firstMethodsResolverMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $secondMethodsResolverMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $paymentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $this->resolversRegistryMock->expects($this->once())
                                    ->method('all')
                                    ->willReturn([$firstMethodsResolverMock, $secondMethodsResolverMock]);
        $firstMethodsResolverMock->expects($this->once())
                                 ->method('supports')
                                 ->with($paymentMock)
                                 ->willReturn(false);
        $secondMethodsResolverMock->expects($this->once())
                                  ->method('supports')
                                  ->with($paymentMock)
                                  ->willReturn(true);
        $secondMethodsResolverMock->expects($this->once())
                                  ->method('getSupportedMethods')
                                  ->with($paymentMock)
                                  ->willReturn([$paymentMethodMock]);

        $this->assertSame(
            [$paymentMethodMock],
            $this->compositeMethodsResolver->getSupportedMethods($paymentMock)
        );
    }

    public function testReturnsEmptyArrayIfNoneOfRegisteredResolversSupportPassedPayment(): void
    {
        $firstMethodsResolverMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $secondMethodsResolverMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $this->resolversRegistryMock->expects($this->once())
                                    ->method('all')
                                    ->willReturn([$firstMethodsResolverMock, $secondMethodsResolverMock]);
        $firstMethodsResolverMock->expects($this->once())
                                 ->method('supports')
                                 ->with($paymentMock)
                                 ->willReturn(false);
        $secondMethodsResolverMock->expects($this->once())
                                  ->method('supports')
                                  ->with($paymentMock)
                                  ->willReturn(false);

        $this->assertSame(
            [],
            $this->compositeMethodsResolver->getSupportedMethods($paymentMock)
        );
    }

    public function testSupportsPaymentIfAtLeastOneRegisteredResolverSupportsIt(): void
    {
        $firstMethodsResolverMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $secondMethodsResolverMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $this->resolversRegistryMock->expects($this->once())
                                    ->method('all')
                                    ->willReturn([$firstMethodsResolverMock, $secondMethodsResolverMock]);
        $firstMethodsResolverMock->expects($this->once())
                                 ->method('supports')
                                 ->with($paymentMock)->willReturn(false);
        $secondMethodsResolverMock->expects($this->once())
                                  ->method('supports')
                                  ->with($paymentMock)->willReturn(true);

        $this->assertTrue($this->compositeMethodsResolver->supports($paymentMock));
    }

    public function testDoesNotSupportPaymentIfNoneOfRegisteredResolversSupportsIt(): void
    {
        $firstMethodsResolverMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $secondMethodsResolverMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $this->resolversRegistryMock->expects($this->once())
                                    ->method('all')
                                    ->willReturn([$firstMethodsResolverMock, $secondMethodsResolverMock]);
        $firstMethodsResolverMock->expects($this->once())
                                 ->method('supports')
                                 ->with($paymentMock)
                                 ->willReturn(false);
        $secondMethodsResolverMock->expects($this->once())
                                  ->method('supports')
                                  ->with($paymentMock)
                                  ->willReturn(false);

        $this->assertFalse($this->compositeMethodsResolver->supports($paymentMock));
    }
}
