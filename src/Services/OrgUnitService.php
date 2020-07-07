<?php

namespace oeleco\Larasuite\Services;

use Exception;
use Google_Service_Directory;
use Illuminate\Support\Collection;
use Google_Service_Directory_OrgUnit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrgUnitService extends Service
{
    protected $customerId;

    protected $id;

    protected $name;

    protected $parentPath;

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
            Google_Service_Directory::ADMIN_DIRECTORY_ORGUNIT,
            Google_Service_Directory::ADMIN_DIRECTORY_ORGUNIT_READONLY,
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

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getParentPath()
    {
        return $this->parentPath;
    }

    public function setParentPath(string $id)
    {
        $this->parentPath = $id;
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
        $orgUnit = $this->service->orgunits->get($this->getCustomerId(), $id);

        $this->setId($orgUnit->getOrgUnitId());
        $this->setName($orgUnit->getName());
        $this->setParentPath($orgUnit->getParentOrgUnitPath());
        $this->setDescription($orgUnit->getDescription() ?? '');

        return $this;
    }


    public function get()
    {
        $orgUnits = new Collection($this->service->orgunits->listOrgunits($this->getCustomerId()));

        return $orgUnits->map(function ($orgUnit) {
            $this->setId($orgUnit->getOrgUnitId());
            $this->setName($orgUnit->getName());
            $this->setParentPath($orgUnit->getParentOrgUnitPath());
            $this->setDescription($orgUnit->getDescription() ?? '');

            return $this;
        });
    }

    public function create(array $input)
    {
        try {
            Validator::make(
                $input,
                [
                    'name'        => 'required|min:3',
                    'description' => 'nullable|min:3|max:255',
                    'parentPath'  => 'nullable'
                ]
            );

            $this->setName($input['name']);
            $this->setParentPath($input['parentPath'] ?? '/');
            $this->setDescription($input['description'] ?? '');

            $orgUnitInstance = new Google_Service_Directory_OrgUnit;
            $orgUnitInstance->setName($this->getName());
            $orgUnitInstance->setParentOrgUnitPath($this->getParentPath());
            $orgUnitInstance->getDescription($this->getDescription());

            $orgUnitCreated = $this->service->orgunits->insert($this->getCustomerId(), $orgUnitInstance);
            $this->setId($orgUnitCreated->getOrgUnitId());

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
                    'name'        => 'required|min:3',
                    'description' =>  'nullable|min:3|max:255',
                ]
            );

            $this->fetch($id);
            $this->setName($input['name']);
            $this->setDescription($input['description'] ?? '');

            $orgUnit = $this->service->orgunits->get($this->getCustomerId(), $this->getId());
            $orgUnit->setName($this->getName());
            $orgUnit->setDescription($this->getDescription());

            $this->service->orgunits->update($this->getCustomerId(), $this->getId(), $orgUnit);

            return $this;
        } catch (ValidationException $v) {
            $this->handleException($v);
        }
    }

    public function destroy(string $id)
    {
        $this->fetch($id);
        $this->service->orgunits->delete($this->getCustomerId(), $this->getId());

        return;
    }

    protected function handleException(ValidationException $v)
    {
        $errors = print_r($v->errors(), true);

        throw new Exception($errors, $v->getCode());
    }
}
