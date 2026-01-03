# PHP Entitlement Enforcement Library

A PHP library for enforcing **commercial entitlement validation** in PHP web applications using a **central entitlement server**.

This library validates an entitlement key and domain, caches the entitlement result internally, enforces application blocking when an entitlement becomes invalid, and supports **server-driven enforcement** via a webhook.

> ⚠️ This library is **not DRM**. It is designed for commercial entitlement enforcement, not piracy prevention.

---

## Table of Contents
1. Overview
2. Requirements
3. Installation
4. Basic Usage
5. Webhook Setup
6. Extension Usage
7. Blocking Behavior
8. What the Application Controls
9. What the Library Controls
10. Notes & Limitations

---

## 1. Overview

The PHP Entitlement Enforcement Library:

- Validates entitlements against a central entitlement server
- Uses an **entitlement key** and **domain name** for validation
- Caches the entitlement verification result internally
- Revalidates entitlements at a randomized interval (1–7 days)
- Fully blocks the application when the entitlement is invalid
- Displays clear, server-defined blocking messages
- Supports forced entitlement revalidation via webhook

---

## 2. Requirements

- PHP 8.0 or higher
- PHP web application (not CLI)
- Composer-compatible autoloading
- HTTP client dependency: Guzzle (installed via Composer)

---

## 3. Installation (Private GitHub Repository Install)

If the repository is private, add it to your consuming application's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/marufarafat/WooPackages"
    }
  ],
  "require": {
    "wooshaper/WooPackages": "dev-main"
  }
}
```

Then authenticate Composer with a GitHub token that has repo access:

```json
{
  "github-oauth": {
    "github.com": "YOUR_GITHUB_TOKEN"
  }
}
```

Finally, install/update:

```bash
composer update wooshaper/WooPackages
```

---

## 4. Basic Usage (Required)

### 4.1 Vanilla PHP

### Step 1: Add Entitlement Key

Add your entitlement key to the application `.env` file:

```env
LICENSE_KEY=your-entitlement-key-here
```

---

### Step 2: Initialize the Library

Call the library at the **very beginning** of your main entry file (e.g. `index.php`).

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use WooPackages\Entitlements\Enforcer;

// MUST be the first executable line
Enforcer::boot();

// Application code starts here
echo 'Welcome to my application';
```

If the entitlement is invalid, the application will be blocked and no further code will execute.

> Note: PHP does not automatically load `.env`. The sample `index.php` includes a small `.env` loader for local testing only. In production, load your environment variables via your runtime or framework.

---

### 4.2 Laravel

#### Middleware (Global)

Create a middleware that boots the entitlement check:

```php
<?php

namespace App\Http\Middleware;

use Closure;use WooPackages\Entitlements\Enforcer;

class EntitlementEnforcerMiddleware
{
    public function handle($request, Closure $next)
    {
        Enforcer::boot();
        return $next($request);
    }
}
```

Register it in `bootstrap/app.php` (Laravel 12):

```php
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\EntitlementEnforcerMiddleware::class);
    })
    ->create();
```

#### Webhook Route (API)

Use an API route to avoid CSRF:

```php
use WooPackages\Webhook\ForceUpdateController;

Route::post('/entitlement-webhook.php', function () {
    $controller = new ForceUpdateController();
    $controller->handle();
});
```

Call it at:

```
POST https://yourapp.com/api/entitlement-webhook.php
```

---

## 5. Webhook Setup (Required)

The library provides its own webhook entry file:

```text
vendor/wooshaper/woopackages/public/webhook.php
```

You must expose this file via your web server (for example, using a symlink):

```bash
ln -s \
vendor/wooshaper/woopackages/public/webhook.php \
public/api/entitlement-webhook.php
```

The entitlement server will call:

```text
POST https://yourapp.com/api/entitlement-webhook.php
```

### Webhook Security
The library validates:
- `X-Entitlement-Key` header
- That the entitlement key matches the application entitlement key
- That the request originates from the entitlement server

The application does **not** implement webhook logic.

---

## 6. Extension Usage

The entitlement server may define extensions that enable or disable specific capabilities.

Example entitlement response:

```json
{
  "extensions": [
    { "name": "analytics", "is_enabled": true },
    { "name": "reports", "is_enabled": false }
  ]
}
```

### Checking Extensions

```php
use WooPackages\Entitlements\ExtensionManager;

if (!ExtensionManager::enabled('analytics')) {
    exit('Analytics extension is not enabled');
}
```

- Returns `true` if enabled
- Returns `false` if disabled or missing

---

## 6.1 Activation Payload & Response

The entitlement server expects the activation payload to include the domain:

```json
{
  "activation": "domain.com"
}
```

Example response:

```json
{
  "status": true,
  "message": "License verified successfully",
  "acknowledgement": {
    "entitlement": {
      "status": "active",
      "expires_at": "2026-01-16T11:39:29+00:00"
    },
    "extensions": [
      { "name": "test", "is_enabled": true },
      { "name": "test 2", "is_enabled": true }
    ]
  }
}
```

If `status` is false, the library blocks and displays the `message` exactly as provided.

---

## 7. Blocking Behavior

When the entitlement is invalid:

- Application execution stops immediately
- The landing page is replaced
- The blocking message is shown **exactly as returned by the entitlement server**
- No application code continues executing

---

## 8. What the Application Controls

The consuming application:

- Installs the library
- Provides the entitlement key via `.env`
- Calls `Enforcer::boot()`
- Exposes the webhook entry file

Nothing else.

---

## 9. What the Library Controls

The library exclusively controls:

- Entitlement server communication
- Cache storage and revalidation
- Application blocking
- Webhook handling
- Extension enforcement

The application cannot override these behaviors.

---

## 10. Notes & Limitations

- This library is **not DRM**
- Application owners may modify code to bypass enforcement
- Designed for commercial licensing, not piracy prevention
- Works only for PHP web applications
- Tests run via `composer test` (requires PHPUnit from Composer)

---

## Summary

This library provides a simple, transparent, and enforceable licensing mechanism for PHP web applications, with centralized control and clear communication when enforcement occurs.
