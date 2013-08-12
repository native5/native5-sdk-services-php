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
 * @category  Jobs
 * @package   Native5\Services\Jobs
 * @author    Barada Sahu <barry@native5.com>
 * @copyright 2012 Native5. All Rights Reserved 
 * @license   See attached LICENSE for details
 * @version   GIT: $gitid$ 
 * @link      http://www.docs.native5.com 
 */

namespace Native5\Services\Job;

/**
 * JobStatus 
 * 
 * @category  Jobs 
 * @package   Native5\Services\Jobs
 * @author    Shamik Datta <shamik@native5.com>
 * @copyright 2012 Native5. All Rights Reserved 
 * @license   See attached NOTICE.md for details
 * @version   Release: 1.0 
 * @link      http://www.docs.native5.com 
 */

class Job {
    const STATUS_CREATED = 0;
    const STATUS_RUNNING = 1;
    const STATUS_COMPLETED_SUCCESS = 2;
    const STATUS_COMPLETED_ERROR = 3;

    private $_serviceName;
    private $_status;
    private $_state;
    private $_result;

    public function setServiceName($name) {
        $this->_serviceName = $name;
    }

    public function getServiceName(){
        return $this->_serviceName;
    }

    public function setStatus($status) {
        $this->_status = $status;
    }

    public function getStatus(){
        return $this->_status;
    }

    public function setState($state) {
        $this->_state = $state;
    }

    public function getState() {
        return $this->_state;
    }

    public function setResult($result) {
        $this->_result = $result;
    }

    public function getResult() {
        return $this->_result;
    }
}

