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
<div ng-app="Podcasts" class="app--container">
    <div id="app-navigation" class="app--navigation" ng-controller="SidebarController as sidebar">
        <ul id="navigation-list" class="with-icon">
          <li data-id="feeds" data-activate-item="feeds" <?php p($item['id']) ?>" class="nav-<?php p($item['id']) ?>">
            <a href="<?php p(isset($item['href']) ? $item['href'] : '#') ?>"
              class="nav-icon-rss svg" ng-click="sidebar.filter($event, null)">
              <?php p($l->t('All Podcasts')); ?>
            </a>
          </li>


            <li class="navigation--feed" data-activate-item="feeds" ng-repeat="feed in feeds">
                <a href="#" class="feed--item" ng-click="sidebar.filter($event, feed)" ng-class="{'is--active' : sidebar.isSelected(feed)}">{{feed.name}}</a>
                <div class="app-navigation-entry-utils">
                    <button class="feed--delete-button icon-delete" title="<?php p($l->t('Delete')); ?>"
                            ng-click="sidebar.unsubscribeFeed(feed.id)"></button>
                </div>
            </li>

            <li class="navigation--add-new" data-activate-item="feeds">
                <div class="add-new--container">
                    <form class="add-feed" data-url="<?php echo $_['add_url'] ?>">
                        <input type="text" class="add-feed--input" ng-model="feedUrl"
                               placeholder="<?php p($l->t('Add Podcast Feed URL')); ?>"/>
                        <button class="add-feed--button" ng-click="sidebar.subscribeFeed()"
                                title="<?php p($l->t('Add Feed')); ?>">
                                <span ng-show="loading == false"><?php p($l->t('Add')); ?></span>
                                <img class="navigation--loading-indicator" ng-show="loading"
                                     src="<?php print_unescaped(\OCP\Template::image_path('podcasts', 'loading.gif')); ?>"/>
                        </button>
                    </form>
                </div>
            </li>
        </ul>

        <div id="app-settings">
            <div id="app-settings-header">
              <button class="settings-button" data-apps-slide-toggle="#app-settings-content">
                <?php p($l->t('Settings')); ?>
              </button>
            </div>
            <div id="app-settings-content">
              <button class="settings--mark-played" ng-click="sidebar.markAllPlayed()">
                <?php p($l->t('Mark all as played')); ?>
              </button>
              <ul id="navigation-list" class="with-icon">
              <li data-id="gpoddersettings" data-activate-item="gpoddersettings">
                <a class="nav-icon-gpoddersettings svg"  ng-click="sidebar._onClickItem($event)">
                  <?php p($l->t('gpodder.net Integration')); ?> </a>
                </a>
              </li>
            </ul>
          </div>
        </div>
    </div>
    <div id="app-content" class="">
      <div id="app-content-feeds" ng-controller="EpisodeListController as list"  class="viewcontainer">
        <?php $_['content']->printPage(); ?>
      </div>
      <div id="app-content-gpoddersettings" ng-controller="GpodderController as gpodderSettings"  class="hidden viewcontainer app-content-gpoddersettings">
        <?php $_['gpoddersettings']->printPage(); ?>
      </div>
    </div>

</div>
