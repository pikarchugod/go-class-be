<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Order;

class PaymentController extends Controller
{
    /**
     * 發起付款：回傳自動送出表單的 HTML
     * POST /api/payment/initiate
     * Body: { "order_id": <int> }
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::findOrFail($request->order_id);

        // 若訂單已付款，直接回應
        if ($order->status === 'paid') {
            return response()->json(['message' => '此訂單已付款'], 400);
        }

        // 1. ECPay 測試環境參數 (可放在 .env)
        $merchantId = env('ECPAY_MERCHANT_ID', '3002607');
        $hashKey    = env('ECPAY_HASH_KEY', 'pwFHCqoQZGmho4w6');
        $hashIV     = env('ECPAY_HASH_IV', 'EkRm7iFT261dpevs');
        $serviceUrl = env('ECPAY_SERVICE_URL', 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5');

        // 2. 從 orders 表取得 merchant_trade_no，若無則重新生成
        $tradeNo = $order->merchant_trade_no;
        if (!$tradeNo) {
            $tradeNo = 'ORDER' . Str::random(10);
            $order->update(['merchant_trade_no' => $tradeNo]);
        }

        // 3. 組合付款表單參數
        //    注意：避免特殊字元 / 符號，如 #、中文，以降低 CheckMacValue 出錯機率
        $formParams = [
            'MerchantID'        => $merchantId,
            'MerchantTradeNo'   => $tradeNo,
            'MerchantTradeDate' => Carbon::now()->format('Y/m/d H:i:s'), // YYYY/MM/DD HH:mm:ss
            'PaymentType'       => 'aio',
            'TotalAmount'       => (int) $order->total_amount, // 確保整數
            'TradeDesc'         => 'TestOrder',   // 建議用簡單英數字
            'ItemName'          => 'Course' . $order->id, // 避免 #、中文
            'ReturnURL'         => route('ecpay.callback'),   // 後端回調
            'ChoosePayment'     => 'ALL',
            'EncryptType'       => 1, // AES
            // 若要前端跳轉可用
            'ClientRedirectURL' => route('ecpay.redirect'),

            // 其他可選參數 (ex: 'NeedExtraPaidInfo' => 'Y')
        ];

        // 4. 產生檢查碼 (SHA256 + 特定符號替換)
        $checkMacValue = $this->generateCheckMacValue($formParams, $hashKey, $hashIV);
        $formParams['CheckMacValue'] = $checkMacValue;

        // 5. 產生自動送出表單
        $html = $this->genAutoSubmitForm($serviceUrl, $formParams);

        // 回傳給前端 (JSON)，由前端將 html 插入頁面並自動提交
        return response()->json([
            'message' => '請前往付款',
            'html'    => $html
        ]);
    }

    /**
     * 綠界付款完成後，後端回調 (Server POST)
     * 必須回傳 "1|OK" 表示成功接收
     * POST /ecpay/callback
     */
    public function paymentCallback(Request $request)
    {
        $params = $request->all();

        // (可加上檢查碼驗證)
        // $checkMac = $this->generateCheckMacValue($params, env('ECPAY_HASH_KEY'), env('ECPAY_HASH_IV'));
        // if ($checkMac !== $params['CheckMacValue']) {
        //     return 'CheckMacValue Error';
        // }

        // 依 MerchantTradeNo 找到對應訂單
        $merchantTradeNo = $params['MerchantTradeNo'] ?? null;
        if (!$merchantTradeNo) {
            return 'MerchantTradeNo Missing';
        }

        $order = Order::where('merchant_trade_no', $merchantTradeNo)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return 'Order Not Found or Already Paid';
        }

        // 更新訂單狀態為 paid
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

    /**
     * 自動送出表單
     */
    private function genAutoSubmitForm($actionUrl, $fields)
    {
        $html = '<form id="ecpay_form" method="post" action="' . e($actionUrl) . '">';
        foreach ($fields as $name => $value) {
            $html .= '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
        }
        $html .= '</form>';
        $html .= '<script>document.getElementById("ecpay_form").submit();</script>';
        return $html;
    }

    /**
     * 產生 CheckMacValue (SHA256 + 特定符號替換)
     */
    private function generateCheckMacValue(array $params, string $hashKey, string $hashIV)
    {
        // 1. 依照 ECPay 規範，先將參數依 key 排序
        ksort($params);

        // 2. 組合字串
        $raw = 'HashKey=' . $hashKey;
        foreach ($params as $k => $v) {
            if ($k === 'CheckMacValue') continue;
            $raw .= '&' . $k . '=' . $v;
        }
        $raw .= '&HashIV=' . $hashIV;

        // 3. URL encode
        $raw = urlencode($raw);

        // 4. 特定符號替換 (官方文件要求)
        $raw = str_replace('%2d', '-', $raw);
        $raw = str_replace('%2f', '/', $raw);
        $raw = str_replace('%3a', ':', $raw);
        $raw = str_replace('%21', '!', $raw);
        $raw = str_replace('%28', '(', $raw);
        $raw = str_replace('%29', ')', $raw);
        $raw = str_replace('%2a', '*', $raw);
        $raw = str_replace('%2d', '-', $raw);
        $raw = str_replace('%5f', '_', $raw);
        $raw = str_replace('%20', ' ', $raw);
        $raw = str_replace('%2e', '.', $raw);
        $raw = str_replace('%2c', ',', $raw);
        $raw = str_replace('%2b', '+', $raw);
        $raw = str_replace('%25', '%', $raw);

        // 5. 轉小寫
        $raw = strtolower($raw);

        // 6. 使用 SHA256 雜湊
        $raw = hash('sha256', $raw);

        // 7. 轉大寫
        return strtoupper($raw);
    }
}
