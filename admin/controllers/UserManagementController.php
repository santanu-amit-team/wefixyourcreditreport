<?php

namespace Admin\Controller;

class UserManagementController
{
    public function userList()
    {
        $userList = array(
            'webmaster',
            'offeradmin',
        );
        $users = array();
        foreach ($userList as $user) {
            $usernameFilePath = LAZER_DATA_PATH . '.users' . DS . '.' . $user;
            $passFilePath     = LAZER_DATA_PATH . '.passwords' . DS . '.' . $user;

            if (file_exists($usernameFilePath) && file_exists($passFilePath)) {
                $users[] = array(
                    'username' => trim(@file_get_contents($usernameFilePath)),
                    'password' => trim(@file_get_contents($passFilePath)),
                );
            }
        }
        return array(
            'success' => true,
            'data'    => $users,
        );
    }
}
