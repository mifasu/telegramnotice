<?php namespace Dmdev\Telegramnotice;

use Backend;
use System\Classes\PluginBase;
use Dmdev\Telegramnotice\Models\Settings;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'Telegram Notice',
            'description' => 'Send frontend form submissions to Telegram via direct Bot API or fallback proxy.',
            'author' => 'Denis Mishin',
            'icon' => 'icon-telegram'
        ];
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return [
            'Dmdev\Telegramnotice\Components\TelegramNotice' => 'TelegramNotice',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Telegram Notice',
                'description' => 'Configure Telegram bot token and chat id',
                'icon' => 'icon-telegram',
                'class' => 'Dmdev\\Telegramnotice\\Models\\Settings',
                'order' => 500,
                'keywords' => 'telegram bot chat'
            ]
        ];
    }

    public function boot()
    {
        // If config file exists, migrate values into settings (first run)
        $configPath = plugins_path('dmdev/telegramnotice/config/telegram.php');
        if (file_exists($configPath)) {
            $cfg = require $configPath;
            if (is_array($cfg)) {
                $settings = Settings::instance();
                $changed = false;
                foreach (['bot_token','chat_id','pechkin_secret'] as $key) {
                    if (!empty($cfg[$key]) && empty($settings->{$key})) {
                        $settings->{$key} = $cfg[$key];
                        $changed = true;
                    }
                }
                if ($changed) $settings->save();
            }
        }
    }

 
}
