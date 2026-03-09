<?php
namespace App\Telegram;
use Illuminate\Support\Facades\App;

class SystemParent {
    public $request;
    public $bot;
    public $user;
    public $user_id;
    public $prefix;
    public $close_button = false;
    
    public function __construct($token, $prefix)
    {
        $this->bot = new Bot($token);
        $this->prefix = $prefix;
    }
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
    public function setUser($user)
    {
        if($user?->locale){
          App::setLocale($user->locale);
        }
        $this->user = $user;
    }
    public function setRequest($request)
    {
        $this->request = $request;
        if(isset($request->message)){
            $this->user_id = $request->input('message.from.id');
        }
        elseif (isset($request->callback_query)) {
            $this->user_id = $request->input('callback_query.from.id');
        }
        elseif (isset($request->inline_query)) {
            $this->user_id = $request->input('inline_query.from.id');
        }
        elseif (isset($request->poll_answer)) {
            $this->user_id = $request->input('poll_answer.user.id');
        }
    }
    public function setDirectory($directory)
    {
        $system = cache($this->prefix.'system_'.$this->user_id) ?? [];
        $system['directory'] = $directory;
        cache([$this->prefix.'system_'.$this->user_id => $system]);
        
        $lines = explode('_', $directory);
        $callback_prefix = '';
        for ($i = 0; $i < count($lines); $i++) {
            if($i == 0){
                $callback_prefix .= $lines[$i];
            }else{
                $callback_prefix .= ucfirst($lines[$i]);
            }
            if($i == (count($lines) - 1)) {
                $callback_prefix .= '_';
            }
        }
        $this->setCallbackPrefix($callback_prefix);
        
    }
    public function setCallbackPrefix($callback_prefix)
    {
        $system = cache($this->prefix.'system_'.$this->user_id) ?? [];
        $system['callback_prefix'] = $callback_prefix;
        cache([$this->prefix.'system_'.$this->user_id => $system]);
    }
    public function getCallbackPrefix()
    {
        $system = cache($this->prefix.'system_'. $this->user_id) ?? [];
        if(isset($system['callback_prefix'])){
            return $system['callback_prefix'];
        }
        return '';
    }
    public function getDirectory()
    {
        $system = cache($this->prefix.'system_'.$this->user_id) ?? [];
        if(isset($system['directory'])){
            return $system['directory'];
        }
        return null;
    }
    public function setData($directory, $data)
    {
        $system = cache($this->prefix.'system_'. $this->user_id) ?? [];
        if(isset($system['records'][$directory])){
            $system['records'][$directory] = array_merge($system['records'][$directory], $data);
        }else{
            $system['records'][$directory] = $data;
        }
        cache([$this->prefix.'system_'.$this->user_id => $system]);
    }
    public function getData($directory)
    {
        $user_id = $this->user_id;
        $system = cache($this->prefix.'system_'.$this->user_id) ?? [];
        if(isset($system['records'][$directory])){
            return $system['records'][$directory];
        }
        return [];
    }
    public function clearData()
    {
        $system = cache($this->prefix.'system_'. $this->user_id) ?? [];
        $system['records'] = [];
        cache([$this->prefix.'system_'.$this->user_id => $system]);
    }
    public function getMessagePhoto()
    {
        if(isset($this->request->message['photo']))
        {
            $photos = $this->request->message['photo'];
            $file_id = 0;
            foreach ($photos as $photo) {
                $file_id = $photo['file_id'];
            }
            $getFile = $this->getFile($file_id);
            return $getFile;
        }else{
            return null;
        }
    }
    public function getMessageDocument()
    {
        if(isset($this->request->message['document']))
        {
            $document = $this->request->message['document'];
            $file_id = $document['file_id'];
            $getFile = $this->getFile($file_id);
            return $getFile;
        }else{
            return null;
        }
    }
    public function getMessageText()
    {
        $request = $this->request;
        if(isset($request->message)){
            return $request->input('message.text');
        }else{
            return null;
        }
    }
    public function getInlineQuery()
    {
        $request = $this->request;
        if(isset($request->inline_query)){
            return $request->input('inline_query.query');
        }else{
            return '';
        }
    }
    public function getPhoneNumber()
    {
        $request = $this->request;
        if(isset($request->message['contact'])){
            return $request->input('message.contact.phone_number');
        }else{
            return '';
        }
    }
    public function getMyPhoneNumber()
    {
        $request = $this->request;
        if(!isset($request->message['contact'])){
            return;
        }
        if(!isset($request->message['contact']['user_id'])){
            return;
        }
        if($request->message['contact']['user_id'] == $this->user_id){
            return $request->input('message.contact.phone_number');
        }
    }
    public function getOriginalCallbackData()
    {
        $request = $this->request;
        if(isset($request->callback_query)) {
            $callback_data = $request->input('callback_query.data');
            return $callback_data;
        }
        return '';
    }
    public function getCallbackData()
    {
        $request = $this->request;
        if(isset($request->callback_query)) {
            $callback_prefix = $this->getCallbackPrefix();
            $callback_data = $request->input('callback_query.data');
            if(substr($callback_data, 0, strlen($callback_prefix)) == $callback_prefix){
                $callback_data = substr_replace($callback_data, '', 0, strlen($callback_prefix));
            }
            return $callback_data;
        }
        return '';
    }
    public function getFile($file_id)
    {
        $getFile = $this->send('getFile', ['file_id' => $file_id]);
        if(isset($getFile['result']))
        {
            return 'https://api.telegram.org/file/bot'.$this->bot->token.'/'.$getFile['result']['file_path'];
        }
        return null;
    }
    public function send($method, $data = [], $attachment = [])
    {
        $request = $this->request;
        if(isset($request->message)){
            $message_id = $request->input('message.message_id');
        }
        elseif (isset($request->callback_query)) {
            $query_id = $request->input('callback_query.id');
            $message_id = $request->input('callback_query.message.message_id');
        }
        elseif (isset($request->inline_query)) {
            $inline_query_id = $request->input('inline_query.id');
        }
        
        
        $allow_methods = ['sendMessage', 'sendAudio', 'editMessageText', 'sendPhoto', 'editMessageCaption', 'editMessageMedia', 'editMessageReplyMarkup'];
        if(in_array($method, $allow_methods))
        {
            $callback_prefix = $this->getCallbackPrefix();
            if(isset($data['reply_markup']['inline_keyboard'])){
                $inline_keyboard = $data['reply_markup']['inline_keyboard'];
                foreach ($inline_keyboard as $row_key => $row) {
                    foreach ($row as $col_key => $col) {
                        if(isset($col['callback_data'])){
                            $callback_data = $col['callback_data'];
                            $enable_callback_prefix = true;
                            if(isset($col['callback_prefix'])){
                                $enable_callback_prefix = $col['callback_prefix'];
                                unset($inline_keyboard[$row_key][$col_key]['callback_prefix']);
                            }
                            if($enable_callback_prefix){
                                $callback_data = $callback_prefix.$callback_data;
                            }
                            $inline_keyboard[$row_key][$col_key]['callback_data'] = $callback_data;
                        }
                    }
                }
                $data['reply_markup']['inline_keyboard'] = $inline_keyboard;
            }
            
        }
        $user_id = $this->user_id;
        switch ($method) {
            case 'sendMessage':
                $data['chat_id'] = $user_id;
                $data['parse_mode'] = 'html';
                $response = $this->bot->dispatch($method, $data);
                break;
            case 'sendAudio':
                $data['chat_id'] = $user_id;
                $data['parse_mode'] = 'html';
                $response = $this->bot->dispatch($method, $data);
                break;
            case 'deleteMessage':
                $data['chat_id'] = $user_id;
                $data['message_id'] = $message_id;
                $response = $this->bot->dispatch($method, $data);
                break;
            case 'editMessageText':
                $data['chat_id'] = $user_id;
                $data['message_id'] = $message_id;
                $data['parse_mode'] = 'html';
                $response = $this->bot->dispatch($method, $data);
                
                break;
            case 'answerCallbackQuery':
                $data['callback_query_id'] = $query_id;
                $response = $this->bot->dispatch($method, $data);
                break;
            case 'answerInlineQuery':
                $data['inline_query_id'] = $inline_query_id;
                $data['cache_time'] = 10;
                $response = $this->bot->dispatch($method, $data);
                break;
            case 'sendPhoto':
                $data['chat_id'] = $user_id;
                $data['parse_mode'] = 'html';
                $response = $this->bot->dispatch($method, $data);
                
                break;
            case 'editMessageCaption':
                $data['chat_id'] = $user_id;
                $data['message_id'] = $message_id;
                $data['parse_mode'] = 'html';
                $response = $this->bot->dispatch($method, $data);
                break;
            case 'editMessageReplyMarkup':
                $data['chat_id'] = $user_id;
                $data['message_id'] = $message_id;
                $response = $this->bot->dispatch($method, $data);
                break;
            case 'editMessageMedia':
                $data['chat_id'] = $user_id;
                $data['message_id'] = $message_id;
                $data['media']['parse_mode'] = 'html';
                $response = $this->bot->dispatch($method, $data);
                
                break;
            case 'sendContact':
                $data['chat_id'] = $user_id;
                $response = $this->bot->dispatch($method, $data);
                break;
            case 'sendChatAction':
                $data['chat_id'] = $user_id;
                $response = $this->bot->dispatch($method, $data);
                break;
            case 'sendDocument':
                $data['chat_id'] = $user_id;
                $data['parse_mode'] = 'html';
                $response = $this->bot->dispatch($method, $data, $attachment);
                break;
            default:
                $response = $this->bot->dispatch($method, $data);
                break;
        }
        return $response;
    }
}