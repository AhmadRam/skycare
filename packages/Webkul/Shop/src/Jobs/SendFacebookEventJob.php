<?php

namespace Webkul\Shop\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFacebookEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventName;
    protected $user;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param string $eventName
     * @param mixed $data
     */
    public function __construct(string $eventName, $user, $data)
    {
        $this->eventName = $eventName;
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Prepare Facebook API details
            $accessToken = config('app.facebook_access_token', '');
            $pixelId = config('app.facebook_pixel_id', '');
            $client = new Client(['base_uri' => 'https://graph.facebook.com/v15.0/']);

            // Prepare user data
            $userData = $this->getUserData();

            // Prepare custom data based on the event
            $customData = $this->prepareCustomData();
            // Send the event
            $response = $client->post("{$pixelId}/events", [
                'query' => ['access_token' => $accessToken],
                'json' => [
                    'data' => [
                        [
                            'event_name'    => $this->eventName,
                            'event_time'    => time(),
                            'user_data'     => $userData,
                            'custom_data'   => $customData,
                            'action_source' => 'website',
                            'event_source_url' => url()->current(),  // Current URL of the event
                        ],
                    ],
                ],
            ]);

            // Log success
            Log::info("Facebook Event '{$this->eventName}' sent successfully.", [
                'response' => json_decode($response->getBody(), true),
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error("Error sending Facebook Event '{$this->eventName}': " . $e->getMessage());
            Log::error('Payload:', [
                'event_name' => $this->eventName,
                'data' => $this->data,
            ]);
        }
    }

    /**
     * Generate user data for the Facebook Conversions API.
     *
     * @return array
     */
    private function getUserData(): array
    {
        return [
            'em' => $this->user->email ?? null,
            'ph' => $this->user->phone ?? null,
            // 'ip' => request()->ip(),
            'fbc' => request()->cookie('_fbc'),
            'fbp' => request()->cookie('_fbp'),
            'external_id' => $this->user->id ?? null,  // Add external ID if available
            'client_user_agent' => request()->header('User-Agent'),  // Client User Agent - Do Not Hash
            'client_ip_address' => request()->ip(),  // Client IP Address - Do Not Hash
        ];
    }

    /**
     * Prepare custom data for the event based on the event name and provided data.
     *
     * @return array
     */
    private function prepareCustomData(): array
    {
        switch ($this->eventName) {
            case 'PageView':
                return [
                    'event_time' => time(),
                    'event_name' => $this->eventName,
                    'event_source_url' => url()->current(),
                    'action_source' => 'website',
                ];

            case 'ViewContent':
                return [
                    'currency' => core()->getCurrentCurrency()->code,
                    'value' => $this->data->getTypeInstance()->getFinalPrice(),
                    'content_name' => $this->data->name,
                    'content_ids' => [$this->data->id],
                    'external_id' => $this->data->id,
                    'client_ip_address' => request()->ip(),
                    'fbp' => request()->cookie('_fbp'),
                    'fbc' => request()->cookie('_fbc'),
                    'email' => $this->user->email ?? null,
                    'phone' => $this->user->phone ?? null,
                ];

            case 'AddToCart':
                return [
                    'currency' => core()->getCurrentCurrency()->code,
                    'value' => $this->data->getTypeInstance()->getFinalPrice(),
                    'content_name' => $this->data->name,
                    'content_ids' => [$this->data->id],
                    'event_id' => uniqid(),  // Event ID for uniqueness
                    'external_id' => $this->data->id,
                    'client_ip_address' => request()->ip(),
                    'email' => $this->user->email ?? null,
                    'phone' => $this->user->phone ?? null,
                ];

            case 'InitiateCheckout':
                return [
                    'currency' => core()->getCurrentCurrency()->code,
                    'value' => $this->data->total,
                    'num_items' => $this->data->items->count(),
                    'event_id' => uniqid(),
                    'email' => $this->user->email ?? null,
                    'phone' => $this->user->phone ?? null,
                    'client_ip_address' => request()->ip(),
                    'fbp' => request()->cookie('_fbp'),
                    'fbc' => request()->cookie('_fbc'),
                ];

            case 'Purchase':
                $address = $this->data->shipping_address;
                return [
                    'currency' => core()->getCurrentCurrency()->code,
                    'value' => $this->data->grand_total,
                    'contents' => $this->data->items->map(function ($item) {
                        return [
                            'id' => $item->product_id,
                            'quantity' => $item->qty_ordered,
                            'item_price' => $item->price,
                        ];
                    })->toArray(),
                    'first_name' => $address->first_name ?? null,
                    'last_name' => $address->last_name ?? null,
                    'email' => $address->email ?? null,
                    'phone' => $address->phone ?? null,
                    'city' => $address->city ?? null,
                    'state' => $address->state ?? null,
                    'zip_code' => $address->zip_code ?? null,
                    'country' => $address->country ?? null,
                    'external_id' => $this->data->id,
                    'client_ip_address' => request()->ip(),
                    'client_user_agent' => request()->header('User-Agent'),
                    'fbp' => request()->cookie('_fbp'),
                    'fbc' => request()->cookie('_fbc'),
                ];

            case 'Search':
                return [
                    'currency' => core()->getCurrentCurrency()->code,
                    'value' => $this->data->getTypeInstance()->getFinalPrice(),
                    'content_name' => $this->data->name,
                    'content_ids' => [$this->data->id],
                    'event_time' => time(),
                    'event_source_url' => url()->current(),
                    'action_source' => 'website',
                    'external_id' => $this->data->id,
                    'client_ip_address' => request()->ip(),
                    'fbp' => request()->cookie('_fbp'),
                    'fbc' => request()->cookie('_fbc'),
                ];

            default:
                return [];
        }
    }
}
