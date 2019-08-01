<?php

class UsersController extends ControllerBase
{
    /**
     * Private functions
     */
    private function checkForbiddenUsername($username)
    {
        $username = trim($username);
        if ($username == 'admin') {
            $this->buildErrorResponse(409, 'common.COULD_NOT_BE_CREATED');
        }
    }

    private function checkIfUsernameAlreadyExists($username)
    {
        // checks if user already exists
        $conditions = 'username = :username:';
        $parameters = array(
            'username' => trim($username),
        );
        $user = Users::findFirst(
            array(
                $conditions,
                'bind' => $parameters,
            )
        );
        if ($user) {
            $this->buildErrorResponse(409, 'profile.ANOTHER_USER_ALREADY_REGISTERED_WITH_THIS_USERNAME');
        }
    }

    private function createUser($email, $new_password, $username, $firstname, $lastname, $level, $phone, $mobile, $address, $city, $country, $state, $latitude, $longitude, $birthday, $authorised = 0)
    {
        $user = new Users();
        $user->email = trim($email);
        $user->username = trim($username);
        $user->firstname = trim($firstname);
        $user->lastname = trim($lastname);
        $user->level = trim($level);
        $user->phone = trim($phone);
        $user->mobile = trim($mobile);
        $user->address = trim($address);
        $user->city = trim($city);
        $user->country = trim($country);
        $user->state = trim($state);
        $user->latitude = trim($latitude);
        $user->longitude = trim($longitude);
        $user->birthday = trim($birthday);
        $user->authorised = trim($authorised);
        $user->password = password_hash($new_password, PASSWORD_BCRYPT);
        $this->tryToSaveData($user, 'common.COULD_NOT_BE_CREATED');
        return $user;
    }

    private function findUserLastAccess($user)
    {
        $conditions = 'username = :username:';
        $parameters = array(
            'username' => $user['username'],
        );
        $last_access = UsersAccess::find(
            array(
                $conditions,
                'bind' => $parameters,
                'columns' => 'date, ip, domain, browser',
                'order' => 'id DESC',
                'limit' => 10,
            )
        );
        if ($last_access) {
            $array = array();
            $user_last_access = $last_access->toArray();
            foreach ($user_last_access as $key_last_access => $value_last_access) {
                $this_user_last_access = array(
                    'date' => $this->utc_to_iso8601($value_last_access['date']),
                    'ip' => $value_last_access['ip'],
                    'domain' => $value_last_access['domain'],
                    'browser' => $value_last_access['browser'],
                );
                $array[] = $this_user_last_access;
            }
            $user = empty($array) ? $this->array_push_assoc($user, 'last_access', '') : $this->array_push_assoc($user, 'last_access', $array);
            return $user;
        }
    }

    private function updateUser($user, $firstname, $lastname, $birthday, $email, $level, $phone, $mobile, $address, $city, $country, $authorised = 0)
    {
        $user->firstname = trim($firstname);
        $user->lastname = trim($lastname);
        $user->birthday = trim($birthday);
        $user->email = trim($email);
        $user->level = trim($level);
        $user->phone = trim($phone);
        $user->mobile = trim($mobile);
        $user->address = trim($address);
        $user->city = trim($city);
        $user->country = trim($country);
        $user->authorised = trim($authorised);
        $this->tryToSaveData($user, 'common.COULD_NOT_BE_UPDATED');
        return $user;
    }

    private function setNewPassword($new_password, $user)
    {
        $user->password = password_hash($this->request->getPut('new_password'), PASSWORD_BCRYPT);
        $this->tryToSaveData($user, 'common.COULD_NOT_BE_UPDATED');
    }

    /**
     * Public functions
     */
    public function index()
    {
        $this->initializeGet();
        $options = $this->buildOptions('firstname asc, lastname asc', $this->request->get('sort'), $this->request->get('order'), $this->request->get('limit'), $this->request->get('offset'));
        $filters = $this->buildFilters($this->request->get('filter'));
        $cities = $this->findElements('Users', $filters['conditions'], $filters['parameters'], 'id, firstname, lastname, level, email, phone, mobile, address, country, city, birthday, authorised', $options['order_by'], $options['offset'], $options['limit']);
        $total = $this->calculateTotalElements('Users', $filters['conditions'], $filters['parameters']);
        $data = $this->buildListingObject($cities, $options['rows'], $total);
        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }

    public function create()
    {
        $this->initializePost();
        $this->checkForEmptyData([$this->request->getPost('username'), $this->request->getPost('firstname'), $this->request->getPost('new_password'), $this->request->getPost('email')]);
        $this->checkForbiddenUsername($this->request->getPost('username'));
        $this->checkIfUsernameAlreadyExists($this->request->getPost('username'));
        $user = $this->createUser($this->request->getPost('email'), $this->request->getPost('new_password'), $this->request->getPost('username'), $this->request->getPost('firstname'), $this->request->getPost('lastname'), $this->request->getPost('level'), $this->request->getPost('phone'), $this->request->getPost('mobile'), $this->request->getPost('address'), $this->request->getPost('city'), $this->request->getPost('country'),$this->request->getPost('state'),$this->request->getPost('latitude'),$this->request->getPost('longitude'), $this->request->getPost('birthday'));
        $user = $user->toArray();
        $user = $this->unsetPropertyFromArray($user, ['password', 'block_expires', 'login_attempts']);
        $this->registerLog();
        $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $user);
    }

    public function get($id)
    {
        $this->initializeGet();
        $user = $this->findElementById('Users', $id);
        $user = $user->toArray();
        $user = $this->unsetPropertyFromArray($user, ['password', 'block_expires', 'login_attempts']);
        $user = $this->findUserLastAccess($user);
        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $user);
    }

    public function update($id)
    {
        $this->initializePatch();
        $this->checkForEmptyData([$this->request->getPut('firstname'), $this->request->getPut('authorised')]);
        $user = $this->updateUser($this->findElementById('Users', $id), $this->request->getPut('firstname'), $this->request->getPut('lastname'), $this->request->getPut('birthday'), $this->request->getPut('email'), $this->request->getPut('level'), $this->request->getPut('phone'), $this->request->getPut('mobile'), $this->request->getPut('address'), $this->request->getPut('city'), $this->request->getPut('country'), $this->request->getPut('authorised'));
        $user = $user->toArray();
        $user = $this->unsetPropertyFromArray($user, ['password', 'block_expires', 'login_attempts']);
        $this->registerLog();
        $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $user);
    }

    public function changePassword($id)
    {
        $this->initializePatch();
        $this->checkForEmptyData([$this->request->getPut('new_password')]);
        $user = $this->findElementById('Users', $id);
        $this->setNewPassword($this->request->getPut('new_password'), $user);
        $this->registerLog();
        $this->buildSuccessResponse(200, 'change-password.PASSWORD_SUCCESSFULLY_UPDATED');
    }

    public function delete($id)
    {
        $this->initializeDelete();
        if ($this->tryToDeleteData($this->findElementById('Users', $id))) {
            $this->registerLog();
            $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
        }
    }
}
