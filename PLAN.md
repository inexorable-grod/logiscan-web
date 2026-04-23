# LogiScan Pro — Deployment & Dashboard Implementation Plan

## Current State Summary
- **Backend API:** ~85% complete. 25+ endpoints, 13 controllers, 8 models, tests passing.
- **Missing backend:** Manifest PDF module (controller, jobs, Blade template), rate limiting on login.
- **Dashboard (web):** Does NOT exist — only a welcome page. Needs full build.
- **Mobile app:** Code complete. Needs `eas.json`, plugin fixes in `app.json`, and local APK build.

---

## PHASE 1 — Complete Missing Backend Features

### 1.1 Manifest PDF Module
- Create `ManifestController` with `GET /api/manifest/{routeId}`
- Create `GenerateManifestPdfJob` (queued, uses DomPDF)
- Create Blade template `resources/views/pdf/manifest.blade.php`
- Create `SendManifestEmailJob` (optional email to supervisor)
- Register route in `routes/api.php`
- Update `docs/openapi/logiscan-api.yaml`

### 1.2 Rate Limiting on Login
- Add `throttle:5,1` middleware to `POST /login` route

### 1.3 Run Tests
- `php artisan test` — ensure everything passes before proceeding

---

## PHASE 2 — Web Dashboard (Blade + Tailwind)

Build a full admin dashboard using Laravel Blade + Tailwind CSS (already configured via Vite).

### 2.1 Auth & Layout
- `routes/web.php` — web login/logout routes with session auth
- `DashboardAuthController` — login form, authenticate, logout
- **Layout:** `resources/views/layouts/app.blade.php` — sidebar nav, top bar, responsive
- **Login page:** `resources/views/auth/login.blade.php`
- Middleware: reuse existing `RoleMiddleware` adapted for web sessions

### 2.2 Dashboard Home (per role)
- **Supervisor:** scan stats, pending requests count, today's routes, quick center selector
- **TI Admin:** system overview — users count, centers, pending requests, recent audit log
- **Gerente Ops:** read-only global stats — scans per center, approval rates, route completion

### 2.3 Admin Views (ti_admin only)
- **Users CRUD:** list, create, edit, deactivate, reset password
- **Centers CRUD:** list, create, edit, assign operators
- **Routes CRUD:** list, create, edit (per center)

### 2.4 Shared Views (supervisor + ti_admin)
- **Requests:** list pending/resolved, approve/reject with comment modal
- **Clients:** list, create, edit (filtered by center)

### 2.5 Supervisor Views
- **Center selector:** page/modal to switch active center
- **Dashboard:** center-specific stats and activity

### 2.6 Components
- Reusable Blade components: table, modal, form inputs, alert/toast, stats card, badge (status/role)

---

## PHASE 3 — Mobile App Fixes & Local APK Build

### 3.1 Fix `app.json` plugins
Add missing plugins:
```json
"plugins": [
  "expo-sqlite",
  "expo-secure-store",
  ["expo-camera", {
    "cameraPermission": "LogiScan necesita acceso a la cámara para escanear códigos de barras"
  }],
  ["expo-notifications", {
    "icon": "./assets/icon.png"
  }]
]
```

### 3.2 Create `eas.json`
```json
{
  "cli": { "version": ">= 15.0.0" },
  "build": {
    "development": {
      "developmentClient": true,
      "distribution": "internal"
    },
    "preview": {
      "distribution": "internal",
      "android": { "buildType": "apk" }
    },
    "production": {
      "android": { "buildType": "apk" }
    }
  }
}
```

### 3.3 Add notification permission request
- In `App.tsx` or `ScannerScreen.tsx`, request notification permissions on startup
- Register device token via `POST /api/device-tokens` after login

### 3.4 Local APK Build
```bash
cd logiscan-mobile
npm install
npx expo prebuild --platform android --clean
cd android
./gradlew assembleRelease
```
Output: `android/app/build/outputs/apk/release/app-release.apk`

**Prerequisites:** Java 17+, Android SDK installed.

---

## PHASE 4 — Production Environment Configuration

### 4.1 Backend `.env` for production
- `DB_CONNECTION=mysql` with MariaDB credentials
- `QUEUE_CONNECTION=database` (or redis)
- `SESSION_DRIVER=database`
- `APP_DEBUG=false`, `APP_ENV=production`
- Configure SMTP mail

### 4.2 Database & Seeding
- `php artisan migrate --force`
- Seed initial ti_admin user
- `php artisan config:cache && php artisan route:cache && php artisan view:cache`

### 4.3 Queue Worker
- Start queue worker as a daemon: `php artisan queue:work --queue=default,notifications,emails`

### 4.4 API URL (mobile)
- Currently placeholder `api.logiscan.local` — user will configure when ready

---

## Execution Order
1. Phase 1 (backend gaps) — ~30 min
2. Phase 2 (dashboard) — largest effort, multiple sub-steps
3. Phase 3 (mobile fixes + APK) — depends on Android SDK availability
4. Phase 4 (production config) — final step

Phases 1 and 3 are independent and can be worked in parallel.
