<?php
namespace App\Telegram\Controllers\Bot\Handlers;

class StartHandler
{
    public function index($system)
    {
        $handler = new MenuHandler();
        return $handler->index($system);
    }
    public function message($system) 
    {
        $text = $system->getMessageText();
    }
    public function callback($system) 
    {
        $callback_data = $system->getCallbackData();
        $system->send('answerCallbackQuery');
        return;
    }
}