<?php
/**
 *  Copyright 2012 Native5. All Rights Reserved
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  You may not use this file except in compliance with the License.
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *  PHP version 5.3+
 *
 * @category  Messaging 
 * @package   Native5\Services\Messaging
 * @author    Barada Sahu <barry@native5.com>
 * @copyright 2012 Native5. All Rights Reserved 
 * @license   See attached LICENSE for details
 * @version   GIT: $gitid$ 
 * @link      http://www.docs.native5.com 
 */

namespace Native5\Services\Messaging;

use Native5\Services\Messaging\MessagingException;
use Native5\Services\Common\ApiClient;
use Native5\Services\Messaging\Notifier;
use Native5\Services\Messaging\Message;

/**
 * RemoteMailNotifier 
 */
class RemoteMailNotifier extends ApiClient implements Notifier {
    const MAX_ATTACHMENTS = 3;

    /**
     * notify 
     * 
     * @param Message $message Message to send
     *
     * @access public
     * @return NotificationStatus
     * @throws MessagingException
     */
    public function notify(Message $message, $options = array())
    {
        $logger = $GLOBALS['logger'];
        $logger->debug(
            'Sending Mail ',
            array(
             $message->getRecipients(),
             $message->getBody(),
            )
        );
        $path    = 'notifications/mail/send';

        // Check for number of attachments at the top
        $attachments = $message->getAttachments();
        if (count($attachments) > self::MAX_ATTACHMENTS)
            throw new \InvalidArgumentException("Can only send maximum ".self::MAX_ATTACHMENTS." attachments in a single mail.");

        // The base request
        $request = $this->_remoteServer->post($path)
            ->setPostField('type', 'sms')
            ->setPostField('subject', $message->getSubject())
            ->setPostField('to', implode(';', $message->getRecipients()))
            ->setPostField('content', $message->getBody());

        // Add options if present
        if (isset($options['priority']))
            $request->setPostField('priority', $options['priority']);
        if (isset($options['priority']))
            $request->setPostField('format', $options['format']);

        if (!empty($attachments) && is_array($attachments)) {
            foreach($attachments as $idx=>$attachment) {
                if (!empty($attachment) && file_exists($attachment))
                    // TODO: Remove HACK - sending files as attach[0], attach[1], attach[2]
                    $request->addPostFile('attach['.$idx.']', $attachment);
            }
        } else
            // Force a multipart form data by attaching /dev/null
            $request->addPostFile('nullfile', '@/dev/null');

        //// Add the duplicate aggregator for attachments
        //$request->getQuery()->setAggregator(new \Guzzle\Http\QueryAggregator\DuplicateAggregator);

        // Send the remote request
        try {
            $response = $request->send();
            if ($response->getStatusCode() !== 200) {
                throw new MessagingException();
            }

            $rawResponse = $response->getBody('true');

            return $rawResponse;
        } catch (\Exception $e) {
            throw new MessagingException();
        }

    }//end notify()


} //end class

