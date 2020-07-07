<?php

namespace oeleco\Larasuite\Services;

use Google_Client;
use Google_Service_Directory;

abstract class Service
{
    protected $client;
    public $service;

    /**
     * Get scopes required for the service to make the API calls.
     *
     * @return array
     */
    abstract public function getServiceSpecificScopes(): array;

    /**
     * Sets the service instance for the Google Service in use.
     *
     * @return void
     */
    abstract public function setService();

    public function __construct()
    {
        $this->setClient();
        $this->setService();
    }

    protected function setClient()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('oeleco/laravel-admin-gsuite');
        $this->client->setAuthConfig($this->getApplicationCredentials());
        $this->client->setSubject($this->getImpersonateUser());
        $this->client->addScope($this->getServiceSpecificScopes());
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getApplicationCredentials()
    {
        return config('gsuite.application_credentials');
    }

    public function getImpersonateUser()
    {
        return config('gsuite.service_account_impersonate');
    }
}
