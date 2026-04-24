# TelegramNotice — OctoberCMS Plugin

Send frontend form submissions to **Telegram**, **MAX messenger**, or both simultaneously. Falls back automatically to the **dmdev.ru / Pechkin** proxy when neither is configured.

- Plugin code: `Dmdev.Telegramnotice`
- Requires: OctoberCMS 4.x, PHP 8.0+
- Author: [Denis Mishin](https://dmdev.ru)
- License: MIT

---

## Features

- Component `TelegramNotice` renders a ready-to-use contact form on any page or layout.
- **Telegram**: sends directly via Telegram Bot API when `bot_token` + `chat_id` are configured.
- **MAX**: sends via MAX platform API (`platform-api.max.ru`) when `max_bot_token` + `max_chat_id` are configured.
- **Simultaneous delivery**: if both Telegram and MAX are configured the message goes to **both channels at once**.
- **Pechkin fallback**: if neither Telegram nor MAX is configured, the plugin uses the dmdev.ru/Pechkin proxy — works out of the box with zero configuration.
- MAX does not support HTML — text is automatically converted to plain-text (`<code>val</code>` → `«val»`, other tags stripped).
- `pechkin_secret` is generated and persisted automatically if not provided.
- Fallback uses canonical `POST /api/v1/pechkin/sendMessage` with `Authorization: Bearer` + `X-Site-Name` headers; base URL overrideable via `DMDEV_API_BASE_URL` env variable.
- All settings managed in **Backend → Settings → Telegram Notice** (grouped by channel).
- Two ready layouts included: a standalone form and a Bootstrap 5 modal form.
- Errors are logged via the standard Laravel/October `Log` facade.

---

## Installation

### Via October plugin installer (recommended)

```bash
php artisan plugin:install Dmdev.Telegramnotice --from=https://github.com/mifasu/telegramnotice.git
```

### Manual installation

```bash
cd plugins/dmdev
git clone https://github.com/mifasu/telegramnotice.git telegramnotice
php artisan october:up
```

---

## Configuration

Open **Backend → Settings → Telegram Notice** and fill in the channels you want to use.

### Telegram Bot API

| Field | Description |
|---|---|
| `bot_token` | Telegram bot token, e.g. `123456:ABC-DEF…` |
| `chat_id` | Target Telegram chat/channel id, e.g. `-1001234567890` or `@channelname` |

### MAX Messenger Bot API

| Field | Description |
|---|---|
| `max_bot_token` | MAX bot access token (find it in [business.max.ru](https://business.max.ru/self) → Chat bots → Integration) |
| `max_chat_id` | Target MAX chat or channel ID (integer, e.g. `12345678`) |

> **Note:** MAX does not support HTML in message text. The plugin automatically converts HTML to plain-text before sending: `<code>value</code>` → `«value»`, other tags are stripped, HTML entities are decoded.

### Fallback (dmdev.ru / Pechkin)

| Field | Description |
|---|---|
| `pechkin_secret` | Secret for the dmdev.ru fallback (auto-generated if left empty) |

**Routing logic:**

| Configured | Result |
|---|---|
| Telegram only | Sent to Telegram |
| MAX only | Sent to MAX |
| Telegram + MAX | Sent to **both** (success if at least one succeeds) |
| Neither | Sent via Pechkin fallback |

> The plugin **always works** even without any configuration — Pechkin fallback is used automatically. `DMDEV_API_BASE_URL` env variable overrides the fallback base URL (default `https://dmdev.ru`).

---

## Usage

### 1. Attach the component to a page or layout

```ini
title = "Contact"
url = "/contact"
layout = "default"

[telegramNotice]
```

### 2. Render the form

```twig
{% component 'telegramNotice' %}
```

Use the **modal** variant instead:

```twig
{% component 'telegramNotice' template="TelegramNotice::order-modal-form" %}
```

### 3. Component properties (optional)

| Property | Default | Description |
|---|---|---|
| `btnName` | `Оставить заявку` | Submit button label |
| `sitename` | *(auto-detected)* | Legacy — site hostname for Pechkin URL |
| `token` | *(from Settings)* | Legacy — Pechkin token override |

### 4. Accepted form fields

| Field name | Description |
|---|---|
| `ph`, `phone`, `phone_number`, `contact` | Phone number (required, must be > 4 chars) |
| `name`, `nm`, `fullname` | Visitor name |
| `tag` | Optional label/tag appended to the message |
| `from` | Section or page label shown instead of `page.title` |
| `arr[]` | Array of extra values, one per line in the message |

---

## Logging

All send errors are written via `Log::error(...)` and `Log::warning(...)` using the application default log channel (typically `storage/logs/system.log`).

---

## Migration from config file

If you previously used `plugins/dmdev/telegramnotice/config/telegram.php`, the plugin automatically migrates the values into Backend Settings on first boot (only if the Settings fields are still empty).

---

## Troubleshooting

- If `php artisan plugin:install` with an SSH URL prompts for a fingerprint, run `ssh -T git@github.com` and answer `yes` or use the HTTPS URL instead.
- For private repos configure an SSH key on GitHub or use HTTPS with a Personal Access Token.
- Ensure `Plugin.php` exists in the plugin root and `updates/version.yaml` is present.

---

## Changelog

| Version | Notes |
|---|---|
| 1.2.0 | Add MAX messenger support. New settings: `max_bot_token`, `max_chat_id`. Routing: Telegram if set, MAX if set, both simultaneously if both set, Pechkin fallback when neither is configured. MAX text is auto-converted from HTML to plain-text. |
| 1.1.3 | OctoberCMS 4.x compatibility. Fallback uses new canonical API v1 endpoint with Bearer auth. Clean imports, fix Settings model, remove sensitive debug log, standardise Log facade, fix HTML typo. Add LICENSE, marketplace-ready composer.json. |
| 1.1.2 | Fix direct Telegram API call (no URL-encoded token), persist `pechkin_secret`, return real send status, improve error handling. |
| 1.1.1 | Move all config to Backend Settings, remove physical config file, add settings migration. |
| 1.1.0 | Add direct Telegram Bot API support and `pechkin_secret` fallback. |
| 1.0.3 | Bug fixes and refactored send method. |
| 1.0.2 | Support `arr[]` multi-value fields. |
| 1.0.1 | Initial release. |

---

## License

MIT © [Denis Mishin](https://dmdev.ru)

---

## Русский

Компонент `TelegramNotice` отправляет данные форм в **Telegram**, **MAX мессенджер** или оба канала одновременно. Если ни один канал не настроен — автоматически используется fallback через `dmdev.ru / Pechkin`.

**Логика маршрутизации:**
- Заполнены `bot_token` + `chat_id` → отправка в Telegram
- Заполнены `max_bot_token` + `max_chat_id` → отправка в MAX
- Заполнены оба → отправка и в Telegram, и в MAX одновременно
- Не заполнено ничего → отправка через Pechkin (плагин работает из коробки)

**Важно:** MAX не поддерживает HTML-форматирование. Плагин автоматически конвертирует текст перед отправкой: `<code>значение</code>` → `«значение»`, остальные теги удаляются.

Настройка: **Панель управления → Настройки → Telegram Notice**.
