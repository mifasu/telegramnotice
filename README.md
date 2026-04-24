# TelegramNotice — OctoberCMS Plugin

Send frontend form submissions to Telegram. Supports direct Telegram Bot API and an automatic fallback to the **dmdev.ru / Pechkin** proxy when bot credentials are not configured.

- Plugin code: `Dmdev.Telegramnotice`
- Requires: OctoberCMS 4.x, PHP 8.0+
- Author: [Denis Mishin](https://dmdev.ru)
- License: MIT

---

## Features

- Component `TelegramNotice` renders a ready-to-use contact form on any page or layout.
- Sends messages directly via **Telegram Bot API** when `bot_token` + `chat_id` are configured.
- Falls back to the **dmdev.ru Pechkin proxy** automatically when bot credentials are absent or the direct send fails — the plugin still works out of the box without any Telegram bot.
- `pechkin_secret` is generated and persisted automatically if not provided.
- Fallback uses canonical `POST /api/v1/pechkin/sendMessage` with `Authorization: Bearer` + `X-Site-Name` headers; base URL overrideable via `DMDEV_API_BASE_URL` env variable.
- All settings managed in **Backend → Settings → Telegram Notice**.
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

Open **Backend → Settings → Telegram Notice** and fill in:

| Field | Description |
|---|---|
| `bot_token` | Telegram bot token, e.g. `123456:ABC-DEF…` |
| `chat_id` | Target chat/channel id, e.g. `-1001234567890` or `@channelname` |
| `pechkin_secret` | Secret for the dmdev.ru fallback (auto-generated if left empty) |

**Behaviour priority:**
1. If `bot_token` **and** `chat_id` are set → message is sent directly via Telegram Bot API.
2. If either is missing, or the direct send fails → fallback: `POST /api/v1/pechkin/sendMessage` with `Authorization: Bearer <token>` and `X-Site-Name: <sitename>` headers against `DMDEV_API_BASE_URL` (default `https://dmdev.ru`).
3. If `pechkin_secret` is empty, the plugin generates a random 32-character token, saves it, and reuses it.

> The plugin **always works** even without any configuration — it will use the Pechkin fallback automatically.

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

Компонент `TelegramNotice` отправляет данные форм с сайта в Telegram. Если `bot_token` и `chat_id` заданы — отправка идёт напрямую через Telegram Bot API. Иначе используется fallback через `dmdev.ru / Pechkin` — плагин **работает даже без настройки бота**.

Настройка: **Панель управления → Настройки → Telegram Notice**.
