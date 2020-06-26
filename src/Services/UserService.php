<?php

namespace oeleco\LaravelAdminGSuite\Services;

use Exception;
use Google_Service_Directory;
use Google_Service_Directory_User;
use Google_Service_Directory_UserName;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService extends Service
{
    protected $firstName;
    protected $lastName;
    protected $email;
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
        $name = $user->getName();

        $this->setFirstName($name->getGivenName());
        $this->setLastName($name->getFamilyName());
        $this->setEmail($user->getPrimaryEmail());
        $this->setSuspended($user->getSuspended());

        return $this;
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
                    'password'  => 'required|min:8'
                ]
            )->validate();

            $this->setFirstName($input['firstName']);
            $this->setLastName($input['lastName']);
            $this->setEmail($input['email']);
            $this->setSuspended($input['suspended'] ?? false);


            $nameInstance = new Google_Service_Directory_UserName;
            $nameInstance->setGivenName($this->getFirstName());
            $nameInstance->setFamilyName($this->getLastName());

            $userInstance = new Google_Service_Directory_User;
            $userInstance->setName($nameInstance);
            $userInstance->setPrimaryEmail($this->getEmail());
            $userInstance->setHashFunction('MD5');
            $userInstance->setPassword(hash("md5", $input['password']));

            $this->service->users->insert($userInstance);

            return $this;
        } catch (ValidationException $v) {
            $this->handleException($v);
        } catch (\Google_Service_Exception $gse) {
            throw new Exception('already exists a count with the email '. $input['email'], $gse->getCode());
        }
    }

    public function updateName(string $email, array $input)
    {
        try {
            Validator::make(
                $input,
                [
                    'firstName' => 'required|min:3',
                    'lastName' =>  'required|min:3',
                ]
            );

            $this->fetch($email);
            $this->setFirstName($input['firstName']);
            $this->setLastName($input['lastName']);

            $user = $this->service->users->get($this->getEmail());

            $name = $user->getName();
            $name->setGivenName($this->getFirstName());
            $name->setFamilyName($this->getLastName());

            $user->setName($name);

            $this->service->users->update($email, $user);

            return $this;
        } catch (ValidationException $v) {
            $this->handleException($v);
        } catch (\Throwable $th) {
            throw new Exception('Error to update account : '. $this->getEmail(), $th->getCode());
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
            throw new Exception($v->getResponse(), $v->getCode());
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

        throw new Exception($errors, $v->getCode());
    }
}
