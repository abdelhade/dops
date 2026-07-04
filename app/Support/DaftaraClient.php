<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DaftaraClient
{
    protected ?string $subdomain;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->subdomain = AppSetting::get(AppSetting::KEY_DAFTARA_SUBDOMAIN);
        $this->apiKey = AppSetting::get(AppSetting::KEY_DAFTARA_API_KEY);
    }

    public function isConfigured(): bool
    {
        return filled($this->subdomain) && filled($this->apiKey);
    }

    /**
     * Fetch all clients from Daftara API
     *
     * @return array<int, array{id: int|string, name: string, email: ?string, phone: ?string, address: ?string, notes: ?string}>
     */
    public function getClients(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $allClients = [];
        $page = 1;
        $maxPages = 10; // Safeguard against excessive requests

        while ($page <= $maxPages) {
            $url = sprintf('https://%s.daftra.com/api2/clients', $this->subdomain);

            try {
                $response = Http::withHeaders([
                    'APIKEY' => $this->apiKey,
                ])->acceptJson()->get($url, [
                    'page' => $page,
                ]);

                if ($response->failed()) {
                    Log::error('Daftara API request failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    break;
                }

                $data = $response->json();
                $clients = $data['data'] ?? [];

                if (empty($clients)) {
                    break;
                }

                foreach ($clients as $clientData) {
                    // Normalize client data
                    $client = $clientData['Client'] ?? $clientData;

                    $id = $client['id'] ?? '';
                    $name = $client['business_name'] ?? '';
                    if ($name === '') {
                        $firstName = $client['first_name'] ?? '';
                        $lastName = $client['last_name'] ?? '';
                        $name = trim($firstName . ' ' . $lastName);
                    }

                    if ($name === '') {
                        continue;
                    }

                    $email = $client['email'] ?? null;
                    $phone = $client['phone1'] ?? $client['phone'] ?? $client['mobile'] ?? null;
                    
                    $address1 = $client['address1'] ?? '';
                    $address2 = $client['address2'] ?? '';
                    $city = $client['city'] ?? '';
                    $addressParts = array_filter([$address1, $address2, $city]);
                    $address = !empty($addressParts) ? implode(', ', $addressParts) : null;

                    $notes = $client['notes'] ?? null;

                    $allClients[] = [
                        'id' => $id,
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $address,
                        'notes' => $notes,
                    ];
                }

                if (count($clients) < 10) {
                    break;
                }

                $page++;
            } catch (\Exception $e) {
                Log::error('Daftara Client Exception', [
                    'message' => $e->getMessage(),
                ]);
                break;
            }
        }

        return $allClients;
    }
}
