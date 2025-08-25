<?php namespace Dmdev\Telegramnotice\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code to identify the settings in the database
    public $settingsCode = 'dmdev_telegramnotice_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    // Default values
    public static $defaultValues = [
        'bot_token' => '',
        'chat_id' => '',
        'pechkin_secret' => '',
    ];
}
