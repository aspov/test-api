<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private float $lon, private float $lat, private User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //TODO условия
        $api = new \Yandex\Geo\Api();
        $api->setPoint($this->lon, $this->lat);
        $api->setLimit(1) // кол-во результатов
            ->setToken(env('YA_MAPS_TOKEN'))
            ->setLang(\Yandex\Geo\Api::LANG_RU)
            ->load();
        $response = $api->getResponse();
        $collection = $response->getList();
        if (! empty($collection)) {
            UserLocation::create([
                'user_id' => $this->user->id,
                'address' => $collection[0]->getAddress(),
                'lon' => $this->lon,
                'lat' => $this->lat,
            ]);
        }
    }
}
