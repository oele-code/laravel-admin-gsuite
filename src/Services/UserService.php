<?php

namespace oeleco\Larasuite\Services;

use Google_Service_Directory;
use Google_Service_Directory_User;
use Google_Service_Directory_UserName;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService extends Service
{
    protected $customerId;

    protected $firstName;

    protected $lastName;

    protected $email;

    protected $orgUnitPath;

    protected $suspended;

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

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId)
    {
        $this->customerId = $customerId;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getOrgUnitPath()
    {
        return $this->orgUnitPath;
    }

    public function setOrgUnitPath(string $orgUnitPath)
    {
        $this->orgUnitPath = $orgUnitPath;
    }

    public function getSuspended()
    {
        return $this->suspended;
    }

    public function setSuspended(bool $suspended)
    {
        $this->suspended = $suspended;
    }

    public function fetch($email)
    {
        $user = $this->service->users->get($email);

        $this->setCustomerId($user->getCustomerId());
        $this->setFirstName($user->getName()->getGivenName());
        $this->setLastName($user->getName()->getFamilyName());
        $this->setEmail($user->getPrimaryEmail());
        $this->setSuspended($user->getSuspended());

        return $this;
    }

    public function get(array $optionalParams = null)
    {
        $response = $this->service->users->listUsers($optionalParams);
        $arr = [];
        foreach ($response['users'] as $user) {
            $self = new self;
            $self->setCustomerId($user->getCustomerId());
            $self->setCustomerId($user->getCustomerId());
            $self->setFirstName($user->getName()->getGivenName());
            $self->setLastName($user->getName()->getFamilyName());
            $self->setEmail($user->getPrimaryEmail());
            $self->setSuspended($user->getSuspended());

            $arr[] = $self;
        }

        return $arr;
    }

    public function create(array $input)
    {
        try {
            Validator::make(
                $input,
                [
                    'email'     => 'required|email',
                    'firstName' => 'required|min:3',
                    'lastName'  => 'required|min:3',
                    'password'  => 'required|min:8',
                ]
            )->validate();

            $this->setFirstName($input['firstName']);
            $this->setLastName($input['lastName']);
            $this->setEmail($input['email']);
            $this->setOrgUnitPath($input['orgUnitPath'] ?? '/');
            $this->setSuspended($input['suspended'] ?? false);


            $nameInstance = new Google_Service_Directory_UserName;
            $nameInstance->setGivenName($this->getFirstName());
            $nameInstance->setFamilyName($this->getLastName());

            $userInstance = new Google_Service_Directory_User;
            $userInstance->setName($nameInstance);
            $userInstance->setPrimaryEmail($this->getEmail());
            $userInstance->setOrgUnitPath($this->getOrgUnitPath());
            $userInstance->setHashFunction('MD5');
            $userInstance->setPassword(hash("md5", $input['password']));

            $this->service->users->insert($userInstance);

            return $this;
        } catch (ValidationException $v) {
            $this->handleException($v);
        } catch (\Google_Service_Exception $gse) {
            throw new \Exception('already exists a count with the email '. $input['email'], $gse->getCode());
        }
    }

    public function update(string $email, array $input)
    {
        try {
            $this->fetch($email);

            $input = [
                'firstName'     => $input['firstName'] ?? $this->getFirstName(),
                'lastName'      => $input['lastName']   ?? $this->getLastName(),
                'orgUnitPath'   => $input['orgUnitPath'] ?? $this->getOrgUnitPath(),
            ];

            Validator::make(
                $input,
                [
                    'firstName'  => 'min:3',
                    'lastName'   => 'min:3',
                ]
            );

            $this->setFirstName($input['firstName']);
            $this->setLastName($input['lastName']);
            $this->setOrgUnitPath($input['orgUnitPath'] ?? "/");

            $user = $this->service->users->get($this->getEmail());

            $name = $user->getName();
            $name->setGivenName($this->getFirstName());
            $name->setFamilyName($this->getLastName());
            $name->setFamilyName($this->getLastName());
            $user->setName($name);

            $user->setOrgUnitPath($this->getOrgUnitPath());

            $this->service->users->update($email, $user);

            return $this;
        } catch (ValidationException $v) {
            $this->handleException($v);
        } catch (\Throwable $th) {
            throw new \Exception('Error to update account : '. $this->getEmail(), $th->getCode());
        }
    }

    public function setPassword(string $email, string $password)
    {
        try {
            Validator::make(['password' => $password ], [ 'password' => 'required|min:8' ]);

            $this->fetch($email);
            $user = $this->service->users->get($email);
            $user->setHashFunction('MD5');
            $user->setPassword(hash("md5", $password));

            $this->service->users->update($email, $user);

            return $this;
        } catch (ValidationException $v) {
            throw new \Exception($v->getResponse(), $v->getCode());
        }
    }

    public function activate(string $email)
    {
        $this->fetch($email);

        if (! $this->getSuspended()) {
            return $this;
        }

        $this->setSuspended(false);
        $user = $this->service->users->get($email);
        $user->setSuspended($this->getSuspended());
        $this->service->users->update($email, $user);

        return $this;
    }

    public function suspend(string $email)
    {
        $this->fetch($email);

        if ($this->getSuspended()) {
            return $this;
        }

        $this->setSuspended(true);

        $user = $this->service->users->get($email);
        $user->setSuspended($this->getSuspended());
        $this->service->users->update($email, $user);

        return $this;
    }

    public function destroy(string $email)
    {
        $this->fetch($email);
        $this->service->users->delete($email);

        return;
    }

    protected function handleException(ValidationException $v)
    {
        $errors = print_r($v->errors(), true);

        throw new \Exception($errors, $v->getCode());
    }
}
