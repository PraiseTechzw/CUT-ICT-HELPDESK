# Phase 1 Implementation Sources

External compatibility and framework behavior checked on 2026-10-10 against official documentation:

- Laravel 13's release notes state it requires PHP 8.3 or later, list supported PHP versions as 8.3–8.5, and show security fixes through 2028-03-17. This supports using Laravel 13 with the Ubuntu-provided PHP 8.3 for local implementation; it does **not** establish CUT's production runtime requirement.
- PHP's official supported-versions page lists PHP 8.3 security support through 2027-12-31 and PHP 8.5 through 2029-12-31. The sandbox package repositories provide PHP 8.3; institutional hosting remains to be confirmed.
- Laravel's official starter-kit documentation states that the kits use Laravel Fortify and that registration and password-reset features are optional and can be disabled. This implementation uses Fortify with a custom Blade login and no public registration or password reset pending approved mail/identity requirements.
- Laravel's authentication documentation recommends regenerating the session after successful authentication and invalidating the session plus regenerating the CSRF token on logout. It also documents default login throttling by email/username and IP; the application sets an explicit five-attempt-per-minute email/IP limiter.

## References

1. [Laravel 13 Release Notes](https://laravel.com/framework/docs/13.x/releases)
2. [PHP Supported Versions](https://www.php.net/supported-versions.php)
3. [Laravel 13 Starter Kits](https://laravel.com/framework/docs/13.x/starter-kits)
4. [Laravel 13 Authentication](https://laravel.com/framework/docs/13.x/authentication)
