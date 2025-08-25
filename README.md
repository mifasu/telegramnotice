TelegramNotice — OctoberCMS plugin / Плагин TelegramNotice для OctoberCMS
=====================================================================

Русский (RU)
------------

Кратко
: Компонент `TelegramNotice` отправляет данные форм (заявки) в Telegram. По умолчанию используется внешний прокси `https://dmdev.ru/api/botPechkin/` для совместимости. Начиная с v1.1.1 все настройки доступны в Backend Settings (Backend → Settings → Telegram Notice).

Быстрая установка
-----------------

Через менеджер плагинов October (рекомендуется):

```powershell
php artisan plugin:install Dmdev.Telegramnotice --from=https://github.com/mifasu/telegramnotice.git
```

Или вручную (клонировать и выполнить миграции):

```powershell
cd %CMS_ROOT%\plugins\dmdev
git clone https://github.com/mifasu/telegramnotice.git telegramnotice
php artisan october:up
```

Использование
------------

Добавьте компонент в страницу или шаблон October:

```htm
{% component 'telegramNotice' %}
```

Конфигурация
------------

В Backend → Settings → Telegram Notice настройте:

- `bot_token` — токен вашего бота (`123456:ABC-DEF...`)
- `chat_id` — целевой чат (`-1001234567890` или `@channelname`)
- `pechkin_secret` — секрет для fallback (по умолчанию `2207`)

Если `bot_token` и `chat_id` пусты, плагин использует fallback через `dmdev.ru`.

Логирование
-----------

Ошибки и неудачные ответы записываются в канал `daily` (см. `storage/logs/system.log`).

Миграция настроек
-----------------

Если раньше использовался файл `plugins/dmdev/telegramnotice/config/telegram.php`, при первом старте плагин попытается автоматически перенести значения в Backend Settings (если поля в Settings пусты).

Технические заметки и устраняeмые проблемы
----------------------------------------

- Если установка через SSH (`git@github.com:...`) блокируется — примите fingerprint GitHub или используйте HTTPS URL.
- Для приватных репозиториев используйте SSH‑ключ с доступом или HTTPS с Personal Access Token.
- Убедитесь, что в папке плагина присутствует `Plugin.php` и корректный `updates/version.yaml`.

Лицензия
-------

MIT

English (EN)
--------------

Quick summary
: The `TelegramNotice` component sends frontend form submissions to Telegram. By default it uses the `https://dmdev.ru/api/botPechkin/` proxy for compatibility. Since v1.1.1 configuration moved to Backend Settings (Backend → Settings → Telegram Notice).

Quick install
-------------

Install via October's plugin installer (recommended):

```powershell
php artisan plugin:install Dmdev.Telegramnotice --from=https://github.com/mifasu/telegramnotice.git
```

Or clone manually and run migrations:

```powershell
cd %CMS_ROOT%\plugins\dmdev
git clone https://github.com/mifasu/telegramnotice.git telegramnotice
php artisan october:up
```

Usage
-----

Add component to a page/layout:

```htm
{% component 'telegramNotice' %}
```

Configuration
-------------

Set the following in Backend → Settings → Telegram Notice:

- `bot_token` — your bot token (e.g. `123456:ABC-DEF...`)
- `chat_id` — target chat id (e.g. `-1001234567890`) or `@channelname`
- `pechkin_secret` — secret for fallback (default `2207`)

If `bot_token` and `chat_id` are empty the plugin will fallback to the dmdev.ru proxy.

Logging
-------

Send errors are written to the `daily` log channel (`storage/logs/system.log`).

Migration notes
---------------

If you previously used the physical config file `plugins/dmdev/telegramnotice/config/telegram.php`, the plugin will try to migrate those values into backend settings on first boot.

Troubleshooting
---------------

- If `php artisan plugin:install` with an SSH URL prompts for a fingerprint, run `ssh -T git@github.com` and answer `yes` or use the HTTPS URL instead.
- For private repos configure an SSH key on GitHub or use HTTPS with a Personal Access Token.
- Ensure `Plugin.php` exists in the plugin root and `updates/version.yaml` is present.

License
-------

MIT

Автор: Denis Mishin
Сайт: https://dmdev.ru