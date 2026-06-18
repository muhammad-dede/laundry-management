<?php

namespace App\Services;

use App\Traits\FormatterTrait;
use Illuminate\Support\Facades\Http;


class FonnteService
{
    use FormatterTrait;

    public function send(string $phone, string $message)
    {
        $phone = $this->formatPhone($phone);

        return Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/send', [
            'target' => $phone,
            'message' => $message,
        ]);
    }
}
