<?php namespace Dmdev\Telegramnotice\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Dmdev\Telegramnotice\Models\Settings;


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
        // Accept multiple possible input names to be tolerant with theme templates
        $rawPhone = input('ph') ?? input('contact') ?? input('phone') ?? input('phone_number');
        $phone_number = preg_replace('/[^0-9.+]/', '', $rawPhone ?? '');

        $rawName = input('nm') ?? input('name') ?? input('fullname');
        $name = preg_replace("/&#?[a-z0-9]+;/i","", $rawName ?? '');

        $tag = preg_replace("/&#?[a-z0-9]+;/i","", input('tag') ?? '');
        $from = preg_replace("/&#?[a-z0-9]+;/i","", input('from') ?? '');

        $arr = null;
        $inputArr = input('arr');
        if (is_array($inputArr)) {
            foreach ($inputArr as $key => $arrValue)
            {
                if ($key === array_key_first($inputArr)) $arr = preg_replace("/&#?[a-z0-9]+;/i","", $arrValue);
                else $arr = $arr."\n".preg_replace("/&#?[a-z0-9]+;/i","", $arrValue);
            }
        }
        
        $text = "Сайт: ".preg_replace('/^https?:\/\//', '', URL::to('/'));        
        if (!empty($from)) $text .= "\n<b>".$from."</b>";         
        elseif (!empty($this->page->title)) $text .= "\n".$this->page->title;           
        if (!empty($name)) $text .= "\nИмя: <code>".$name."</code>";
        if (!empty($phone_number)) $text .= "\nТел.: <code>".$phone_number."</code>";
        if (!empty($tag)) $text .= "\n".$tag;
        if (!empty($arr)) $text .= "\n".$arr; 

        if (strlen($phone_number)>4)
        {
            if ($this->sendTelegram($text)) $this->page['result'] = true;
        }
    }

    function sendTelegram($text)
    {
    // Read settings from backend settings model
    $settings = Settings::instance();
    $botToken = !empty($settings->bot_token) ? trim($settings->bot_token) : null;
    $chatId = !empty($settings->chat_id) ? trim($settings->chat_id) : null;

        // If botToken and chatId are set, send directly via Telegram Bot API
        if ($botToken && $chatId) {
            $url = 'https://api.telegram.org/bot'.rawurlencode($botToken).'/sendMessage';
            $post = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POSTFIELDS => $post,
            ]);
            $result = curl_exec($ch);
            $errno = curl_errno($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($errno !== 0) {
                Log::channel('daily')->error('TelegramNotice: cURL error while sending to Telegram API', [
                    'errno' => $errno,
                    'text' => $text,
                    'url' => $url,
                    'post' => $post,
                ]);
                // Failed to call Telegram API; fallback to existing service
            } else {
                // Inspect Telegram API response
                $decoded = json_decode($result, true);
                if (!is_array($decoded) || empty($decoded['ok'])) {
                    Log::channel('daily')->error('TelegramNotice: Telegram API returned error or unexpected response', [
                        'http_code' => $httpCode,
                        'response' => $result,
                        'text' => $text,
                        'url' => $url,
                        'post' => $post,
                    ]);
                    // fallback to dmdev.ru
                } else {
                    return true;
                }
            }
        }

        // Fallback: previous behavior via dmdev.ru proxy
        $sitename = preg_replace('/^https?:\/\//', '', URL::to('/'));
    // use pechkin secret from settings (fallback to '2207')
    $pechkinSecret = !empty($settings->pechkin_secret) ? $settings->pechkin_secret : '2207';
    $token = md5($sitename.$pechkinSecret);

        $fallbackUrl = 'https://dmdev.ru/api/botPechkin/'.$sitename.':'.$token.'/sendMessage';
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => $fallbackUrl,
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POSTFIELDS => array(
                    'text' =>  $text,
                    'parse_mode' => 'html'
                ),
            )
        );
        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            Log::channel('daily')->error('TelegramNotice: cURL error while sending to fallback dmdev API', [
                'errno' => $errno,
                'http_code' => $httpCode,
                'response' => $result,
                'fallback_url' => $fallbackUrl,
                'text' => $text,
            ]);
        }

        return true;        
    }
}
