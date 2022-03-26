<?php

namespace App\Http\Controllers\Backend\Content;

use App\Http\Controllers\Controller;
use App\Models\Content\BkashPaymentAgreement;
use App\Models\Content\Order;
use App\Models\Content\Setting;
use App\Traits\BkashApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BkashApiResponseController extends Controller
{
    use BkashApiResponse;

    public function index()
    {
        return view('backend.content.bkash.apiResponse');
    }

    public function grant_token()
    {
        session()->forget('bkash_token');
        $id_token = $this->initializeBkashToken(true);
        Setting::save_settings([
            'bkash_grant_token' => json_encode($id_token)
        ]);
        return redirect()->back()->withFlashSuccess('Grant token generated successfully');
    }

    public function create()
    {
        $order = Order::where('needToPay', '<', '200')->latest()->first();
        $body = [
            'amount' => ceil($order->needToPay),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $order->id,
        ];

        $create_payment = $this->CreatePayment($body);

        Setting::save_settings([
            'bkash_create_api' => json_encode($create_payment),
        ]);
        return redirect()->back()->withFlashSuccess('Bkash Create API successfully');
    }

    public function executeApi()
    {
        $create = get_setting('bkash_create_api');
        if (!$create) {
            return redirect()->back()->withFlashDanger('create api Not generated');
        }
        $create = json_decode($create, true);
        $paymentID = getArrayKeyData($create, 'paymentID');

        $response = $this->ExecutePayment($paymentID);

        Setting::save_settings([
            'bkash_execute_api' => json_encode($response),
        ]);
        return redirect()->back()->withFlashSuccess('Bkash Execute API successfully');
    }

    public function queryApi()
    {
        $create = get_setting('bkash_create_api');
        if (!$create) {
            return redirect()->back()->withFlashDanger('create api Not generated');
        }
        $create = json_decode($create, true);
        $paymentID = getArrayKeyData($create, 'paymentID');

        $response = $this->QueryPayment($paymentID);
        Setting::save_settings([
            'bkash_query_payment' => json_encode($response),
        ]);
        return redirect()->back()->withFlashSuccess('Query Payment API successfully');
    }

    public function searchApi()
    {
        $execute = get_setting('bkash_execute_api');
        if (!$execute) {
            return redirect()->back()->withFlashDanger('execute api Not generated');
        }
        $execute = json_decode($execute, true);
        $trxID = getArrayKeyData($execute, 'trxID');
        $response = $this->SearchTransaction($trxID);
        Setting::save_settings([
            'bkash_search_api' => json_encode($response),
        ]);
        return redirect()->back()->withFlashSuccess('Query Payment API successfully');
    }

    public function refundApi()
    {
        $execute = get_setting('bkash_execute_api');
        if (!$execute) {
            return redirect()->back()->withFlashDanger('Refund api Not generated');
        }
        $execute = json_decode($execute, true);
        $response = $this->RefundTransaction($execute);
        Setting::save_settings([
            'bkash_refund_api' => json_encode($response),
        ]);
        return redirect()->back()->withFlashSuccess('Refund API successfully');
    }

    public function refundStatusApi()
    {
        $execute = get_setting('bkash_execute_api');
        if (!$execute) {
            return redirect()->back()->withFlashDanger('Refund api not generated');
        }
        $execute = json_decode($execute, true);
        $response = $this->RefundStatus($execute);
        Setting::save_settings([
            'bkash_refund_status_api' => json_encode($response),
        ]);
        return redirect()->back()->withFlashSuccess('Refund status API successfully');
    }
}