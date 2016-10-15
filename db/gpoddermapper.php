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
class GpodderMapper extends Mapper
{

    /**
     * Constructor
     *
     * @param IDBConnection $dbConnection
     */
    public function __construct(IDBConnection $dbConnection)
    {
        parent::__construct($dbConnection, "podcasts_gpodder", "\OCA\Podcasts\Db\GpodderCredentials");
    }

    public function getCurrentUserCredentials($uid) {
      try {
        $result = $this->findEntity("SELECT * FROM `*PREFIX*podcasts_gpodder` WHERE `uid` = ?", array($uid));
      } catch(\OCP\AppFramework\Db\DoesNotExistException $e) {
        $result = new GpodderCredentials();
        $result->setUid($uid);
        $this->insert($result);
      }
      return $result;
    }

    /**
     * Marks all episodes for a user as played
     *
     * @param string $uid
     *
     * @return \PDOStatement
     */
    public function save($username, $password)
    {
        $this->execute(
            "DELETE FROM *PREFIX*podcasts_gpodder"
        );


        return $this->execute(
            "INSERT INTO *PREFIX*podcasts_gpodder (username, password) VALUES (?, ?)",
            [$username, $password]
        );
    }
}
