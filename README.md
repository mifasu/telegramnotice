TelegramNotice Plugin for OctoberCMS / Плагин TelegramNotice для OctoberCMS
=============================================================================

Русский (RU)
-----------
Описание
Компонент `TelegramNotice` отправляет формы заявок в Telegram. По умолчанию плагин использует внешний прокси `https://dmdev.ru/api/botPechkin/` для отправки сообщений. Начиная с версии v1.1.1 настройки перенесены в Backend Settings (Backend → Settings → Telegram Notice).

Установка
1. Клонируйте репозиторий в `plugins/dmdev/telegramnotice`:

   git clone https://github.com/mifasu/telegramnotice.git plugins/dmdev/telegramnotice

2. Выполните миграции OctoberCMS:

   php artisan october:up

Конфигурация
- Откройте Backend → Settings → Telegram Notice и укажите:
  - `bot_token` — токен бота Telegram (пример: `123456:ABC-DEF...`)
  - `chat_id` — ID чата (например `-1001234567890`) или `@channelname`
  - `pechkin_secret` — секрет для fallback-прокси (по умолчанию `2207`)

Если `bot_token` и `chat_id` не заполнены, плагин использует прежний fallback через `dmdev.ru`.

Логирование
Ошибки и неудачные ответы API логируются в канал `daily` (файл `storage/logs/system.log`).

Замечания по миграции
Если ранее использовался файл `plugins/dmdev/telegramnotice/config/telegram.php`, при первом запуске значения автоматически мигрируются в Backend Settings (если соответствующие поля пусты).

Лицензия
MIT

English (EN)
--------------
Description
The `TelegramNotice` component sends frontend form submissions to Telegram. By default the plugin uses an external proxy (`https://dmdev.ru/api/botPechkin/`). Since v1.1.1 configuration moved to Backend Settings (Backend → Settings → Telegram Notice).

Installation
1. Clone the repository into `plugins/dmdev/telegramnotice`:

   git clone https://github.com/mifasu/telegramnotice.git plugins/dmdev/telegramnotice

2. Run OctoberCMS migrations:

   php artisan october:up

Configuration
- Open Backend → Settings → Telegram Notice and set:
  - `bot_token` — your Telegram bot token (e.g. `123456:ABC-DEF...`)
  - `chat_id` — target chat id (e.g. `-1001234567890`) or `@channelname`
  - `pechkin_secret` — secret used for dmdev fallback (default `2207`)

If `bot_token` and `chat_id` are empty the plugin will fallback to dmdev.ru proxy.

Logging
Errors and unexpected API responses are logged to the `daily` channel (see `storage/logs/system.log`).

Migration notes
If you previously used the physical config file `plugins/dmdev/telegramnotice/config/telegram.php`, the plugin attempts to migrate those values into the backend settings on first boot.

License
MIT

Автор: Denis Mishin
Сайт: https://dmdev.ru