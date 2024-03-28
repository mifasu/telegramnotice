<?php namespace Dmdev\Telegramnotice\Components;

use Cms\Classes\ComponentBase;

/**
 * TelegramNotice Component
 *
 * @link https://docs.octobercms.com/3.x/extend/cms-components.html
 */
class TelegramNotice extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'TelegramNotice Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * @link https://docs.octobercms.com/3.x/element/inspector-types.html
     */
    public function defineProperties()
    {
        return [
            'sitename' => [
                'description' => 'sitename.ru',
                'title' => 'Site name',
                'type' => 'string',
            ],
            'token' => [
                'description' => 'Enter a 32 digit token',
                'title' => 'Token',
                'type' => 'string',
            ],
            'btnName' => [
                'description' => 'Enter name buttom',
                'title' => 'Buttom name',
                'type' => 'string',
            ],
        ];
    }

    public function onTelegramNoticeSendForm()
    {
        $phone_number = preg_replace('/[^0-9.]+/', '', input('ph'));
        $name = preg_replace("/&#?[a-z0-9]+;/i","", input('nm'));
        $tag = preg_replace("/&#?[a-z0-9]+;/i","", input('tag'));

        if (empty($this->property('sitename')) || empty($this->property('token'))) return false;        
    
        $text = "<b>".$this->page->title."</b>\n".$tag."\nИмя: <code>".$name."</code>\nТел.: <code>".$phone_number."</code>\n";
    
        if (!isset($name)) $name = 'Без имени';
        if (strlen($phone_number)>4)
        {
            $ch = curl_init();
            curl_setopt_array(
                $ch,
                array(
                    CURLOPT_URL => 'https://dmdev.ru/api/botPechkin/'.$this->property('sitename').':'.$this->property('token').'/sendMessage',
                    CURLOPT_POST => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_POSTFIELDS => array(
                        'text' =>  $text,
                        'parse_mode' => 'html'
                    ),
                )
            );
            curl_exec($ch);
            $this->page['result'] = true;
        }
    }
}