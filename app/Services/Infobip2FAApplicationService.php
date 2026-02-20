<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;

/**
 * Création de l'application 2FA Infobip
 * À exécuter UNE SEULE FOIS
 */
class Infobip2FAApplicationService
{
    protected $apiUrl = 'https://api.infobip.com/2fa/2/applications';
    protected $apiKey = 'dbf2dfd0703e9cb8ef176b7bb658dc76-199d469c-4352-4f58-aa5d-ce11efb64bef';

    public function createApplication()
    {
        $client = new Client();

        $data = [
            'name' => '2fa test application',
            'enabled' => true,
            'configuration' => [
                'pinAttempts' => 10,
                'allowMultiplePinVerifications' => true,
                'pinTimeToLive' => '15m',
                'verifyPinLimit' => '1/3s',
                'sendPinPerApplicationLimit' => '100/1d',
                'sendPinPerPhoneNumberLimit' => '10/1d',
            ],
        ];

        try {
            $response = $client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'App ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json' => $data,
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }

            throw new Exception('Unexpected HTTP status: ' . $response->getStatusCode());
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }
}
