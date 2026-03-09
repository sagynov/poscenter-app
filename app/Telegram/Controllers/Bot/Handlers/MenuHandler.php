<?php
namespace App\Telegram\Controllers\Bot\Handlers;

class MenuHandler
{
    public function index($system, $edit = false)
    {
        // set directory
        $system->setDirectory('menu');
        
        $user = $system->user;
        if(!$user) {
          return;
        }
        
        $inline_keyboard = [];
        $inline_keyboard[] = [
            ['text' => ' '.__('telegram.menu.catalog'),  'web_app' => ['url' => secure_url('/bot/webapp')]]
        ];
        $data = [
            'text' => __('telegram.menu.welcome'),
            'reply_markup' => ['inline_keyboard' => $inline_keyboard]
        ];
        $method = 'sendMessage';
        if($edit) {
            $method = 'editMessageText';
        }
        $system->send($method, $data);
        return;
    }
    public function message($system) 
    {
        $text = $system->getMessageText();
        
    }
    public function callback($system) 
    {
        // set directory
        $system->setDirectory('menu');
        $callback_data = $system->getCallbackData();
        $system->send('answerCallbackQuery');
        return;
    }
}