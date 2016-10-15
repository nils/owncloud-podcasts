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

namespace OCA\Podcasts\Db;

use OCP\AppFramework\Db\Entity;

/**
 * Class Feed
 *
 * @package OCA\Podcasts\Db
 */
class SyncLog extends Entity
{
    public function __construct($uid, $provider) {
        // add types in constructor
        $this->addType('uid', 'text(64)');
        $this->addType('provider', 'text(255)');

        $this->setUid($uid);
        $this->setProvider($provider);
    }

    /**
     * @var string
     */
    public $uid;

    /**
     * @var string
     */
    public $provider = "";

    public $logEntries = [];
    public $logEntriesLoaded = false;

    public function addException($exception) {
      $result = new SyncLogEntry();


      $result->setTimestamp(time());
      $result->setMessageType("ERROR");
      $result->setMessageKey($exception->getMessage());
      $this->logEntries[] = $result;
      return $result;
    }


    public function createLogEntry($messageType, $messageKey, $podcastId, $episodeId) {
      $result = new SyncLogEntry();


      $result->setTimestamp(time());
      $result->setMessageType($messageType);
      $result->setMessageKey($messageKey);
      $result->setPodcastId($podcastId);
      $result->setEpisodeId($episodeId);
      $this->logEntries[] = $result;
      return $result;
    }

    public function getLogEntries() {
      foreach($this->logEntries as $logEntry) {
        $logEntry->setUid($this->getUid());
        $logEntry->setProvider($this->getProvider());
        $logEntry->setLogId($this->getId());
      }
      return $this->logEntries;
    }
}
