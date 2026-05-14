<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * خدمة Paymob — متخصصة في دفع المحافظ المحمولة المصرية
 *
 * تدعم: Vodafone Cash · Etisalat Cash · Orange Cash · We Pay
 *
 * تدفق الدفع:
 *   1. authenticate()         → الحصول على auth_token
 *   2. registerOrder()        → تسجيل أمر الدفع + الحصول على order_id
 *   3. getPaymentToken()      → الحصول على payment_key لهذه العملية
 *   4. initiateWalletPayment()→ إرسال طلب الدفع لرقم المحفظة
 *   5. handleWebhook()        → استقبال نتيجة الدفع من Paymob
 */
class PaymobService
{
    /** عنوان API الخاص بـ Paymob */
    private const BASE_URL = 'https://accept.paymob.com/api';

    /** معرّف Integration الخاص بالمحافظ المحمولة */
    private int    $integrationId;
    private string $apiKey;
    private string $hmacSecret;

    public function __construct()
    {
        $this->apiKey        = config('services.paymob.api_key');
        $this->integrationId = (int) config('services.paymob.wallet_integration_id');
        $this->hmacSecret    = config('services.paymob.hmac_secret');
    }

    // ═══════════════════════════════════════════════════
    //  STEP 1: المصادقة — الحصول على auth_token
    // ═══════════════════════════════════════════════════

    /**
     * يُرسل API Key ويستقبل auth_token صالح لمدة ساعة.
     *
     * @return string auth_token
     * @throws \Exception عند فشل المصادقة
     */
    public function authenticate(): string
    {
        $response = Http::timeout(45)->connectTimeout(15)->post(self::BASE_URL . '/auth/tokens', [
            'api_key' => $this->apiKey,
        ]);

        if (!$response->successful() || !isset($response['token'])) {
            Log::error('Paymob Auth Failed', ['response' => $response->json()]);
            throw new \Exception('فشل التوثيق مع Paymob. تحقق من API Key.');
        }

        return $response['token'];
    }

    // ═══════════════════════════════════════════════════
    //  STEP 2: تسجيل أمر الدفع
    // ═══════════════════════════════════════════════════

    /**
     * يُسجّل أمر دفع في نظام Paymob ويعيد order_id.
     * المبالغ ترسل بالقروش (Cents): 100 ج.م = 10000 قرش
     *
     * @param string $authToken    رمز المصادقة من STEP 1
     * @param float  $amountEGP   المبلغ بالجنيه المصري
     * @param int    $topupId     معرف طلب الشحن في قاعدة بياناتنا
     * @param array  $userInfo    بيانات المستخدم للفاتورة
     * @return array ['order_id' => ..., 'auth_token' => ...]
     */
    public function registerOrder(
        string $authToken,
        float  $amountEGP,
        int    $topupId,
        array  $userInfo
    ): array {
        $amountCents = (int) round($amountEGP * 100);

        $response = Http::timeout(45)->connectTimeout(15)->post(self::BASE_URL . '/ecommerce/orders', [
            'auth_token'         => $authToken,
            'delivery_needed'    => false,
            'amount_cents'       => $amountCents,
            'currency'           => 'EGP',
            'merchant_order_id'  => "DM-TOPUP-{$topupId}-" . time(),
            'items'              => [[
                'name'        => 'شحن محفظة DebtMate',
                'amount_cents' => $amountCents,
                'description' => "طلب شحن رقم #{$topupId}",
                'quantity'    => 1,
            ]],
            'shipping_data' => [
                'apartment'    => 'NA',
                'email'        => $userInfo['email'],
                'floor'        => 'NA',
                'first_name'   => $userInfo['first_name'],
                'last_name'    => $userInfo['last_name'] ?? 'NA',
                'street'       => $userInfo['address'] ?? 'Egypt',
                'building'     => 'NA',
                'phone_number' => $userInfo['phone'] ?? '+201000000000',
                'postal_code'  => '00000',
                'city'         => 'Cairo',
                'country'      => 'EGY',
                'state'        => 'Cairo',
            ],
        ]);

        if (!$response->successful() || !isset($response['id'])) {
            Log::error('Paymob Order Registration Failed', [
                'topup_id' => $topupId,
                'response' => $response->json(),
            ]);
            throw new \Exception('فشل تسجيل أمر الدفع في Paymob.');
        }

        return [
            'order_id'   => $response['id'],
            'auth_token' => $authToken,
        ];
    }

    // ═══════════════════════════════════════════════════
    //  STEP 3: الحصول على Payment Key
    // ═══════════════════════════════════════════════════

    /**
     * يُولِّد payment_key مرتبطاً بأمر الدفع المحدد.
     * هذا المفتاح صالح لمعاملة واحدة فقط.
     *
     * @param string $authToken   من STEP 1
     * @param int    $orderId     من STEP 2
     * @param float  $amountEGP  المبلغ (يجب أن يطابق STEP 2)
     * @param array  $billingData بيانات الفاتورة
     * @return string payment_key
     */
    public function getPaymentKey(
        string $authToken,
        int    $orderId,
        float  $amountEGP,
        array  $billingData
    ): string {
        $amountCents = (int) round($amountEGP * 100);

        $response = Http::timeout(45)->connectTimeout(15)->post(self::BASE_URL . '/acceptance/payment_keys', [
            'auth_token'     => $authToken,
            'amount_cents'   => $amountCents,
            'expiration'     => 3600,  // صالح لساعة واحدة
            'order_id'       => $orderId,
            'currency'       => 'EGP',
            'integration_id' => $this->integrationId,
            'lock_order_when_paid' => true,
            'billing_data'   => [
                'apartment'    => 'NA',
                'email'        => $billingData['email'],
                'floor'        => 'NA',
                'first_name'   => $billingData['first_name'],
                'last_name'    => $billingData['last_name'] ?? 'NA',
                'street'       => $billingData['address'] ?? 'Egypt',
                'building'     => 'NA',
                'phone_number' => $billingData['phone'] ?? '+201000000000',
                'postal_code'  => '00000',
                'city'         => 'Cairo',
                'country'      => 'EGY',
                'state'        => 'Cairo',
            ],
        ]);

        if (!$response->successful() || !isset($response['token'])) {
            Log::error('Paymob Payment Key Failed', ['response' => $response->json()]);
            throw new \Exception('فشل الحصول على مفتاح الدفع من Paymob.');
        }

        return $response['token'];
    }

    // ═══════════════════════════════════════════════════
    //  STEP 4: بدء الدفع عبر المحفظة المحمولة
    // ═══════════════════════════════════════════════════

    /**
     * يُرسل طلب الدفع لرقم هاتف Vodafone Cash.
     * Paymob يُرسل SMS للمستخدم لتأكيد الدفع.
     *
     * @param string $paymentKey  من STEP 3
     * @param string $phoneNumber رقم هاتف Vodafone Cash (01XXXXXXXXX)
     * @return array استجابة Paymob تحتوي على redirect_url أو رسالة
     */
    public function initiateWalletPayment(string $paymentKey, string $phoneNumber): array
    {
        // تأكد أن الرقم يبدأ بـ +20
        $phone = $this->normalizeEgyptianPhone($phoneNumber);

        $response = Http::timeout(45)->connectTimeout(15)->post(self::BASE_URL . '/acceptance/payments/pay', [
            'source' => [
                'identifier'   => $phone,
                'subtype'      => 'WALLET',  // نوع المصدر: محفظة محمولة
            ],
            'payment_token' => $paymentKey,
        ]);

        if (!$response->successful()) {
            Log::error('Paymob Wallet Payment Init Failed', [
                'phone'    => $phone,
                'response' => $response->json(),
            ]);
            throw new \Exception('فشل إرسال طلب الدفع. تحقق من رقم المحفظة.');
        }

        return $response->json();
    }

    // ═══════════════════════════════════════════════════
    //  STEP 5: التحقق من Webhook (HMAC Validation)
    // ═══════════════════════════════════════════════════

    /**
     * يتحقق من صحة HMAC الوارد من Paymob لمنع التزوير.
     *
     * Paymob يُرسل المعاملات مرتّبةً أبجدياً في سلسلة نصية
     * ويُشفّرها بـ HMAC-SHA512 باستخدام hmac_secret.
     *
     * @param array $data   البيانات الكاملة من الـ Webhook
     * @return bool
     */
    public function validateHmac(array $data): bool
    {
        $hmacReceived = $data['hmac'] ?? '';

        // الحقول المستخدمة في حساب HMAC (ترتيب أبجدي — محدد من Paymob)
        $hmacFields = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];

        // استخراج قيم الحقول من obj.transaction_response
        $transaction = $data['obj'] ?? [];
        $concatenated = '';
        foreach ($hmacFields as $field) {
            $value = match ($field) {
                'order'            => $transaction['order']['id'] ?? '',
                'source_data_pan'  => $transaction['source_data']['pan'] ?? '',
                'source_data_sub_type' => $transaction['source_data']['sub_type'] ?? '',
                'source_data_type' => $transaction['source_data']['type'] ?? '',
                default            => $transaction[$field] ?? '',
            };
            $concatenated .= (string) $value;
        }

        $calculatedHmac = hash_hmac('sha512', $concatenated, $this->hmacSecret);

        return hash_equals($calculatedHmac, $hmacReceived);
    }

    // ═══════════════════════════════════════════════════
    //  تدفق الدفع الكامل (Convenience Method)
    // ═══════════════════════════════════════════════════

    /**
     * يُنفّذ STEPS 1-4 دفعةً واحدة لتبسيط الاستخدام في Controller.
     *
     * @param float  $amountEGP   المبلغ بالجنيه
     * @param int    $topupId     معرف طلب الشحن
     * @param array  $userInfo    بيانات المستخدم
     * @param string $walletPhone رقم Vodafone Cash
     * @return array ['redirect_url' => ..., 'order_id' => ..., 'payment_key' => ...]
     */
    public function initiateVodafonePayment(
        float  $amountEGP,
        int    $topupId,
        array  $userInfo,
        string $walletPhone
    ): array {
        // STEP 1
        $authToken = $this->authenticate();

        // STEP 2
        $orderData = $this->registerOrder($authToken, $amountEGP, $topupId, $userInfo);

        // STEP 3
        $paymentKey = $this->getPaymentKey(
            $authToken,
            $orderData['order_id'],
            $amountEGP,
            $userInfo
        );

        // STEP 4
        $paymentResponse = $this->initiateWalletPayment($paymentKey, $walletPhone);

        return [
            'order_id'     => $orderData['order_id'],
            'payment_key'  => $paymentKey,
            'redirect_url' => $paymentResponse['redirect_url'] ?? null,
            'pending'      => $paymentResponse['pending'] ?? true,
            'raw_response' => $paymentResponse,
        ];
    }

    // ═══════════════════════════════════════════════════
    //  Helpers
    // ═══════════════════════════════════════════════════

    /**
     * يُحوّل رقم الهاتف المصري إلى الصيغة الدولية.
     * 01XXXXXXXXX → +201XXXXXXXXX
     */
    private function normalizeEgyptianPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone); // إزالة كل غير الأرقام
        if (str_starts_with($phone, '0')) {
            $phone = '2' . $phone;  // 01... → 201...
        }
        return '+' . $phone;
    }
}
