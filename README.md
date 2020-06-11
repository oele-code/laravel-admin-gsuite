# Laravel Admin GSuite
A Laravel package to setup GSuite Admin SDK.

## Installation
You can install the package using composer
```
composer require oele_code/laravel-admin-gsuite
```

Publish the configurations
```
php artisan vendor:publish --provider="oele_code\LaravelAdminGSuite\Providers\GSuiteServiceProvider" --tag="config"
```

## Setting up GSuite Admin Service
In your `.env` file, add the following credentials:
```
GOOGLE_APPLICATION_CREDENTIALS=your_gsuite_service_account_crendentials
GOOGLE_SERVICE_ACCOUNT_IMPERSONATE=your_gsuite_admin_email
```
To know more about service account and steps to get one, visit [the official Google Documentation](https://developers.google.com/identity/protocols/OAuth2ServiceAccount).

**NOTE:** Make sure you enable `Domain-wide Delegation` when creating the service account for your project.

You can now use various services provided by the package. For example, if you want to fetch a user details, you can use the `GSuiteUserService` facade.
```php
use oele_code\LaravelAdminGSuite\Facades\GSuiteUserService;

// ...

$user = GSuiteUserService::fetch('jon@mycompany.com');

echo $user->getName(); // Jon Snow
```

Forked from [ColoredCow/laravel-gsuite](https://github.com/ColoredCow/laravel-gsuite).
