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
            $ok = $this->sendTelegram($text);
            $this->page['result'] = $ok ? true : false;
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
            $url = 'https://api.telegram.org/bot'.$botToken.'/sendMessage';
            $postFields = [
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
                CURLOPT_POSTFIELDS => http_build_query($postFields),
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
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
                    'post' => $postFields,
                ]);
                // Failed to call Telegram API; fallback to existing service
            } else {
                // Inspect Telegram API response
                $decoded = json_decode($result, true);
                if (is_array($decoded) && !empty($decoded['ok'])) {
                    return true;
                }

                Log::channel('daily')->error('TelegramNotice: Telegram API returned error or unexpected response', [
                    'http_code' => $httpCode,
                    'response' => $result,
                    'text' => $text,
                    'url' => $url,
                    'post' => $postFields,
                ]);
                // fallback to dmdev.ru
            }
        }

        // Fallback: previous behavior via dmdev.ru proxy
        $siteUrl = URL::to('/');
        $sitename = parse_url($siteUrl, PHP_URL_HOST);
        if (empty($sitename)) {
            $sitename = rtrim(preg_replace('/^https?:\/\//', '', $siteUrl), '/');
        }

        // If pechkin_secret is provided in settings, use it directly as token.
        // If not provided, generate pechkin_secret = md5(sitename), persist it and use as token.
        $originalPechkin = !empty($settings->pechkin_secret) ? trim($settings->pechkin_secret) : null;
        if ($originalPechkin) {
            $pechkinSecret = $originalPechkin;
        } else {
            // generate a random token (32 hex chars)
            try {
                $pechkinSecret = bin2hex(random_bytes(16));
            } catch (\Exception $e) {
                // fallback to less secure unique id
                $pechkinSecret = md5(uniqid((string)mt_rand(), true));
            }
            // persist generated secret to settings so token remains stable
            try {
                $settings->pechkin_secret = $pechkinSecret;
                $settings->save();
            } catch (\Exception $e) {
                Log::channel('daily')->warning('TelegramNotice: could not save generated pechkin_secret to settings', [
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        // Use pechkin secret directly as token (per settings behaviour)
        $token = $pechkinSecret;

        $apiBase = rtrim(env('DMDEV_API_BASE_URL', 'https://dmdev.ru'), '/');
        $fallbackUrl = $apiBase.'/api/v1/pechkin/sendMessage';

        Log::channel('daily')->info('TelegramNotice: using pechkin_secret for fallback', [
            'sitename' => $sitename,
            'pechkin_secret' => $pechkinSecret,
            'token' => $token,
            'fallback_url' => $fallbackUrl,
            'api_base' => $apiBase,
        ]);

        $postFields = ['text' => $text, 'parse_mode' => 'HTML'];
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer '.$token,
            'X-Site-Name: '.$sitename,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $fallbackUrl,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => http_build_query($postFields),
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || $httpCode < 200 || $httpCode >= 300) {
            Log::channel('daily')->error('TelegramNotice: error while sending to fallback dmdev API', [
                'errno' => $errno,
                'http_code' => $httpCode,
                'response' => $result,
                'fallback_url' => $fallbackUrl,
                'api_base' => $apiBase,
                'text' => $text,
            ]);
            return false;
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded) || empty($decoded['ok'])) {
            Log::channel('daily')->error('TelegramNotice: DMDEV API returned unexpected response', [
                'response' => $result,
                'http_code' => $httpCode,
                'fallback_url' => $fallbackUrl,
                'api_base' => $apiBase,
                'text' => $text,
            ]);
            return false;
        }

        return true;
    }
}
