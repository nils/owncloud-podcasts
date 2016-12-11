<?php

/**
 * Copyright (c) 2016 David Prandzioch
 * https://github.com/dprandzioch/owncloud-podcasts.
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
style('podcasts', 'default');
vendor_script('podcasts', 'angular/angular.min');
vendor_script('podcasts', 'angular-sanitize/angular-sanitize.min');
vendor_script('podcasts', 'videogular/videogular');
vendor_script('podcasts', 'videogular-controls/vg-controls');
vendor_script('podcasts', 'videogular-buffering/vg-buffering');
vendor_script('podcasts', 'videogular-poster/vg-poster');
script('podcasts', 'podcasts');

?>

<div class="podcasts--list">
    <img src="<?php print_unescaped(\OCP\Template::image_path('podcasts', 'loading.gif')); ?>" ng-show="loading" />

    <div class="list--item" ng-repeat="episode in episodes" ng-show="filteredFeedId == null || episode.feed_id == filteredFeedId">
        <div class="item--cover-container">
            <img src="{{episode.cover}}" ng-show="episode.cover != null && episode.cover != ''" class="cover-container--cover" ng-click="list.select(episode)" ng-class="{'is--active' : list.isSelected(episode)}" ng-dblclick="list.openPlayer(episode)" />
            <img src="<?php print_unescaped(\OCP\Template::image_path('podcasts', 'nocover.jpg')); ?>" srcset="<?php print_unescaped(\OCP\Template::image_path('podcasts', 'nocover.jpg')); ?> 1x, <?php print_unescaped(\OCP\Template::image_path('podcasts', 'nocover@2x.jpg')); ?> 2x" ng-show="episode.cover == null || episode.cover == ''" class="cover-container--cover" ng-click="list.select(episode)" ng-class="{'is--active' : list.isSelected(episode)}" ng-dblclick="list.openPlayer(episode)" />
            <i class="cover-container--icon cover-container--icon-new icon-info-white" ng-show="episode.duration == 0 && episode.played == 0"></i>
            <i class="cover-container--icon cover-container--icon-playing icon-play" ng-show="episode.duration > 0 && episode.played == 0"></i>
        </div>
        <div class="item--description">
            {{episode.name}}
        </div>
    </div>
</div>