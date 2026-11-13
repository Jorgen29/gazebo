<?php

namespace App\Contracts;

use App\Models\Payment;

interface XenditGateway
{
    /**
     * @param array{
     *   external_id:string,
     *   amount:float|int|string,
     *   payer_email:?string,
     *   description:string,
     *   invoice_duration:int,
     *   currency:string
     * } $data
     * @return array{id:string,invoice_url:?string,status:?string,expires_at?:mixed,payment_method?:?string,raw:mixed}
     */
    public function createInvoice(array $data): array;

    /**
     * @return array{id:string,invoice_url:?string,status:?string,expires_at?:mixed,payment_method?:?string,paid_at?:mixed,raw:mixed}
     */
    public function getInvoice(string $invoiceId): array;

    public function expireInvoice(string $invoiceId): void;
}