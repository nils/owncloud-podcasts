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
 * Class Episode
 *
 * @package OCA\Podcasts\Db
 */
class GpodderCredentials extends Entity
{
    public function __construct() {
        // add types in constructor
        $this->addType('uid', 'text(64)');
        $this->addType('username', 'text(255)');
        $this->addType('password', 'text(255)');
        $this->addType('lastSuccess', 'timestamp');
        $this->addType('lastFailure', 'timestamp');
        $this->addType('lastSynchronization', 'timestamp');
    }

    /**
     * @var string
     */
    public $uid;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $lastSuccess;


    /**
     * @var string
     */
    public $lastFailure;

    public $lastSynchronization;
}
