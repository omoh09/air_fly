<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ZohoCRMService
{
    private $clientId;
    private $clientSecret;
    private $refreshToken;

    public function __construct($clientId, $clientSecret, $refreshToken)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;
    }

    public function createContact($recordId, $status)
    {
        $accessToken = $this->getAccessToken();

        $contactData = [
            "data" => [
                [
                    "Sync_Status" => $status
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            'Content-Type' => 'application/json'
        ])->put("https://www.zohoapis.com/crm/v2/contacts/{$recordId}", $contactData);

        if ($response->successful()) {
            return $response->json();
        } else {
            $filePath = storage_path('app/log.txt');
            $content = [
                "message" => "Failed to update contact. sync status",
                "timestamp" => now()
            ];
            File::append($filePath, json_encode($content) . PHP_EOL);
            return null;
        }
    }

    private function getAccessToken()
    {
        $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        } else {
            $filePath = storage_path('app/log.txt');
            $content = [
                "message" => "Authorization failed! check your credentials - client id, secrete id and refresh token",
                "timestamp" => now()
            ];
            File::append($filePath, json_encode($content) . PHP_EOL);
            return null;
        }
    }
}
