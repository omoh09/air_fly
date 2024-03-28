<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoCRMService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class ContactController extends Controller
{
    public function forwardPayload($id)
    {
        $client_id = env('CLIENT_ID');
        $client_secret = env('SECRETE_ID');
        $refresh_token = env('REFRESH_TOKEN');
    
        $requestPhone = request("phone");

        $payload = [
            "token" => "7X2BWJ9l4q9wZc3l",
            "tenant_id" => "1017",
            "cr_number" => [$requestPhone],
            "did_id" => "51",
            "cr_action_type" => "CAMPAIGN",
            "cr_action_id" => "218",
            "cr_description" => "VIP ROUTING"
        ];

        $url = 'https://avlcc.avetiumconsult.com/HoduCC_api/v1.4/createCallerIdRouting';

        try {
            $zohoService = new ZohoCRMService($client_id, $client_secret, $refresh_token);
            
            $response = Http::post($url, $payload);
            // dd($response);

            if ($response->successful()) {
                $zohoService->createContact($id, "Sync");
                return response()->json(['data' => $response->json()], 200);
            } else {
                $zohoService->createContact($id, "Not Sync");
                return response()->json(['data' => $response->json()], 500);
            }
        } catch (\Exception $e) {
            $filePath = storage_path('app/log.txt');
            $content = [
                "message" => $e->getMessage(),
                "timestamp" => now()
            ];
            File::append($filePath, json_encode($content) . PHP_EOL);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function getLogs()
    {
        $filePath = storage_path('app/log.txt'); 

        if (File::exists($filePath)) {
            $content = File::get($filePath);

            return response()->json(['content' => $content]);
        } else {
            return response()->json(['error' => 'File not found'], 404);
        }
    }

}
