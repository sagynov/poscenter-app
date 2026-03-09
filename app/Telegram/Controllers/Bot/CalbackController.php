<?php
namespace App\Telegram\Controllers\Bot;

use App\Telegram\Controllers\Bot\Handlers\MenuHandler;

class CallbackController
{
    public function index($system)
    {
        // Проверим есть ли привязка к курьеру
        if(!$system->user){
          $system->send('answerCallbackQuery');
          return;
        }

        $callback_data = $system->getOriginalCallbackData();
        if($callback_data == 'close')
        {
            $system->send('answerCallbackQuery');
            $system->send('deleteMessage');
            return;
        }
        elseif(substr($callback_data, 0, 5) == 'menu_'){
            $handler = new MenuHandler();
            return $handler->callback($system);
        }
        
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
            return $handler->callback($system);
        }
    }
}