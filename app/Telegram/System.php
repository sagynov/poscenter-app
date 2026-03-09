<?php
namespace App\Telegram;

use App\Models\TelegramUser;
use App\Models\User;

class System extends SystemParent
{
    public $prefix = 'telegram_';
    public function __construct()
    {
        $bot_token = config('services.telegram.bot_token');
        parent::__construct($bot_token, $this->prefix);
    }
    public function setRequest($request)
    {
        parent::setRequest($request);
        $user = TelegramUser::where('telegram_id', $this->user_id)->first();
        if($user){
            parent::setUser($user);
        }else{
            $this->register();
        }
    }
    public function register()
    {
        $user_id = $this->user_id;
        $request = $this->request;
        if(isset($request->message)){
            $text = $request->input('message.text');
            $user = new TelegramUser();
            $data = [
                'telegram_id' =>  $user_id,
                'username' => $request->input('message.from.username') ?? '',
                'first_name' => $request->input('message.from.first_name') ?? '',
                'last_name' => $request->input('message.from.last_name') ?? '',
                'locale' => config('app.locale')
            ];
            $user->fill($data);
            $user->save();
            parent::setUser($user);
        }
    }
}