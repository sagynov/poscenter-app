<?php
namespace App\Telegram\Controllers;

use Illuminate\Http\Request;
use App\Telegram\System;
use Inertia\Inertia;
use Inertia\Response;

class BotController
{
    public function index(Request $request)
    {        
        $system = new System();
        $system->setRequest($request);
        $user = $system->user;
        if(isset($request->message)){
            $controller = new Bot\MessageController();
            return $controller->index($system);
        }
        elseif (isset($request->callback_query)) {
            $controller = new Bot\CallbackController();
            return $controller->index($system);
        }
        return 'success';
    }
    public function webapp(Request $request): Response
    {
        return Inertia::render('Bot/WebApp');
    }
    public function cart(Request $request): Response
    {
        return Inertia::render('Bot/Cart');
    }
    public function checkout(Request $request): Response
    {
        return Inertia::render('Bot/Checkout');
    }
    public function success(Request $request): Response
    {
        return Inertia::render('Bot/Success');
    }
}