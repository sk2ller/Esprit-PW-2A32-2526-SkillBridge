<?php

class StripePaymentService
{
    private const CHECKOUT_SESSION_URL = 'https://api.stripe.com/v1/checkout/sessions';

    public function createCheckoutSession($service, $successUrl, $cancelUrl)
    {
        $secretKey = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';

        if ($secretKey === '') {
            return [
                'success' => false,
                'message' => 'Veuillez configurer la variable d environnement STRIPE_SECRET_KEY.'
            ];
        }

        $amount = $this->convertDtToStripeAmount($service['prix']);
        if ($amount < 50) {
            $amount = 50;
        }

        $params = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $service['id_service'],
            'metadata[id_service]' => (string) $service['id_service'],
            'metadata[titre]' => $service['titre'],
            'line_items[0][quantity]' => 1,
            'line_items[0][price_data][currency]' => strtolower(STRIPE_CURRENCY),
            'line_items[0][price_data][unit_amount]' => $amount,
            'line_items[0][price_data][product_data][name]' => $service['titre'],
            'line_items[0][price_data][product_data][description]' => 'Service freelance - prix original: ' . number_format((float) $service['prix'], 2) . ' DT'
        ];

        $response = $this->postToStripe($params, $secretKey);
        if ($response === null) {
            return [
                'success' => false,
                'message' => 'Impossible de creer la session Stripe.'
            ];
        }

        if (!empty($response['url'])) {
            return [
                'success' => true,
                'url' => $response['url']
            ];
        }

        return [
            'success' => false,
            'message' => $response['error']['message'] ?? 'Erreur Stripe inconnue.'
        ];
    }

    private function convertDtToStripeAmount($priceDt)
    {
        $rate = defined('STRIPE_DT_TO_PAYMENT_RATE') ? (float) STRIPE_DT_TO_PAYMENT_RATE : 0.30;
        $amount = (float) $priceDt * $rate;

        return (int) round($amount * 100);
    }

    private function postToStripe($params, $secretKey)
    {
        $payload = http_build_query($params);

        if (function_exists('curl_init')) {
            $curl = curl_init(self::CHECKOUT_SESSION_URL);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_USERPWD => $secretKey . ':',
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded'
                ],
                CURLOPT_TIMEOUT => 20
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            return $response ? json_decode($response, true) : null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => 20,
                'ignore_errors' => true,
                'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Basic " . base64_encode($secretKey . ':') . "\r\n",
                'content' => $payload
            ]
        ]);

        $response = @file_get_contents(self::CHECKOUT_SESSION_URL, false, $context);

        return $response ? json_decode($response, true) : null;
    }
}
