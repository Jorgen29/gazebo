<?php

namespace Tests\Unit;

use App\Contracts\XenditGateway;
use App\Services\Xendit\XenditInvoiceGateway;
use Tests\TestCase;
use Xendit\Invoice\InvoiceApi;

class XenditGatewayTest extends TestCase
{
    public function test_fake_gateway_binding_can_be_used_in_tests(): void
    {
        $fake = new class implements XenditGateway {
            public array $created = [];
            public function createInvoice(array $data): array
            {
                $this->created[] = $data;
                return [
                    'id' => 'inv_test_123',
                    'invoice_url' => 'https://checkout.test/inv_test_123',
                    'status' => 'PENDING',
                    'expires_at' => '2026-11-13T00:00:00.000Z',
                    'paid_at' => null,
                    'payment_method' => null,
                    'raw' => ['id' => 'inv_test_123'],
                ];
            }
            public function getInvoice(string $invoiceId): array
            {
                return [
                    'id' => $invoiceId,
                    'invoice_url' => 'https://checkout.test/'.$invoiceId,
                    'status' => 'PAID',
                    'expires_at' => null,
                    'paid_at' => '2026-11-12T01:00:00.000Z',
                    'payment_method' => 'EWALLET',
                    'raw' => ['id' => $invoiceId, 'status' => 'PAID'],
                ];
            }
            public function expireInvoice(string $invoiceId): void {}
        };

        $this->app->instance(XenditGateway::class, $fake);
        $resolved = app(XenditGateway::class);

        $result = $resolved->createInvoice([
            'external_id' => 'inq-1-pay-1',
            'amount' => 5000,
            'payer_email' => 'a@example.com',
            'description' => 'Booking payment',
            'invoice_duration' => 86400,
            'currency' => 'PHP',
        ]);

        $this->assertSame($fake, $resolved);
        $this->assertSame('inv_test_123', $result['id']);
        $this->assertSame('https://checkout.test/inv_test_123', $result['invoice_url']);
        $this->assertSame('PENDING', $result['status']);
        $this->assertSame('inq-1-pay-1', $fake->created[0]['external_id']);
        $this->assertSame(5000, $fake->created[0]['amount']);
    }

    public function test_xendit_invoice_gateway_maps_sdk_create_and_get_results(): void
    {
        if (! class_exists(InvoiceApi::class)) {
            $this->markTestSkipped('xendit/xendit-php not installed');
        }

        $api = $this->getMockBuilder(InvoiceApi::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createInvoice', 'getInvoiceById'])
            ->getMock();

        $invoiceObj = (object) [
            'id' => 'inv_sdk_1',
            'invoice_url' => 'https://checkout.xendit/inv_sdk_1',
            'status' => 'PENDING',
            'expiry_date' => '2026-11-13T00:00:00.000Z',
        ];
        $paidObj = (object) [
            'id' => 'inv_sdk_1',
            'invoice_url' => 'https://checkout.xendit/inv_sdk_1',
            'status' => 'PAID',
            'paid_at' => '2026-11-12T02:00:00.000Z',
            'payment_method' => 'BANK_TRANSFER',
        ];

        $api->expects($this->once())
            ->method('createInvoice')
            ->with($this->callback(function ($req) {
                return $req instanceof \Xendit\Invoice\CreateInvoiceRequest;
            }))
            ->willReturn($invoiceObj);

        $api->expects($this->once())
            ->method('getInvoiceById')
            ->with('inv_sdk_1')
            ->willReturn($paidObj);

        config(['services.xendit.secret_key' => 'xnd_test_key']);
        $gateway = new XenditInvoiceGateway($api);

        $created = $gateway->createInvoice([
            'external_id' => 'ext-1',
            'amount' => 1234.56,
            'payer_email' => 'payer@example.com',
            'description' => 'Test',
            'invoice_duration' => 86400,
            'currency' => 'PHP',
        ]);
        $fetched = $gateway->getInvoice('inv_sdk_1');

        $this->assertSame('inv_sdk_1', $created['id']);
        $this->assertSame('https://checkout.xendit/inv_sdk_1', $created['invoice_url']);
        $this->assertSame('PENDING', $created['status']);
        $this->assertSame('2026-11-13T00:00:00.000Z', $created['expires_at']);

        $this->assertSame('PAID', $fetched['status']);
        $this->assertSame('BANK_TRANSFER', $fetched['payment_method']);
        $this->assertSame('2026-11-12T02:00:00.000Z', $fetched['paid_at']);
    }
}