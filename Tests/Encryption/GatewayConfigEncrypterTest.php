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
use PHPUnit\Framework\TestCase;
use Sylius\Component\Payment\Encryption\EncrypterInterface;
use Sylius\Component\Payment\Encryption\EntityEncrypterInterface;
use Sylius\Component\Payment\Encryption\GatewayConfigEncrypter;
use Sylius\Component\Payment\Model\GatewayConfigInterface;

final class GatewayConfigEncrypterTest extends TestCase
{
    private MockObject $encrypterMock;

    private GatewayConfigEncrypter $gatewayConfigEncrypter;

    protected function setUp(): void
    {
        $this->encrypterMock = $this->createMock(EncrypterInterface::class);
        $this->gatewayConfigEncrypter = new GatewayConfigEncrypter($this->encrypterMock);
    }

    public function testAnEntityEncrypter(): void
    {
        $this->assertInstanceOf(EntityEncrypterInterface::class, $this->gatewayConfigEncrypter);
    }

    public function testDoesNothingWhenEncryptingEmptyGatewayConfig(): void
    {
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfigMock->expects($this->once())
                          ->method('getConfig')
                          ->willReturn([]);
        $this->encrypterMock->expects($this->never())
                            ->method('encrypt');
        $gatewayConfigMock->expects($this->once())
                          ->method('setConfig')
                          ->with([]);

        $this->gatewayConfigEncrypter->encrypt($gatewayConfigMock);
    }

    public function testEncryptsScalarValuesInGatewayConfig(): void
    {
        $encrypterMock = $this->createMock(EncrypterInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfigMock->expects($this->once())
                          ->method('getConfig')
                          ->willReturn(['key' => 'value']);
        $encrypterMock->expects($this->once())
                      ->method('encrypt')
                      ->with(serialize('value'))
                      ->willReturn('encrypted_value');
        $gatewayConfigMock->expects($this->once())
                          ->method('setConfig')
                          ->with(['key' => 'encrypted_value']);

        $this->gatewayConfigEncrypter = new GatewayConfigEncrypter($encrypterMock);
        $this->gatewayConfigEncrypter->encrypt($gatewayConfigMock);
    }

    public function testEncryptsArrayValuesInGatewayConfig(): void
    {
        $encrypterMock = $this->createMock(EncrypterInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfigMock->expects($this->once())
                          ->method('getConfig')
                          ->willReturn(['key' => ['value', 'some_other_value']]);
        $encrypterMock->expects($this->once())
                      ->method('encrypt')
                      ->with(serialize(['value', 'some_other_value']))
                      ->willReturn('encrypted_value');
        $gatewayConfigMock->expects($this->once())
                          ->method('setConfig')
                          ->with(['key' => 'encrypted_value']);

        $this->gatewayConfigEncrypter = new GatewayConfigEncrypter($encrypterMock);
        $this->gatewayConfigEncrypter->encrypt($gatewayConfigMock);
    }

    public function testDoesNothingWhenDecryptingEmptyGatewayConfig(): void
    {
        $encrypterMock = $this->createMock(EncrypterInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfigMock->expects($this->once())
                          ->method('getConfig')
                          ->willReturn([]);
        $encrypterMock->expects($this->never())
                      ->method('decrypt');
        $gatewayConfigMock->expects($this->never())
                          ->method('setConfig');

        $this->gatewayConfigEncrypter->decrypt($gatewayConfigMock);
    }

    public function testDoesNotDecryptConfigWhenItsElementsAreNotEncryptedStrings(): void
    {
        $encrypterMock = $this->createMock(EncrypterInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfigMock->expects($this->once())
                          ->method('getConfig')
                          ->willReturn([
                              'key' => 'not_encrypted_value',
                              'key-two' => 'not_encrypted_value',
                          ]);
        $encrypterMock->expects($this->never())
                      ->method('decrypt');
        $gatewayConfigMock->expects($this->never())
                          ->method('setConfig');

        $this->gatewayConfigEncrypter->decrypt($gatewayConfigMock);
    }

    public function testDecryptsScalarValuesInGatewayConfig(): void
    {
        $encrypterMock = $this->createMock(EncrypterInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfigMock->expects($this->atLeastOnce())
                          ->method('getConfig')
                          ->willReturn(['key' => 'encrypted_value#ENCRYPTED']);
        $encrypterMock->expects($this->once())
                      ->method('decrypt')
                      ->with('encrypted_value#ENCRYPTED')
                      ->willReturn(serialize('value'));
        $gatewayConfigMock->expects($this->once())
                          ->method('setConfig')
                          ->with(['key' => 'value']);

        $this->gatewayConfigEncrypter = new GatewayConfigEncrypter($encrypterMock);
        $this->gatewayConfigEncrypter->decrypt($gatewayConfigMock);
    }

    public function testDecryptsArrayValuesInGatewayConfig(): void
    {
        $encrypterMock = $this->createMock(EncrypterInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $gatewayConfigMock->expects($this->atLeastOnce())
                          ->method('getConfig')
                          ->willReturn([
                              'key' => 'encrypted_value#ENCRYPTED',
                              'key-two' => 'encrypted_value-two#ENCRYPTED',
                          ]);
        $encrypterMock->expects($this->exactly(2))
                      ->method('decrypt')
                      ->willReturnMap([
                          ['encrypted_value#ENCRYPTED', serialize(['value', 'some_other_value'])],
                          ['encrypted_value-two#ENCRYPTED', serialize('TWO')],
                      ]);
        $gatewayConfigMock->expects($this->once())
                          ->method('setConfig')
                          ->with(['key' => ['value', 'some_other_value'], 'key-two' => 'TWO']);

        $this->gatewayConfigEncrypter = new GatewayConfigEncrypter($encrypterMock);
        $this->gatewayConfigEncrypter->decrypt($gatewayConfigMock);
    }
}
