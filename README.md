TelegramNotice Plugin for OctoberCMS
==================================

Описание
--------
Компонент `TelegramNotice` отправляет формы заявок в Telegram. По умолчанию плагин использует внешний прокси `https://dmdev.ru/api/botPechkin/` для отправки сообщений. Начиная с v1.1.0 можно настроить прямую отправку через Telegram Bot API — настройки теперь находятся в админке OctoberCMS (Backend → Settings → Telegram Notice).

Установка (Git)
---------------
1. Клонируйте репозиторий в `plugins/dmdev/telegramnotice`:

   git clone https://github.com/<your>/telegramnotice.git plugins/dmdev/telegramnotice

2. Установите зависимости OctoberCMS (если требуется) и выполните миграции:

   php artisan october:up

Конфигурация
-----------
Настройки плагина (Backend Settings):

- `bot_token` — токен вашего бота Telegram (формат: 123456:ABC...)
- `chat_id` — ID чата или @username для отправки
- `pechkin_secret` — секрет для fallback-прокси (по умолчанию `2207`)

Если `bot_token` и `chat_id` не заданы в настройках, плагин продолжит работать через `dmdev.ru`.

Логирование
----------
Ошибки отправки логируются в канал `daily` (см. `storage/logs/system.log`).

Дальнейшие улучшения
- Перенести `config/telegram.php` в Backend Settings модели для удобной настройки из админки.
- Добавить unit/feature тесты отправки.

Лицензия
-------
MIT
