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
 * @category  Identity 
 * @package   Native5\<package>
 * @author    Barada Sahu <barry@native5.com>
 * @copyright 2012 Native5. All Rights Reserved 
 * @license   See attached LICENSE for details
 * @version   GIT: $gitid$ 
 * @link      http://www.docs.native5.com 
 */

namespace Native5\Services\Identity;

use Native5\Services\Common\ApiClient;
use Native5\Identity\Authenticator;
use Native5\Identity\Logout;
use Native5\Identity\AuthenticationException;
use Native5\Identity\SimpleAuthInfo;

/**
 * RemoteAuthenticationService
 * 
 * @category  Services\Identity 
 * @package   Native5\Services\Identity
 * @author    Barada Sahu <barry@native5.com>
 * @copyright 2012 Native5. All Rights Reserved 
 * @license   See attached NOTICE.md for details
 * @version   Release: 1.0 
 * @link      http://www.docs.native5.com 
 * Created : 27-11-2012
 * Last Modified : Fri Dec 21 09:11:53 2012
 */
class RemoteAuthenticationService extends ApiClient implements Authenticator, Logout
{


    /**
     * authenticate 
     * 
     * @param mixed $token The AuthenticationToken
     *
     * @access public
     * @return <code>AuthInfo</code>
     * @throws AuthenticationException
     */
    public function authenticate($token, $preventDuplicate=false)
    {
        $logger  = $GLOBALS['logger'];
        $path    = 'users/authenticate';
        $request = $this->_remoteServer->post($path);
        $request->setPostField('username', $token->getUser());
        $request->setPostField('password', $token->getPassword());
        if($preventDuplicate) {
            // Generate a hash of the session id.
            $ua = $_SERVER['HTTP_USER_AGENT'];
            $hashedSessionId = crypt(uniqid().$ua);
            $request->setPostField('session', $hashedSessionId);
        }
        try {
            $response = $request->send();
            if ($response->getStatusCode() !== 200) {
                throw new AuthenticationException();
            }

            $rawResponse = $response->json();

            $GLOBALS['logger']->info("Got authentication response: ".PHP_EOL.print_r($rawResponse, 1));
            $roles    = isset($rawResponse['roles'])?$rawResponse['roles']:array();
            $authInfo = new SimpleAuthInfo();
            if(isset($rawResponse['username']))
                $authInfo->addPrincipal(array('username'=>$rawResponse['username']));
            if(isset($rawResponse['name']))
                $authInfo->addPrincipal(array('displayName'=>$rawResponse['name']));
            if(isset($rawResponse['email']))
                $authInfo->addPrincipal(array('email'=>$rawResponse['email']));
            $authInfo->addPrincipal(array('account'=>$rawResponse['account']));

            if(!empty($rawResponse['aliases'])) {
                foreach ($rawResponse['aliases'] as $k=>$v) {
                    $authInfo->addPrincipal(array($k=>$v));
                }
            }
            $tokens = isset($rawResponse['token'])?$rawResponse['token']: array();

            if($preventDuplicate) {
                return array($authInfo, $roles, $tokens, $hashedSessionId);
            } else {
                return array($authInfo, $roles, $tokens);
            }
        } catch (\Exception $e) {
            throw new AuthenticationException();
        }

    }//end authenticate()


    /**
     * onAccess
     * 
     * @param mixed $principal Principals to logout.
     *
     * @access public
     * @return void
     */
    public function onAccess($sessionHash)
    {
        global $logger;
        $path    = 'users/access';
        $request = $this->_remoteServer->post($path)
            ->setPostField('session', $sessionHash);
        try {
            $response = $request->send();
        } catch(\guzzle\http\exception\badresponseexception $e) {
            $logger->info($e->getresponse()->getbody('true'), array());
            return false;
        }

        return $response->getbody('true');
    }//end onAccess()


    /**
     * logout 
     * 
     * @param mixed $principal Principals to logout.
     *
     * @access public
     * @return void
     */
    public function onLogout($principal, $sessionHash=null)
    {
        $path    = 'users/logout';
        $request = $this->_remoteServer->post($path);
        $request->setPostField('session', $sessionHash); 
        $request->send();
    }//end onLogout()

}//end class

