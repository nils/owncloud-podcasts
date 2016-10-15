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

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

/**
 * Class EpisodeMapper
 *
 * @package OCA\Podcasts\Db
 */
class SyncLogEntryMapper extends Mapper
{

    /**
     * Constructor
     *
     * @param IDBConnection $dbConnection
     */
    public function __construct(IDBConnection $dbConnection)
    {
        parent::__construct($dbConnection, "podcasts_sync_log_entry", "\OCA\Podcasts\Db\SyncLogEntry");
    }

    public function getLogEntries($uid, $provider) {
      $result = $this->findEntities("SELECT * FROM `*PREFIX*podcasts_sync_log_entry` WHERE `uid` = ? AND  `provider` = ?", array($uid, $provider));
      return $result;
    }

    public function deleteAllForProvider($uid, $provider) {
      $this->execute(
          "DELETE FROM *PREFIX*podcasts_sync_log_entry WHERE `uid` = ? AND `provider` = ?", array($uid, $provider)
      );
    }

    /**
     * Marks all episodes for a user as played
     *
     * @param string $uid
     *
     * @return \PDOStatement
     */
    public function save($syncLogEntries)
    {
        foreach($syncLogEntries as $syncLogEntry) {
          $this->insert($syncLogEntry);
        }
    }
}
