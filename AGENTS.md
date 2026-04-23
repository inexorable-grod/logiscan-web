# LogiScan Pro — AGENTS.md

## Stack
- Backend: Laravel 13 + PHP 8.3 + MariaDB 10.11
- Mobile: React Native + Expo SDK 52
- Auth: Laravel Sanctum
- Queue: Database (Redis when available)
- PDF: DomPDF
- Tests: Pest PHP v4

## Code Rules
- Always use Eloquent ORM (never raw SQL without documented justification)
- All public methods in Services must have complete PHPDoc
- All API routes must have their entry in docs/openapi/logiscan-api.yaml
- Use lockForUpdate() in any concurrent transaction
- audit_log must record every action that modifies users, centers, or requests data
- Run `php artisan test` before any commit
- All push notifications must be dispatched as queued jobs
- PDF generation must always be queued (never synchronous)

## Security
- Never expose stack traces in production responses
- Always validate with FormRequest (never request()->all() without filtering)
- Rate limiting on all public API endpoints
- Session regeneration on every login
- One active web session per user

## Roles
- gerente_ops: Global dashboard, read-only
- ti_admin: Full admin access, backup approver
- supervisor: Center-based access (session selector), primary approver
- operario: Assigned center only, scanning and requests

## Documentation Requirements
- ADR for each relevant architectural decision in docs/architecture/decisions/
- PHPDoc on every public method of Controllers and Services
- JSDoc on every hook and component of React Native
- OpenAPI updated on every endpoint change
