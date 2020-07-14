<?php

namespace oeleco\Larasuite\Services;

use Exception;
use Google_Service_Directory;
use Google_Service_Directory_Member;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MemberService extends Service
{
    protected $email;

    protected $role;

    public function getServiceSpecificScopes(): array
    {
        return [
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP_MEMBER,
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP_MEMBER_READONLY,
        ];
    }

    public function setService()
    {
        $this->service = new Google_Service_Directory($this->client);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole(string $role)
    {
        $this->role = $role;
    }

    public function fetch(string $groupId, string $email)
    {
        $member = $this->service->members->get($groupId, $email);
        $this->setEmail($member->getEmail());
        $this->setRole($member->getRole());

        return $this;
    }

    public function get(string $groupId)
    {
        $response = $this->service->members->listMembers($groupId);
        $arr = [];
        foreach ($response['members'] as $member) {
            $self = (new self);
            $self->setEmail($member->getEmail());
            $self->setRole($member->getRole());

            $arr[] = $self;
        }

        return $arr;
    }

    public function create(string $groupId, array $input)
    {
        try {
            Validator::make(
                $input,
                [
                    'email'       => 'required|email',
                    'role'        => 'nullable|in:OWNER,MANAGER,MEMBER',
                ]
            );

            $this->setEmail($input['email']);
            $this->setRole($input['role'] ?? 'MEMBER');

            $memberInstance = new Google_Service_Directory_Member;
            $memberInstance->setRole($this->getRole());
            $memberInstance->setEmail($this->getEmail());

            $this->service->members->insert($groupId, $memberInstance);

            return $this;
        } catch (ValidationException $v) {
            $this->handleException($v);
        }
    }

    public function update(string $groupId, string $email, string $role)
    {
        try {
            Validator::make(
                ['role' => $role ],
                ['role' => 'in:OWNER,MANAGER,MEMBER']
            );

            $this->fetch($groupId, $email);
            $this->setRole($role ?? $this->getRole());

            $member = $this->service->members->get($groupId, $this->getEmail());
            $member->setRole($this->getRole());

            $this->service->members->update($groupId, $this->getEmail(), $member);

            return $this;
        } catch (ValidationException $v) {
            $this->handleException($v);
        }
    }

    public function destroy(string $groupId, string $email)
    {
        $this->fetch($groupId, $email);
        $this->service->members->delete($groupId, $this->getEmail());

        return;
    }

    protected function handleException(ValidationException $v)
    {
        $errors = print_r($v->errors(), true);

        throw new Exception($errors, $v->getCode());
    }
}
