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
class EpisodeMapper extends Mapper
{

    /**
     * Constructor
     *
     * @param IDBConnection $dbConnection
     */
    public function __construct(IDBConnection $dbConnection)
    {
        parent::__construct($dbConnection, "podcasts_episodes");
    }

    /**
     * Marks all episodes for a user as played
     *
     * @param string $uid
     *
     * @return \PDOStatement
     */
    public function markAllAsPlayed($uid)
    {
        return $this->execute(
            "UPDATE *PREFIX*podcasts_episodes SET played = ? WHERE uid = ?",
            [true, $uid]
        );
    }

    /**
     * Checks if an episode exists
     *
     * @param string $uid
     * @param string $url
     *
     * @return bool
     */
    public function episodeExists($uid, $url)
    {
        $sql = "SELECT * FROM *PREFIX*podcasts_episodes WHERE uid = ? AND url = ? LIMIT 1";

        $stmt = $this->execute($sql, [$uid, $url]);
        $exists = count($stmt->fetchAll()) > 0;
        $stmt->closeCursor();

        return $exists;
    }

    /**
     * Gets a list of episodes for the current user (limited by feed if ID is
     * supplied)
     *
     * @param string $uid
     * @param int    $feedId
     *
     * @return array
     */
    public function getEpisodes($uid, $feedId = null)
    {
        $params = [$uid];

        $sql = "SELECT *PREFIX*podcasts_episodes.*, *PREFIX*podcasts_feeds.cover
                FROM *PREFIX*podcasts_episodes
                INNER JOIN *PREFIX*podcasts_feeds ON *PREFIX*podcasts_episodes.feed_id = *PREFIX*podcasts_feeds.id
                WHERE *PREFIX*podcasts_episodes.uid = ?";

        if (false === is_null($feedId)) {
            $sql .= " AND *PREFIX*podcasts_episodes.feed_id = ?";

            $params[] = (int)$feedId;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->execute($sql, $params);
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * Updates the playback position of an episode
     *
     * @param string $uid
     * @param int    $episodeId
     * @param int    $second
     * @param int    $duration
     *
     * @return \PDOStatement
     */
    public function updatePosition($uid, $episodeId, $second, $duration)
    {
        $episodeId = (int)$episodeId;
        $second = (int)$second;
        $duration = (int)$duration;

        return $this->execute(
            "UPDATE *PREFIX*podcasts_episodes SET current_second = ?, duration = ? WHERE id = ? AND uid = ?",
            [$second, $duration, $episodeId, $uid]
        );
    }

    /**
     * Mark episode as played / revoke played flag
     *
     * @param string $uid
     * @param int    $episodeId
     * @param bool   $status
     *
     * @return \PDOStatement
     */
    public function updatePlayedStatus($uid, $episodeId, $status)
    {
        $episodeId = (int)$episodeId;
        $status = (bool)$status;

        return $this->execute(
            "UPDATE *PREFIX*podcasts_episodes SET played = ? WHERE id = ? AND uid = ?",
            [$status, $episodeId, $uid]
        );
    }

    /**
     * Deletes all episodes that belong to a feed by it's ID
     *
     * @param int    $feedId
     * @param string $uid
     *
     * @return \PDOStatement
     */
    public function deleteByFeedId($feedId, $uid)
    {
        $feedId = (int)$feedId;

        return $this->execute(
            "DELETE FROM *PREFIX*podcasts_episodes WHERE feed_id = ? AND uid = ?",
            [$feedId, $uid]
        );
    }

    /**
     * Loads an episode by it's ID
     *
     * @param int    $episodeId
     * @param string $uid
     *
     * @throws DoesNotExistException
     *
     * @return array
     */
    public function getEpisode($episodeId, $uid)
    {
        $episodeId = (int)$episodeId;

        $sql = "SELECT * FROM *PREFIX*podcasts_episodes WHERE id = ? AND uid = ?";
        $stmt = $this->execute($sql, [$episodeId, $uid]);

        $episode = $stmt->fetch();

        if (false === $episode) {
            throw new DoesNotExistException("Episode id={$episodeId}
            uid={$uid} not found");
        }

        return $episode;
    }
}
