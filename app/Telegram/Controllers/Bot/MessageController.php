<?php
namespace App\Telegram\Controllers\Bot;

use App\Telegram\Controllers\Bot\Handlers\MenuHandler;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Telegram\User;

class MessageController
{
    public function index($system) 
    {
        $text = $system->getMessageText();
        preg_match('/^(?<command>\/\w+)/', $text, $matches);
        if(isset($matches['command'])){
            $text = $matches['command'];
        }
        // If command
        $pages = ['/start', '/menu'];
        if(in_array($text, $pages)){
            $system->clearData();
            $command = substr_replace($text, '', 0, 1);
            $class = '\App\Telegram\Controllers\Bot\Handlers\\'.ucfirst($command).'Handler';
            if (class_exists($class)) {
                $handler = new $class();
                return $handler->index($system);
            }
        }
        // If NOT command
        $directory = $system->getDirectory();
        $lines = explode('_', $directory);
        $class = '\App\Telegram\Controllers\Bot\Handlers';
        for ($i = 0; $i < count($lines); $i++) {
              $class .= '\\'.ucfirst($lines[$i]);
              if($i == (count($lines) - 1)) {
                  $class .= 'Handler';
              }
        }
        if (class_exists($class)) {
            $handler = new $class();
            return $handler->message($system);
        }
    }
}