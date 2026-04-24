<?php namespace Dmdev\Telegramnotice\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Dmdev\Telegramnotice\Models\Settings;


/**
 * TelegramNotice Component
 *
 * @link https://docs.octobercms.com/4.x/extend/cms-components.html
 */
class TelegramNotice extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'TelegramNotice Component',
            'description' => 'Sends form submissions to Telegram, MAX messenger, or dmdev.ru Pechkin fallback.',
        ];
    }

    /**
     * @link https://docs.octobercms.com/4.x/element/inspector-types.html
     */
    public function defineProperties()
    {
        return [
            'sitename' => [
                'title'       => 'Site name',
                'description' => 'sitename.ru',
                'type'        => 'string',
            ],
            'token' => [
                'title'       => 'Token',
                'description' => 'Enter a 32 digit token',
                'type'        => 'string',
            ],
            'btnName' => [
                'title'       => 'Button name',
                'description' => 'Submit button label',
                'type'        => 'string',
            ],
        ];
    }

    public function onTelegramNoticeSendForm()
    {
        // Accept multiple possible input names to be tolerant with theme templates
        $rawPhone = input('ph') ?? input('contact') ?? input('phone') ?? input('phone_number');
        $phone_number = preg_replace('/[^0-9.+]/', '', $rawPhone ?? '');

        $rawName = input('nm') ?? input('name') ?? input('fullname');
        $name = preg_replace("/&#?[a-z0-9]+;/i", '', $rawName ?? '');

        $tag  = preg_replace("/&#?[a-z0-9]+;/i", '', input('tag')  ?? '');
        $from = preg_replace("/&#?[a-z0-9]+;/i", '', input('from') ?? '');

        $arr = null;
        $inputArr = input('arr');
        if (is_array($inputArr)) {
            foreach ($inputArr as $key => $arrValue) {
                $clean = preg_replace("/&#?[a-z0-9]+;/i", '', $arrValue);
                $arr   = ($key === array_key_first($inputArr)) ? $clean : $arr . "\n" . $clean;
            }
        }

        $text = 'Сайт: ' . preg_replace('/^https?:\/\//', '', URL::to('/'));
        if (!empty($from))              $text .= "\n<b>" . $from . '</b>';
        elseif (!empty($this->page->title)) $text .= "\n" . $this->page->title;
        if (!empty($name))         $text .= "\nИмя: <code>" . $name . '</code>';
        if (!empty($phone_number)) $text .= "\nТел.: <code>" . $phone_number . '</code>';
        if (!empty($tag))          $text .= "\n" . $tag;
        if (!empty($arr))          $text .= "\n" . $arr;

        if (strlen($phone_number) > 4) {
            $ok = $this->sendMessage($text);
            $this->page['result'] = $ok;
        }
    }

    /**
     * Dispatch message to all configured channels.
     * Priority:
     *  - Telegram + MAX configured   → send to both; true if at least one succeeds
     *  - Only Telegram configured     → send to Telegram
     *  - Only MAX configured          → send to MAX
     *  - Neither configured           → fallback to Pechkin
     */
    protected function sendMessage($text)
    {
        $settings = Settings::instance();

        $hasTelegram = !empty($settings->bot_token) && !empty($settings->chat_id);
        $hasMax      = !empty($settings->max_bot_token) && !empty($settings->max_chat_id);

        if (!$hasTelegram && !$hasMax) {
            return $this->sendToPechkin($settings, $text);
        }

        $ok = false;
        if ($hasTelegram) {
            $ok = $this->sendToTelegram($settings, $text) || $ok;
        }
        if ($hasMax) {
            $ok = $this->sendToMax($settings, $text) || $ok;
        }

        return $ok;
    }

    /**
     * Send via Telegram Bot API.
     * POST https://api.telegram.org/bot{token}/sendMessage
     */
    protected function sendToTelegram($settings, $text)
    {
        $botToken = trim($settings->bot_token);
        $chatId   = trim($settings->chat_id);
        $url      = 'https://api.telegram.org/bot' . $botToken . '/sendMessage';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POSTFIELDS     => http_build_query([
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $result   = curl_exec($ch);
        $errno    = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            Log::error('TelegramNotice: cURL error sending to Telegram', [
                'errno' => $errno,
                'url'   => $url,
            ]);
            return false;
        }

        $decoded = json_decode($result, true);
        if (is_array($decoded) && !empty($decoded['ok'])) {
            return true;
        }

        Log::error('TelegramNotice: Telegram API error', [
            'http_code' => $httpCode,
            'response'  => $result,
        ]);
        return false;
    }

    /**
     * Send via MAX platform Bot API.
     * POST https://platform-api.max.ru/messages?chat_id={chat_id}
     * Header: Authorization: {token}   (no "Bearer" prefix per MAX docs)
     * Body JSON: {"text": "..."}   — MAX does NOT support HTML; text is converted to plain-text first.
     */
    protected function sendToMax($settings, $text)
    {
        $maxToken  = trim($settings->max_bot_token);
        $maxChatId = (int) $settings->max_chat_id;
        $url       = 'https://platform-api.max.ru/messages?chat_id=' . $maxChatId;

        $body = json_encode(['text' => $this->htmlToPlain($text)]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: ' . $maxToken,
            ],
        ]);
        $result   = curl_exec($ch);
        $errno    = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            Log::error('TelegramNotice: cURL error sending to MAX', [
                'errno' => $errno,
                'url'   => $url,
            ]);
            return false;
        }

        // MAX returns 200 with a message object on success
        if ($httpCode >= 200 && $httpCode < 300) {
            $decoded = json_decode($result, true);
            if (is_array($decoded) && isset($decoded['message'])) {
                return true;
            }
        }

        Log::error('TelegramNotice: MAX API error', [
            'http_code' => $httpCode,
            'response'  => $result,
        ]);
        return false;
    }

    /**
     * Fallback via dmdev.ru Pechkin proxy.
     * Used only when neither Telegram nor MAX are configured.
     */
    protected function sendToPechkin($settings, $text)
    {
        $siteUrl  = URL::to('/');
        $sitename = parse_url($siteUrl, PHP_URL_HOST);
        if (empty($sitename)) {
            $sitename = rtrim(preg_replace('/^https?:\/\//', '', $siteUrl), '/');
        }

        $pechkinSecret = !empty($settings->pechkin_secret) ? trim($settings->pechkin_secret) : null;
        if (!$pechkinSecret) {
            try {
                $pechkinSecret = bin2hex(random_bytes(16));
            } catch (\Exception $e) {
                $pechkinSecret = md5(uniqid((string) mt_rand(), true));
            }
            try {
                $settings->pechkin_secret = $pechkinSecret;
                $settings->save();
            } catch (\Exception $e) {
                Log::warning('TelegramNotice: could not save generated pechkin_secret', [
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        $apiBase     = rtrim(env('DMDEV_API_BASE_URL', 'https://dmdev.ru'), '/');
        $fallbackUrl = $apiBase . '/api/v1/pechkin/sendMessage';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $fallbackUrl,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POSTFIELDS     => http_build_query(['text' => $text, 'parse_mode' => 'HTML']),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $pechkinSecret,
                'X-Site-Name: ' . $sitename,
            ],
        ]);
        $result   = curl_exec($ch);
        $errno    = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || $httpCode < 200 || $httpCode >= 300) {
            Log::error('TelegramNotice: Pechkin fallback error', [
                'errno'        => $errno,
                'http_code'    => $httpCode,
                'fallback_url' => $fallbackUrl,
            ]);
            return false;
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded) || empty($decoded['ok'])) {
            Log::error('TelegramNotice: Pechkin unexpected response', [
                'response'     => $result,
                'http_code'    => $httpCode,
                'fallback_url' => $fallbackUrl,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Convert HTML (Telegram parse_mode=HTML) to plain-text for MAX messenger.
     * MAX does not support HTML formatting — it must receive plain text.
     *
     * <code>value</code>  →  «value»
     * other tags          →  stripped (text preserved)
     * HTML entities       →  decoded
     */
    protected function htmlToPlain(string $html): string
    {
        // <code>...</code> → «...»  so phone/name values stay readable
        $text = preg_replace_callback(
            '/<code>(.*?)<\/code>/si',
            static fn($m) => '«' . $m[1] . '»',
            $html
        );

        // strip all remaining HTML tags
        $text = strip_tags($text);

        // decode HTML entities (&amp; &lt; etc.)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $text;
    }
}

