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

namespace Tests\Sylius\Component\Payment\Encryption;

use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Payment\Encryption\EncrypterInterface;
use Sylius\Component\Payment\Encryption\EntityEncrypterInterface;
use Sylius\Component\Payment\Encryption\PaymentRequestEncrypter;
use Sylius\Component\Payment\Model\PaymentRequestInterface;

final class PaymentRequestEncrypterTest extends TestCase
{
    private MockObject $encrypterMock;

    private PaymentRequestEncrypter $paymentRequestEncrypter;

    protected function setUp(): void
    {
        $this->encrypterMock = $this->createMock(EncrypterInterface::class);
        $this->paymentRequestEncrypter = new PaymentRequestEncrypter($this->encrypterMock);
    }

    public function testAnEntityEncrypter(): void
    {
        $this->assertInstanceOf(EntityEncrypterInterface::class, $this->paymentRequestEncrypter);
    }

    public function testDoesNothingWhenEncryptingPaymentRequestWithNoPayloadAndEmptyResponseData(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->once())
                           ->method('getPayload')
                           ->willReturn(null);
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $this->encrypterMock->expects($this->never())
                            ->method('encrypt');
        $paymentRequestMock->expects($this->once())
                           ->method('setResponseData')
                           ->with([]);

        $this->paymentRequestEncrypter->encrypt($paymentRequestMock);
    }

    public function testEncryptsScalarPayload(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn('payload');
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $this->encrypterMock->expects($this->once())
                            ->method('encrypt')
                            ->with(serialize('payload'))
                            ->willReturn('encrypted_payload');
        $paymentRequestMock->expects($this->once())
                           ->method('setPayload')
                           ->with('encrypted_payload');
        $paymentRequestMock->expects($this->once())
                           ->method('setResponseData')
                           ->with([]);
        $this->paymentRequestEncrypter->encrypt($paymentRequestMock);
    }

    public function testEncryptsArrayPayload(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn(['key' => 'value']);
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $this->encrypterMock->expects($this->once())
                            ->method('encrypt')
                            ->with(serialize(['key' => 'value']))
                            ->willReturn('encrypted_payload');
        $paymentRequestMock->expects($this->once())
                           ->method('setPayload')
                           ->with('encrypted_payload');
        $paymentRequestMock->expects($this->once())
                           ->method('setResponseData')
                           ->with([]);

        $this->paymentRequestEncrypter->encrypt($paymentRequestMock);
    }

    public function testEncryptsObjectPayload(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);
        $object = new stdClass();

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn($object);
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $this->encrypterMock->expects($this->once())
                            ->method('encrypt')
                            ->with(serialize($object))
                            ->willReturn('encrypted_payload');
        $paymentRequestMock->expects($this->once())
                           ->method('setPayload')
                           ->with('encrypted_payload');
        $paymentRequestMock->expects($this->once())
                           ->method('setResponseData')
                           ->with([]);

        $this->paymentRequestEncrypter->encrypt($paymentRequestMock);
    }

    public function testEncryptsScalarValuesInResponseData(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->once())
                           ->method('getPayload')
                           ->willReturn(null);
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn(['key' => 'value']);
        $this->encrypterMock->expects($this->once())
                            ->method('encrypt')
                            ->with(serialize('value'))
                            ->willReturn('encrypted_value');
        $paymentRequestMock->expects($this->never())
                           ->method('setPayload')
                           ->with(null);
        $paymentRequestMock->expects($this->once())
                           ->method('setResponseData')
                           ->with(['key' => 'encrypted_value']);

        $this->paymentRequestEncrypter->encrypt($paymentRequestMock);
    }

    public function testEncryptsArrayValuesInResponseData(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->once())
                           ->method('getPayload')
                           ->willReturn(null);
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn(['key' => ['value', 'some_other_value']]);
        $this->encrypterMock->expects($this->once())
                            ->method('encrypt')
                            ->with(serialize(['value', 'some_other_value']))
                            ->willReturn('encrypted_value');
        $paymentRequestMock->expects($this->never())
                           ->method('setPayload')
                           ->with(null);
        $paymentRequestMock->expects($this->once())
                           ->method('setResponseData')
                           ->with(['key' => 'encrypted_value']);

        $this->paymentRequestEncrypter->encrypt($paymentRequestMock);
    }

    public function testDoesNothingWhenDecryptingPaymentRequestWithNoPayloadAndEmptyResponseData(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->once())
                           ->method('getPayload')
                           ->willReturn(null);
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $this->encrypterMock->expects($this->never())
                            ->method('decrypt');
        $paymentRequestMock->expects($this->never())
                           ->method('setResponseData');

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }

    public function testDoesNotDecryptIfPaymentRequestIsNotString(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn(['array']);
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $this->encrypterMock->expects($this->never())
                            ->method('decrypt');
        $paymentRequestMock->expects($this->never())
                           ->method('setResponseData');

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }

    public function testDoesNotDecryptPaymentRequestIsNotEncryptedString(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn('not_encrypted_payload');
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $this->encrypterMock->expects($this->never())
                            ->method('decrypt');
        $paymentRequestMock->expects($this->never())
                           ->method('setResponseData');

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }

    public function testDecryptsScalarPayload(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn('encrypted_payload#ENCRYPTED');
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $this->encrypterMock->expects($this->once())
                            ->method('decrypt')
                            ->with('encrypted_payload#ENCRYPTED')
                            ->willReturn(serialize('payload'));
        $paymentRequestMock->expects($this->once())
                           ->method('setPayload')
                           ->with('payload');
        $paymentRequestMock->expects($this->never())
                           ->method('setResponseData');

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }

    public function testDecryptsArrayPayload(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn('encrypted_payload#ENCRYPTED');
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $this->encrypterMock->expects($this->once())
                            ->method('decrypt')
                            ->with('encrypted_payload#ENCRYPTED')
                            ->willReturn(serialize(['key' => 'value']));
        $paymentRequestMock->expects($this->once())
                           ->method('setPayload')
                           ->with(['key' => 'value']);
        $paymentRequestMock->expects($this->never())
                           ->method('setResponseData');

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }

    public function testDecryptsObjectPayload(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn('encrypted_payload#ENCRYPTED');
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([]);
        $object = new stdClass();
        $this->encrypterMock->expects($this->once())
                            ->method('decrypt')
                            ->with('encrypted_payload#ENCRYPTED')
                            ->willReturn(serialize($object));
        $paymentRequestMock->expects($this->once())
                           ->method('setPayload')
                           ->with($object);
        $paymentRequestMock->expects($this->never())
                           ->method('setResponseData');

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }

    public function testDoesNotDecryptResponseDataWhenItsElementsAreNotStrings(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->once())
                           ->method('getPayload')
                           ->willReturn(null);
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([
                               'array' => ['value'],
                               'array-two' => ['value'],
                           ]);
        $this->encrypterMock->expects($this->never())
                            ->method('decrypt');
        $paymentRequestMock->expects($this->never())
                           ->method('setPayload')
                           ->with(null);
        $paymentRequestMock->expects($this->never())
                           ->method('setResponseData');

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }

    public function testDoesNotDecryptResponseDataWhenItsElementsAreNotEncryptedStrings(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->once())
                           ->method('getPayload')
                           ->willReturn(null);
        $paymentRequestMock->expects($this->once())
                           ->method('getResponseData')
                           ->willReturn([
                               'key' => 'not_encrypted_value',
                               'key-two' => 'not_encrypted_value',
                           ]);
        $this->encrypterMock->expects($this->never())
                            ->method('decrypt');
        $paymentRequestMock->expects($this->never())
                           ->method('setPayload')->with(null);
        $paymentRequestMock->expects($this->never())
                           ->method('setResponseData');

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }

    public function testDecryptsScalarValuesInResponseData(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn(null);
        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getResponseData')
                           ->willReturn(['key' => 'encrypted_value#ENCRYPTED']);
        $this->encrypterMock->expects($this->once())
                            ->method('decrypt')
                            ->with('encrypted_value#ENCRYPTED')
                            ->willReturn(serialize('value'));
        $paymentRequestMock->expects($this->never())
                           ->method('setPayload')
                           ->with(null);
        $paymentRequestMock->expects($this->once())
                           ->method('setResponseData')
                           ->with(['key' => 'value']);

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }

    public function testDecryptsArrayValuesInResponseData(): void
    {
        $paymentRequestMock = $this->createMock(PaymentRequestInterface::class);

        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getPayload')
                           ->willReturn(null);
        $paymentRequestMock->expects($this->atLeastOnce())
                           ->method('getResponseData')
                           ->willReturn(['key' => 'encrypted_value#ENCRYPTED']);
        $this->encrypterMock->expects($this->once())
                            ->method('decrypt')
                            ->with('encrypted_value#ENCRYPTED')
                            ->willReturn(serialize(['value', 'some_other_value']));
        $paymentRequestMock->expects($this->never())
                           ->method('setPayload')->with(null);
        $paymentRequestMock->expects($this->once())
                           ->method('setResponseData')
                           ->with(['key' => ['value', 'some_other_value']]);

        $this->paymentRequestEncrypter->decrypt($paymentRequestMock);
    }
}
