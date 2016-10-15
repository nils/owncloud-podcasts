<?php

/**
 * Copyright (c) 2016 David Prandzioch
 * https://github.com/dprandzioch/owncloud-podcasts
 *
 * This file is part of owncloud-podcasts.
 *
 * owncloud-podcasts is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Podcasts\Controller;


use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\ApiController;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http;
use \OCA\Podcasts\Db\Gpodder;
use \OCA\Podcasts\Db\GpodderMapper;
use \OCA\Podcasts\Db\EpisodeMapper;
use \OCA\Podcasts\Db\SyncLogMapper;
use \OCA\Podcasts\Db\SyncLog;
use \OCA\Podcasts\Db\SyncLogEntryMapper;
use \OCA\Podcasts\Db\SyncLogEntry;
use \OCA\Podcasts\Db\FeedMapper;
use \OCA\Podcasts\Db\Feed;
use \OCA\Podcasts\Feed\FeedUpdater;
use \mygpo\ApiRequest;

/**
 * Class GpodderApiController
 *
 * @package OCA\Podcasts\Controller
 */
class GpodderApiController extends ApiController
{

    /**
     * @var string
     */
    protected $userId;

    protected $userSession;

    /**
     * @var GpodderMapper
     */
    protected $gpodderMapper;

    /**
     * @var IRequest
     */
    protected $request;

    protected $feedMapper;
    protected $feedUpdater;
    protected $episodeMapper;
    protected $syncLogMapper;
    protected $syncLogEntryMapper;

    /**
     * GpodderApiController constructor.
     *
     * @param string        $appName
     * @param IRequest      $request
     * @param string        $userId
     * @param IUser        $user
     * @param GpodderMapper $mapper
     */
    public function __construct(
        $appName,
        IRequest $request,
        $userId,
        IUserSession $user,
        GpodderMapper $mapper,
        FeedMapper $feedMapper,
        FeedUpdater $feedUpdater,
        EpisodeMapper $episodeMapper,
        SyncLogMapper $syncLogMapper,
        SyncLogEntryMapper $syncLogEntryMapper
    ) {
        parent::__construct($appName, $request);

        $this->userId = $userId;
        $this->userSession = $user;
        $this->request = $request;
        $this->gpodderMapper = $mapper;
        $this->feedMapper = $feedMapper;
        $this->feedUpdater = $feedUpdater;
        $this->episodeMapper = $episodeMapper;
        $this->syncLogMapper = $syncLogMapper;
        $this->syncLogEntryMapper = $syncLogEntryMapper;
    }

    /**
     * TODO
     *
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function getCredentials()
    {
          $credentials = $this->gpodderMapper->getCurrentUserCredentials($this->userId);

          $lastLogEntries = $this->syncLogEntryMapper->getLogEntries($this->userId, "gpodder");

          return new JSONResponse([
              "currentLogin" => $credentials,
              "lastSyncLog" => $lastLogEntries
          ]);
    }


    /**
     * Returns all feeds for the current user
     *
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function testCredentials()
    {
        $password = $this->request->getParam("password");
        $username = $this->request->getParam("username");
        try {
          $apiRequest = new \mygpo\ApiRequest($username, $password);
          $res = $apiRequest->listDevices($username);

          return new JSONResponse([
              "success" => true
          ]);

        } catch(\Requests_Exception $e) {
          return new JSONResponse([
              "errorMessage"    => $e->getMessage(),
              "success" => false
          ], \OCP\AppFramework\Http::STATUS_BAD_REQUEST);
        }
    }

    /**
     * Updates gpodder.net credentials
     *
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function removeCredentials()
    {
      $credentials = $this->gpodderMapper->getCurrentUserCredentials($this->userId);
      $this->gpodderMapper->delete($credentials);

      $this->syncLogMapper->deleteAllForProvider($this->userId, "gpodder");
      $this->syncLogEntryMapper->deleteAllForProvider($this->userId, "gpodder");

      return new JSONResponse([
          "currentLogin" => NULL,
          "lastSyncLog" => []
      ]);
    }

    /**
     * Updates gpodder.net credentials
     *
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function updateCredentials()
    {
      $password = $this->request->getParam("password");
      $username = $this->request->getParam("username");
      $credentials = $this->gpodderMapper->getCurrentUserCredentials($this->userId);
      $credentials->setUsername($username);
      $credentials->setPassword($password);

      $lastLogEntries = $this->syncLogEntryMapper->getLogEntries($this->userId, "gpodder");

      try {
        $apiRequest = new \mygpo\ApiRequest($username, $password);
        $res = $apiRequest->listDevices($username);

        $credentials->setLastSuccess(time());
        $credentials->setLastSynchronization(0);
        $this->gpodderMapper->update($credentials);
        return new JSONResponse([
            "currentLogin" => $credentials,
            "loginAttempt" => [
              "username" => $credentials->username,
              "password" => $credentials->password,
              "success" => true
            ],
            "lastSyncLog" => $lastLogEntries
        ]);

      } catch(\Requests_Exception $e) {
        $credentials->setLastFailure(time());
        $this->gpodderMapper->update($credentials);
        return new JSONResponse([
            "currentLogin" => $this->gpodderMapper->getCurrentUserCredentials($this->userId),
            "loginAttempt" => [
              "username" => $credentials->username,
              "password" => $credentials->password,
              "success" => false,
              "errorMessage" => $e->getMessage()
            ],
            "lastSyncLog" => $lastLogEntries
        ], \OCP\AppFramework\Http::STATUS_BAD_REQUEST);
      }
    }

    public function getSynchronizationStatus()
    {
      $credentials = $this->gpodderMapper->getCurrentUserCredentials($this->userId);
      return new JSONResponse([
          "currentSynchronizationStatus" => $this->gpodderMapper->getCurrentUserCredentials($this->userId)
      ]);
    }


        protected function syncFeeds($delta, $log) {
          //var_dump($delta);
          $logEntries = [];

          $existingFeeds = $this->feedMapper->getFeeds($this->userId);
          $existingFeedUrls = [];

          foreach($existingFeeds as $existingFeed) {
            if(in_array($existingFeed->getUrl(), $delta->rem)) {
              $this->feedMapper->delete($existingFeed);
              $this->episodeMapper->deleteByFeedId($existingFeed->getId(), $this->userId);
              $log->createLogEntry("ERROR", "Feed_Removed", $existingFeed->getId());
            } else {
               $existingFeedUrls[$existingFeed->getUrl()] = $existingFeed;
            }
          }

          foreach($delta->add as $feedToBeAdded) {
            $existingFeed = $existingFeedUrls[$feedToBeAdded->url];
            if($existingFeed) {
              $log->createLogEntry("INFO", "Feed_Already_Known", $existingFeed->getId());
              continue;
            }

            $feed = new Feed();
            $feed->setUid($this->userId);

            $feed->setName($feedToBeAdded->title);
            $feed->setCover($feedToBeAdded->logo_url);
            $feed->setUrl($feedToBeAdded->url);


            $feed = $this->feedMapper->insert($feed);
            if (true === $this->feedUpdater->checkFeed($feed)) {
              try {


                $this->feedUpdater->processFeed($feed);
                $feed = $this->feedMapper->update($feed);
                $log->createLogEntry("INFO", "Feed_Added", $feed->getId());
              } catch(\Exception $e) {
                $log->createLogEntry("ERROR", "Feed_Added_Error", $feed->getId());
              }
            } else {
              $log->createLogEntry("ERROR", "Feed_Added_Error", $feed->getId());
            }
          }
        }

    public function synchronize()
    {
      $credentials = $this->gpodderMapper->getCurrentUserCredentials($this->userId);
      $existingFeeds = $this->feedMapper->getFeeds($this->userId);

      $log = new SyncLog($this->userId, "gpodder");

      try {
        $apiRequest = new \mygpo\ApiRequest($credentials->username, $credentials->password);
        $devicename =  $this->userSession->getUser()->getDisplayName().".owncloud2.".\OCP\Util::getServerHostName();
        $devicecaption =  $this->userSession->getUser()->getDisplayName()."@owncloud.".\OCP\Util::getServerHostName();
        $setupDevice = false;

        try {
          // check if device exists

          $delta = $apiRequest->deviceUpdates($credentials->username, $devicename, $credentials->getLastSynchronization());
          $logEntries = $this->syncFeeds($delta, $log);
        } catch(\Requests_Exception_HTTP_404 $e) {
          $setupDevice = true;
        }

        $feedurls = [];
        $existingFeeds = $this->feedMapper->getFeeds($this->userId);
        foreach($existingFeeds as $feed) {
          $feedurls[] = $feed->getUrl();
        }
        $res = $apiRequest->addRemoveSubscriptions($credentials->username, $devicename, $feedurls, array());
        if($setupDevice) {
          $apiRequest->renameDevice($credentials->username, $devicename, $devicecaption, \mygpo\Device::SERVER);
        }

        $credentials->setLastSynchronization($res->timestamp);
        $credentials->setLastSuccess(time());

        $this->gpodderMapper->update($credentials);

        $this->syncLogMapper->deleteAllForProvider($this->userId, "gpodder");
        $this->syncLogEntryMapper->deleteAllForProvider($this->userId, "gpodder");

        $this->syncLogMapper->insert($log);
        $this->syncLogEntryMapper->save($log->getLogEntries());

        flush();
        return new JSONResponse([
            "currentLogin" => $this->gpodderMapper->getCurrentUserCredentials($this->userId),
            "lastSyncLog" => $log->getLogEntries()
        ]);
      } catch(\Requests_Exception_HTTP_401 $e) {
        $log->addException($e);
        flush();

        $credentials->setLastFailure(time());
        $this->gpodderMapper->update($credentials);

        $this->syncLogMapper->deleteAllForProvider($this->userId, "gpodder");
        $this->syncLogEntryMapper->deleteAllForProvider($this->userId, "gpodder");

        $this->syncLogMapper->insert($log);
        $this->syncLogEntryMapper->save($log->getLogEntries());

        return new JSONResponse([
            "currentLogin" => $this->gpodderMapper->getCurrentUserCredentials($this->userId),
            "lastSyncLog" => $log->getLogEntries()
        ], \OCP\AppFramework\Http::STATUS_UNAUTHORIZED);
      } catch(\Requests_Exception $e) {
        $log->addException($e);
        flush();

        $this->gpodderMapper->update($credentials);

        $this->syncLogMapper->deleteAllForProvider($this->userId, "gpodder");
        $this->syncLogEntryMapper->deleteAllForProvider($this->userId, "gpodder");

        $this->syncLogMapper->insert($log);
        $this->syncLogEntryMapper->save($log->getLogEntries());

        return new JSONResponse([
            "currentLogin" => $this->gpodderMapper->getCurrentUserCredentials($this->userId),
            "lastSyncLog" => $log->getLogEntries()
        ], \OCP\AppFramework\Http::STATUS_BAD_REQUEST);
      }
    }
}