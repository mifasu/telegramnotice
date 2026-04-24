<?php namespace DMdev\Telegramnotice\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code to identify the settings in the database
    public $settingsCode = 'dmdev_telegramnotice_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    // Default values for settings fields
    protected $defaultValues = [
        'bot_token'      => '',
        'chat_id'        => '',
        'max_bot_token'  => '',
        'max_chat_id'    => '',
        'pechkin_secret' => '',
    ];
}
