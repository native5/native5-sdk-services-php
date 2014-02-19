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

/**
 * UserManager 
 * 
 * @category  Users 
 * @package   Native5\<package>
 * @author    Barada Sahu <barry@native5.com>
 * @copyright 2012 Native5. All Rights Reserved 
 * @license   See attached NOTICE.md for details
 * @version   Release: 1.0 
 * @link      http://www.docs.native5.com 
 * Created : 27-11-2012
 * Last Modified : Fri Dec 21 09:11:53 2012
 */
interface UserManager
{
    public function deactivateUser($username);
    public function activateUser($username);
    //public function getStatus($user);
    public function authenticate($subject);
    public function definePasswordPolicy($policy);
    public function applyPasswordPolicy($policy, $groups);
    public function verifyEmail($email);
    public function verifyToken($email, $token);
    public function changePassword($email, $token, $newPassword);
    public function createUser(\Native5\Services\Users\User $user);
    public function saveUser(\Native5\Services\Users\User $user, $updates);
    public function deleteUser($username);
    public function getAllUsers($count = 1000, $offset = 0, $searchToken = null);
    
}

