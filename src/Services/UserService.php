<?php

namespace oeleco\LaravelAdminGSuite\Services;

use Google_Service_Directory;
use Google_Service_Directory_User;
use Google_Service_Directory_UserName;
use Illuminate\Support\Facades\Validator;

class UserService extends Service
{
    protected $name;
    protected $joinedOn;
    protected $designation;

    public function getServiceSpecificScopes(): array
    {
        return [
            Google_Service_Directory::ADMIN_DIRECTORY_USER,
            Google_Service_Directory::ADMIN_DIRECTORY_USER_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_USER_SECURITY,
        ];
    }

    public function setService()
    {
        $this->service = new Google_Service_Directory($this->client);
    }

    public function getUser(string $email)
    {
        return $this->service->users->get($email);
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function setUser(Google_Service_Directory_User $user)
    {
        $this->setEmail($user->getPrimaryEmail());
        $this->setFirstName($user->getName()->getGivenName());
        $this->setLastName($user->getName()->getFamilyName());

        return $this;
    }

    public function fetch($email)
    {
        $user = $this->getUser($email);
        return  $this->setUser($user);
    }

    public function create(array $params, array $optionalParams = [])
    {
        Validator::make($params, [
                'email'     => 'required|email',
                'password'  => 'required|min:8',
                'firstName' => 'required|min:3',
                'lastName'  => 'required|min:3',
            ])->validate();

        $nameInstance    = new Google_Service_Directory_UserName;
        $nameInstance->setGivenName($params['firstName']);
        $nameInstance->setFamilyName($params['lastName']);

        $userInstance = new Google_Service_Directory_User;
        $userInstance->setName($nameInstance);
        $userInstance->setPrimaryEmail($params['email']);
        $userInstance->setPassword($params['password']);

        try {
            $user = $this->service->users->insert($userInstance, $optionalParams);
            return $this->setUser($user);
        } catch (\Google_Service_Exception $gse) {
            return $this->fetch($params['email']);
        }
    }

    public function updateName(string $email, array $params)
    {
        Validator::make($params, [
            'firstName' => 'required|min:3',
            'lastName' => 'required|min:3',
        ])->validate();

        $user = $this->getUser($email);
        $name = $user->getName();

        $name->setGivenName($params['firstName']);
        $name->setFamilyName($params['lastName']);
        $user->setName($name);

        $updatedUser = $this->service->users->update($email, $user);

        return $this->setUser($updatedUser);
    }

    public function deleteUser(string $email)
    {
        $response = $this->service->users->delete($email);
        return $response->getBody()->getContents();
    }
}
