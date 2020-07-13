<?php

namespace oeleco\Larasuite\Services;

use Exception;
use Google_Service_Directory;
use Google_Service_Directory_Group;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GroupService extends Service
{
    protected $customerId;

    protected $id;

    protected $email;

    protected $name;

    protected $description;

    public function __construct()
    {
        parent::__construct();

        $user = (new UserService)->fetch($this->getImpersonateUser());
        $this->setCustomerId($user->getCustomerId());
    }

    public function getServiceSpecificScopes(): array
    {
        return [
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP_READONLY,
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

    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function fetch(string $id)
    {
        $group = $this->service->groups->get($id);

        $this->setId($group->getId());
        $this->setEmail($group->getEmail());
        $this->setName($group->getName());
        $this->setDescription($group->getDescription() ?? '');

        return $this;
    }

    public function get()
    {
        $response = $this->service->orgunits->list($this->getCustomerId());
        $arr = [];
        foreach ($response['groups'] as $group) {
            $arr[] = (new self)->fetch($group->getId());
        }

        return $arr;
    }

    public function create(array $input)
    {
        try {
            Validator::make(
                $input,
                [
                    'email'       => 'required|email',
                    'name'        => 'required|min:3',
                    'description' => 'nullable|min:3|max:255',
                ]
            );

            $this->setName($input['name']);
            $this->setEmail($input['email']);
            $this->setDescription($input['description'] ?? '');

            $groupInstance = new Google_Service_Directory_Group;
            $groupInstance->setName($this->getName());
            $groupInstance->setEmail($this->getEmail());
            $groupInstance->getDescription($this->getDescription());

            $groupCreated = $this->service->groups->insert($groupInstance);

            $this->setId($groupCreated->getId());

            return $this;
        } catch (ValidationException $v) {
            $this->handleException($v);
        }
    }

    public function update(string $id, array $input)
    {
        try {
            Validator::make(
                $input,
                [
                    'email'       => 'email',
                    'name'        => 'min:3',
                    'description' => 'min:3|max:255',
                ]
            );

            $this->fetch($id);
            $this->setName($input['name'] ?? $this->getName());
            $this->setEmail($input['email'] ?? $this->getEmail());
            $this->setDescription($input['description'] ?? $this->getDescription());

            $group = $this->service->groups->get($this->getId());
            $group->setName($this->getName());
            $group->setEmail($this->getEmail());
            $group->setDescription($this->getDescription());

            $this->service->groups->update($this->getId(), $group);

            return $this;
        } catch (ValidationException $v) {
            $this->handleException($v);
        }
    }

    public function destroy(string $id)
    {
        $this->fetch($id);
        $this->service->groups->delete($this->getId());

        return;
    }

    protected function handleException(ValidationException $v)
    {
        $errors = print_r($v->errors(), true);

        throw new Exception($errors, $v->getCode());
    }
}
