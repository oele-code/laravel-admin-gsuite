<?php

namespace oeleco\Larasuite\Services;

use Google_Service_Directory;
use Google_Service_Directory_UserPhoto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserPhotoService extends Service
{
    public function getServiceSpecificScopes(): array
    {
        return [
            Google_Service_Directory::ADMIN_DIRECTORY_USER,
            Google_Service_Directory::ADMIN_DIRECTORY_USER_READONLY,
        ];
    }

    public function setService()
    {
        $this->service = new Google_Service_Directory($this->client);
    }

    public function fetch($email) : Google_Service_Directory_UserPhoto
    {
        try {
            $photo = $this->service->users_photos->get($email);
        } catch (\Google_Service_Exception $gse) {
            if ($gse->getCode() == 404) {
                $photo = new Google_Service_Directory_UserPhoto;
                $photo->setPrimaryEmail($email);
            }
        }

        return $photo;
    }

    public function update(string $email, array $input)
    {
        try {
            Validator::make(
                $input,
                [
                    'photoData' => 'required',
                    'height'    => 'required|min:3',
                    'width'     => 'required|min:3',
                    'mimeType'  => 'required'
                ]
            );


            $photoInstance =  $this->fetch($email);
            $photoInstance->setPhotoData($input['photoData']);
            $photoInstance->setWidth($input['width']);
            $photoInstance->setHeight($input['height']);
            $photoInstance->setMimeType($input['mimeType']);

            return $this->service->users_photos->update($photoInstance->getPrimaryEmail(), $photoInstance);
        } catch (ValidationException $v) {
            $this->handleException($v);
        }
    }


    public function destroy(string $email)
    {
        $photoInstance =  $this->fetch($email);
        $this->service->users_photos->delete($photoInstance->getPrimaryEmail());

        return;
    }

    protected function handleException(ValidationException $v)
    {
        $errors = print_r($v->errors(), true);

        throw new \Exception($errors, $v->getCode());
    }
}
