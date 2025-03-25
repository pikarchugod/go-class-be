<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Order; // 你的 Order Model
use Carbon\Carbon;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * 發起付款：回傳一段 HTML，內含自動送出表單
     * POST /api/payment/initiate
     * Body: { "order_id": <int> }
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::findOrFail($request->order_id);

        // 若已付款，直接回應
        if ($order->status === 'paid') {
            return response()->json(['message' => '此訂單已付款'], 400);
        }

        // ECPay 測試環境參數 (可放 .env)
        $merchantId = env('ECPAY_MERCHANT_ID', '3002607');
        $hashKey    = env('ECPAY_HASH_KEY', 'pwFHCqoQZGmho4w6');
        $hashIV     = env('ECPAY_HASH_IV', 'EkRm7iFT261dpevs');
        $serviceUrl = env('ECPAY_SERVICE_URL', 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5');

        // 組合付款表單必備參數
        $tradeNo = 'ECPAY' . Str::random(10);  // 每筆交易編號需唯一
        $formParams = [
            'MerchantID'        => $merchantId,
            'MerchantTradeNo'   => $tradeNo,
            'MerchantTradeDate' => Carbon::now()->format('Y/m/d H:i:s'),
            'PaymentType'       => 'aio',
            'TotalAmount'       => $order->total_amount,
            'TradeDesc'         => 'Laravel_ECPay_Test',
            'ItemName'          => '課程訂單#' . $order->id,
            'ReturnURL'         => route('ecpay.callback'),   // 後端接收通知
            'ChoosePayment'     => 'ALL',
            'EncryptType'       => 1,  // AES 加密
            // 若要前端跳轉可用
            'ClientRedirectURL' => route('ecpay.redirect'),
        ];

        // 產生檢查碼
        $checkMacValue = $this->generateCheckMacValue($formParams, $hashKey, $hashIV);
        $formParams['CheckMacValue'] = $checkMacValue;

        // 產生自動送出表單
        $html = $this->genAutoSubmitForm($serviceUrl, $formParams);

        return response()->json([
            'message' => '請前往付款',
            'html'    => $html
        ]);
    }

    /**
     * 綠界付款完成後，後端回調 (Server POST)
     * 需回傳 "1|OK" 表示接收成功
     * POST /ecpay/callback
     */
    public function paymentCallback(Request $request)
    {
        // 接收所有 POST 參數
        $params = $request->all();

        // (可加上檢查碼驗證)
        // $checkMac = $this->generateCheckMacValue($params, env('ECPAY_HASH_KEY'), env('ECPAY_HASH_IV'));
        // if ($checkMac !== $params['CheckMacValue']) {
        //     return 'CheckMacValue Error';
        // }

        // 依 MerchantTradeNo 找到對應訂單 (示範: 可能要對應 tradeNo -> orderId)
        $merchantTradeNo = $params['MerchantTradeNo'] ?? null;

        // 這裡假設：你在 initiatePayment 時，未儲存 tradeNo 到 DB
        // 正常應該在 orders 表多個 merchant_trade_no 欄位對應
        // 這裡僅示範，實際需依你邏輯
        $order = Order::where('status', 'pending')->first();
        // or ->where('merchant_trade_no', $merchantTradeNo)->first();

        if (!$order) {
            return 'Order Not Found or Already Paid';
        }

        // 更新訂單狀態為已付款
        $order->update(['status' => 'paid']);

        // 回應綠界：1|OK
        return '1|OK';
    }

    /**
     * 前端跳轉 URL (付款完成後)
     * GET /ecpay/redirect
     */
    public function paymentRedirect(Request $request)
    {
        // 付款完成後，前端跳轉到此，可顯示 "付款成功" 或導回某頁面
        return redirect('/orders');
    }

    // ------------------- Helper Methods -------------------

    private function genAutoSubmitForm($actionUrl, $fields)
    {
        $html = '<form id="ecpay_form" method="post" action="' . $actionUrl . '">';
        foreach ($fields as $name => $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '">';
        }
        $html .= '</form>';
        $html .= '<script>document.getElementById("ecpay_form").submit();</script>';
        return $html;
    }

    private function generateCheckMacValue(array $params, string $hashKey, string $hashIV)
    {
        // 1. 依照 ECPay 規範，先將參數依 key 排序
        ksort($params);
        // 2. 組合字串
        $checkStr = 'HashKey=' . $hashKey;
        foreach ($params as $k => $v) {
            if ($k === 'CheckMacValue') continue;
            $checkStr .= '&' . $k . '=' . $v;
        }
        $checkStr .= '&HashIV=' . $hashIV;
        // 3. URL encode -> 轉小寫 -> md5
        $checkStr = urlencode($checkStr);
        $checkStr = strtolower($checkStr);
        $checkStr = md5($checkStr);
        // 4. 全部轉大寫
        return strtoupper($checkStr);
    }
}
