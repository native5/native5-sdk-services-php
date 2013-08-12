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

use Native5\Api\ApiClient;

/**
 * JobManager 
 * 
 * @category  Jobs 
 * @package   Native5\Services\Jobs
 * @author    Shamik Datta <shamik@native5.com>
 * @copyright 2012 Native5. All Rights Reserved 
 * @license   See attached NOTICE.md for details
 * @version   Release: 1.0 
 * @link      http://www.docs.native5.com 
 */
class JobManager extends ApiClient {
    const API_PATH = 'job/';
    const CREATE_JOB = 'create/';
    const GET_JOB = '';
    const UPDATE_JOB_STATE = 'state/';
    const UPDATE_JOB_STATUS = 'status/';
    const SET_JOB_RESULT = 'result/';
    const DELETE_JOB = 'delete/';

    private $_logger;

    public function __construct() {
        parent::__construct();
        $this->_logger = $GLOBALS['logger'];
    }

    /**
     * createJob Create an entry for a long running job
     * 
     * @param string $serviceName 
     * @access public
     * @return boolean true if successful, false otherwise
     *
     * @assert (null) throws Exception
     * @assert (array()) throws Exception
     * @assert ("testingJob") != null
     */
    public function createJob($serviceName) {
        if (!($_serviceName = $this->_checkString($serviceName)))
            throw new \Exception("Invalid non-string or non-numeric service name: ".print_r($serviceName, true));

        $path = self::API_PATH.self::CREATE_JOB;
        $request = $this->_remoteServer->post($path)
            ->setPostField('name', $_serviceName);

        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $this->_logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }
 
        // Long running job ID
        $respBody = $response->getBody(true);
        $GLOBALS['logger']->info("Created job with id: ".$respBody, array());

        return $respBody;
    }

    public function deleteJob($jobId) {
        $jobId = $this->_processJobId($jobId);
        $path = self::API_PATH.$jobId.'/'.self::DELETE_JOB;
        
        $request = $this->_remoteServer->post($path);
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $this->_logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }
 
        // True/False
        $result = $this->_convertToBoolean($response->getBody(true));
        $GLOBALS['logger']->info("Delete status job for id [ $jobId ]: ".$result, array());

        return $result;
    }

    public function updateJobState($jobId, $state) {
        // Check for a valid string state
        if (!($state = $this->_checkString($state)))
            throw \Exception("Invalid non-string or non-numeric state string: ".print_r($state, true));

        $jobId = $this->_processJobId($jobId);
        $path = self::API_PATH.$jobId.'/'.self::UPDATE_JOB_STATE;
        
        $res = $this->_sendPut($path, 'state', $state);
        // True/False
        $GLOBALS['logger']->info("Updated Job State: ".($res ? "True" : "False"), array());

        return $res;
    }

    public function updateJobStatus($jobId, $status) {
        // Check for a valid status constant
        if (($status !== Job::STATUS_CREATED) && ($status !== Job::STATUS_RUNNING) && 
                ($status !== Job::STATUS_COMPLETED_SUCCESS ) && ($status !== Job::STATUS_COMPLETED_ERROR))
            throw new \Exception("Status should be one of the STATUS_ constants defined in the Job class - received: ".print_r($status, true));

        $jobId = $this->_processJobId($jobId);
        $path = self::API_PATH.$jobId.'/'.self::UPDATE_JOB_STATUS;
        
        $res = $this->_sendPut($path, 'status', $status);
        // True/False
        $GLOBALS['logger']->info("Updated Job Status: ".($res ? "True" : "False"), array());

        return $res;
    }

    public function setJobResult($jobId, $result) {
        $jobId = $this->_processJobId($jobId);
        $path = self::API_PATH.$jobId.'/'.self::SET_JOB_RESULT;
        
        $res = $this->_sendPut($path, 'result', json_encode($result));
        // True/False
        $GLOBALS['logger']->info("Set Job Result: ".($res ? "True" : "False"), array());

        return $res;
    }

    /**
     * getJob Get details of a long running job entry
     * 
     * @param string $jobId The job Id
     *
     * @access public
     * @return @see JobStatus 
     *
     * @assert (null) throws Exception
     * @assert (array()) throws Exception
     */
    public function getJob($jobId)
    {
        $jobId = $this->_processJobId($jobId);
        $path = self::API_PATH.$jobId.'/'.self::GET_JOB;

        $request =  $this->_remoteServer->get($path);
        $request->getQuery();
        try {
            $response = $request->send();
        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $this->_logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }
 
        return $this->_buildJob($response->json());
    }

    private function _buildJob ($jobData) {
        if (empty($jobData) || !is_array($jobData) || !isset($jobData['SERVICE']) || !isset($jobData['STATUS']))
            return false;

        $job = new Job();
        $job->setServiceName($jobData['SERVICE']);
        $job->setStatus($jobData['STATUS']);
        $job->setState(( !empty($jobData['STATE']) ? $jobData['STATE'] : null ));
        $job->setResult(( !empty($jobData['RESULT']) ? json_decode($jobData['RESULT'], true) : null ));

        return $job;
    }

    private function _sendPut ($apiPath, $key, $val) {
        $request = $this->_remoteServer->put($apiPath)
            ->setPostField($key, $val);
        try {
            $response = $request->send();

        } catch(\Guzzle\Http\Exception\BadResponseException $e) {
            $this->_logger->info($e->getResponse()->getBody('true'), array());
            return false;
        }
 
        return $this->_convertToBoolean($response->getBody(true));
    }

    private function _processJobId($jobId) {
        if (!($_jobId = $this->_checkString($jobId)))
            throw new \Exception("Invalid non-string or non-numeric job id: ".$jobId);

        return $_jobId;
    }

    private function _checkString($item) {
        if (!is_string($item) && !is_numeric($item))
            return false;
            
        $item = "$item";
        return $item;
    }

    private function _convertToBoolean($item) {
        if (strcmp($item, 'true') === 0)
            return true;
        else
            return false;
    }
}

