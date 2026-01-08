TelegramNotice — OctoberCMS plugin / Плагин TelegramNotice для OctoberCMS
=====================================================================

Русский (RU)
------------

Кратко
: Компонент `TelegramNotice` в первую очередь отправляет данные форм напрямую в Telegram через Bot API (если заданы `bot_token` и `chat_id`). При отсутствии этих настроек или при ошибках отправки доступен fallback — отправка через собственный провайдер (dmdev.ru / Pechkin). Начиная с v1.1.2 все настройки доступны в Backend Settings (Backend → Settings → Telegram Notice).

Changelog (v1.1.2)
------------------
- Исправлена прямая отправка в Telegram: больше не кодируем весь `bot_token` в URL, параметры отправляются как `application/x-www-form-urlencoded`.
- Улучшена обработка ошибок и логирование.
- При использовании fallback на `dmdev.ru` теперь корректно нормализуется `sitename`.
- Если `pechkin_secret` отсутствует — он генерируется и сохраняется в настройках, чтобы токен оставался постоянным.
- Компонент возвращает реальный статус отправки (`page['result'] = true|false`).

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

Подключение компонента
----------------------

В OctoberCMS компонент нужно сначала подключить к странице или шаблону. Например, в верхней части страницы (header) добавьте блок компонентов:

```
title = "Контакты"
url = "/contact"
layout = "default"

[telegramNotice]
```

Затем вставьте вызов компонента в разметку страницы/шаблона:

```htm
{% component 'telegramNotice' %}
```

Конфигурация
------------

В Backend → Settings → Telegram Notice настройте в первую очередь:

- `bot_token` — токен вашего бота (`123456:ABC-DEF...`). Если указан вместе с `chat_id`, плагин будет отправлять сообщения напрямую через Telegram Bot API.
- `chat_id` — целевой чат (`-1001234567890` или `@channelname`).
- `pechkin_secret` — секрет для fallback (если не указан, плагин сгенерирует случайный секрет и сохранит его в настройках).

Поведение:
- Если заданы `bot_token` и `chat_id` — отправка идёт напрямую в Telegram Bot API.
- Если прямой способ недоступен или данные не заданы — используется fallback через `https://dmdev.ru/api/botPechkin/{sitename}:{token}/sendMessage`, где `{token}` соответствует `pechkin_secret` из настроек (если он задан) или автоматически сгенерированному и сохранённому значению.

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
: The `TelegramNotice` component primarily sends frontend form submissions directly to Telegram via the Bot API when `bot_token` and `chat_id` are configured. If those are missing or the direct send fails, it can fallback to a custom provider (dmdev.ru / Pechkin). Since v1.1.2 configuration moved to Backend Settings (Backend → Settings → Telegram Notice).

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

Registering the component
-------------------------

First register the component on the page or layout. For example, add the component block at the top of the page:

```
title = "Contact"
url = "/contact"
layout = "default"

[telegramNotice]
```

Then place the component where you want it to render:

```htm
{% component 'telegramNotice' %}
```

Configuration
-------------

Set the following in Backend → Settings → Telegram Notice (priority order):

- `bot_token` — your bot token (e.g. `123456:ABC-DEF...`). When provided together with `chat_id`, the plugin will send messages directly through the Telegram Bot API.
- `chat_id` — target chat id (e.g. `-1001234567890`) or `@channelname`.
- `pechkin_secret` — secret for fallback. If omitted the plugin will generate a random secret, save it in settings, and use it as the token for the fallback provider.

Behavior:
- Direct Bot API send is attempted first when `bot_token` and `chat_id` are set.
- If direct send is not possible or credentials are missing, the plugin will use the dmdev.ru Pechkin API as a fallback.

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