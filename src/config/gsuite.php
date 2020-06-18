<?php

return [
    'hosted_domain' => env('GSUITE_HOSTED_DOMAIN'),

    /**
     * When using the GSuite API services, we need to know the application credentials for your domain.
     * This is the service account file that you need to get from your Google Developer Console.
     *
     * Laravel-GSuite package utilizes domain wide delegation. Make sure you enable that at the time of file creation.
     */
    'application_credentials' => env('GSUITE_APPLICATION_CREDENTIALS'),

    /**
     * This must be the super-admin account of your GSuite organization.
     * Laravel-GSuite will act on behalf of this user to perform all the API actions.
     */
    'service_account_impersonate' => env('GSUITE_SERVICE_ACCOUNT_IMPERSONATE'),


];
