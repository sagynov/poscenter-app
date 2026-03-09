<?php
namespace App\Telegram;
use Illuminate\Support\Facades\Http;
// use GuzzleHttp\Client;

class Bot
{
    public $token;
    public function __construct($token)
    {
        $this->token = $token;
    }
    public function dispatch($method, $data, $attachment = [])
    {
        if($attachment)
        {
            $f = fopen($attachment['path'], 'r');
            $response = Http::attach($attachment['name'], $f, $attachment['file_name'])->post('https://api.telegram.org/bot'.$this->token.'/'.$method, $data);
        }else{
            $response = Http::post('https://api.telegram.org/bot'.$this->token.'/'.$method, $data);
        }
        return $response->json();
    }
}