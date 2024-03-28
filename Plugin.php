<?php namespace Dmdev\Telegramnotice;

use Backend;
use System\Classes\PluginBase;

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
            'description' => 'No description provided yet...',
            'author' => 'Denis Mishin',
            'icon' => 'icon-leaf'
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

 
}
