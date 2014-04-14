<?php
/**
 *  Copyright 2012 Native5. All Rights Reserved
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *	You may not use this file except in compliance with the License.
 *
 *	Unless required by applicable law or agreed to in writing, software
 *	distributed under the License is distributed on an "AS IS" BASIS,
 *	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *	See the License for the specific language governing permissions and
 *	limitations under the License.
 *  PHP version 5.3+
 *
 * @category  Users 
 * @package   Native5\<package>
 * @author    Barada Sahu <barry@native5.com>
 * @copyright 2012 Native5. All Rights Reserved 
 * @license   See attached LICENSE for details
 * @version   GIT: $gitid$ 
 * @link      http://www.docs.native5.com 
 */

namespace Native5\Services\Users;

use Native5\Services\Common\ApiClient;
use Native5\Services\Identity\RemoteAuthenticationService;

/**
 * DefaultUserManager 
 * 
 * @category  Users 
 * @package   Native5\Services\Users
 * @author    Barada Sahu <barry@native5.com>
 * @copyright 2012 Native5. All Rights Reserved 
 * @license   See attached NOTICE.md for details
 * @version   Release: 1.0 
 * @link      http://www.docs.native5.com 
 * Created : 27-11-2012
 * Last Modified : Fri Dec 21 09:11:53 2012
 */
class DefaultUserManager extends ApiClient implements UserManager
{
    /**
     * Authenticates the subject.
     * 
     * @param mixed $token The token to authenticate  
     *
     * @access public
     * @return void
     */
    public function authenticate($token)
    {
        $authService = new RemoteAuthenticationService();
        return $authService->authenticate($token); 
    } 


    /**
     * save 
     * 
     * @param mixed $user The user
     *
     * @access public
     * @return void
     */
    public function save($user)
    {
        $logger  = $GLOBALS['logger'];
        $path    = 'users/'.$user->getId();
        $request = $this->_remoteServer->post($path)
            ->setPostField('user', $user->serialize());
        try {
            $response = $request->send();
            return $response->getBody('true');
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }
    }



    /**
     * Updates user details 
     * 
     * @param mixed $user 
     * @access public
     * @return void
     */
    public function saveUser(\Native5\Services\Users\User $user, $updates)
    {
        $logger = $GLOBALS['logger'];
        $path    = 'users/'.$user->getId();
        $request = $this->_remoteServer->put($path, array(), json_encode($updates));
        try {
            $response = $request->send();
            return $response->getBody('true');
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }
    }
    

    /**
     * definePasswordPolicy 
     * 
     * @param mixed $policy Password policy
     *
     * @access public
     * @return void
     */
    public function definePasswordPolicy($policy)
    {
        $policy = new PasswordPolicy($policy);
        $policy->save();
        return $policy;
    }


    /**
     * applyPasswordPolicy 
     * 
     * @param mixed $policy Password policy to apply 
     * @param mixed $groups Groups on which policy must be enforced.
     *
     * @access public
     * @return void
     */
    public function applyPasswordPolicy($policy, $groups)
    {
        // TODO : Store association of groups with policies
        foreach ($groups as $group) {
            $group->applyPolicy($policy);
        }
    }

    /**
     * changePassword 
     * 
     * @param mixed $email    E-mail id of user
     * @param mixed $token    Token
     * @param mixed $password New password
     *
     * @access public
     * @return void
     * @throws \Exception Password change failed
     */
    public function changePassword($email, $token, $password)
    {
        global $logger;
        $path     = 'users/password/change';
        $request = $this->_remoteServer->post($path)
            ->setPostField('email', $email)
            ->setPostField('token', $token)
            ->setPostField('password', $password);
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }
        return true; 

    }//end changePassword()


    /**
     * verifyEmail 
     * 
     * @param mixed $email E-mail to verify.
     *
     * @access public
     * @return void
     */
    public function verifyEmail($email)
    {
        $path = 'users/verify_email';
        $request =  $this->_remoteServer->get($path);
        $request->getQuery()->set('email', $email);
        $response = $request->send();
        return $response->getBody('true');
    }


    /**
     * Verifies token given the user id & the token.
     * 
     * @param mixed $email User e-mail 
     * @param mixed $token API token
     *
     * @access public
     * @return void
     */
    public function verifyToken($email, $token)
    {
        $path = 'users/verify_token';
        $request =  $this->_remoteServer->get($path);
        $request->getQuery()->set('email', $email);
        $request->getQuery()->set('token', $token);
        $response = $request->send();
        return $response->getBody('true');

    }//end verifyToken()

    /**
     * createUser Create User for an application
     * 
     * @param mixed $username   Login username
     * @param mixed $password   Login password
     * @param mixed $name       User name
     * @param array $roles      Optional user roles as an array
     * @param array $aliases    Optional user aliases as an associative array
     * @access public
     * @return boolean          true on success, false otherwise
     */
    public function createUser(\Native5\Services\Users\User $user) {
        global $logger;
        $path     = 'users/create';
        $request = $this->_remoteServer->post($path)
            ->setPostField('username', $user->getUsername())
            ->setPostField('password', $user->getPassword())
            ->setPostField('name', $user->getName())
            ->setPostField('roles', json_encode($user->getRoles()))
            ->setPostField('aliases', json_encode($user->getAliases()));
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }

        return true; 
    }

    /**
     * getAllUsers      List all users for an application
     * 
     * @access public
     * @return array    array of user associative arrays
     */
    public function getAllUsers($count=1000, $offset=0, $searchToken=null) {
        global $logger;
        $path    = 'users';
        $request = $this->_remoteServer->get($path);
        if(!empty($searchToken)) {
            $request->getQuery()->set('searchToken', $searchToken);
        }
        $request->getQuery()->set('numUsers', $count);
        $request->getQuery()->set('offset', $offset);
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }

        return $response->json();
    }

    public function deactivateUser($username) {

        global $logger;
        $path    = 'users/deactivate';
        $request = $this->_remoteServer->post($path)
            ->setPostField('username', $username);
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }

        return $response->getBody('true');
    }

    public function activateUser($username) {
        global $logger;
        $path    = 'users/activate';
        $request = $this->_remoteServer->post($path)
            ->setPostField('username', $username);
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }

        return $response->getBody('true');
    }

    public function deleteUser($username) {
        global $logger;
        $path    = "users/delete?username=$username";
        $request = $this->_remoteServer->delete($path);
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }

        return $response->getBody('true');
    }

    public function generateUserOTP($username) {
        global $logger;
        $path    = 'users/generate_otp';
        $request = $this->_remoteServer->post($path)
            ->setPostField('username', $username);
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }

        return $response->getBody('true');
    }

    public function updateUser($username, $updates) {
        global $logger;
        $path    = "users/update/$username";
        $request = $this->_remoteServer->put($path)
            ->setPostField('updates', json_encode($updates));
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }

        return $response->getBody('true');
    }

    public function getUsersCount($searchToken=null) {
        global $logger;
        $path    = "users/count";
        $request = $this->_remoteServer->get($path);
        if(!empty($searchToken)) {
            $request->getQuery()->set('searchToken', $searchToken);
        }
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }

        return $response->getBody('true');
    }

    public function updatePassword($username, $password, $oldPassword = null) {
        global $logger;
        $path    = "users/password/update";
        $request = $this->_remoteServer->put($path)
            ->setPostField('username', $username)
            ->setPostField('password', $password);
        if(!empty($oldPassword))
            $request->getQuery()->set('oldPassword', $oldPassword);

        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }

        return $response->getBody('true');
    }
}

