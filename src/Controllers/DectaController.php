<?php

namespace Dnsoftware\Decta\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Merchants\DectaPaidService;
use Dnsoftware\Decta\DectaAPI;
use Dnsoftware\Decta\DectaLogger;
use Dnsoftware\Decta\DectaPaidContract;
use Dnsoftware\Decta\Services\DectaOrderService;
use Illuminate\Http\Request;

class DectaController extends Controller
{

    public function decta_webhook_success(Request $request, DectaPaidContract $decta_paid_service)
    {
        $data = $request->all();

        $purchase_id = $data['number'];
        $payment_id = $data['id'];

        if (DectaOrderService::wasPaymentSuccessful($purchase_id, $payment_id)) {

            $decta_paid_service->markOrderAsPaid($purchase_id, $data);

        } else {
            $decta_paid_service->setPurchaseMerchantId($purchase_id, $data);
        }


    }

    public function decta_webhook_failure(Request $request, DectaPaidContract $decta_paid_service)
    {
        $data = $request->all();
        $purchase_id = $data['number'];
        $payment_id = $data['id'];

        if (!DectaOrderService::wasPaymentSuccessful($purchase_id, $payment_id)) {

            $decta_paid_service->setPurchaseMerchantId($purchase_id, $data);
        }

    }

    public function decta_return_success(Request $request, DectaPaidService $decta_paid_service) {

        $order_id = $request->input('oid');
        sleep(2);

        return $decta_paid_service->redirectAfterPaid($order_id);

    }

    public function decta_return_failure(Request $request, DectaPaidService $decta_paid_service) {
        $order_id = $request->input('oid');
        sleep(2);

        return $decta_paid_service->redirectAfterPaid($order_id);

    }

}
