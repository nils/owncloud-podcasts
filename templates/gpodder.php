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
script('podcasts', 'podcasts');

?>
<h2><?php p($l->t('gpodder.net Configuration')); ?></h2>

<ul class="checklist">
<li ng-show="!gpodder.currentLogin.username" ><?php print_unescaped($l->t('Use your <a href="https://gpodder.net" target="_blank" rel="noreferrer">gpodder.net</a> account to synchronize your podcast subscriptions across devices.')); ?></li>
<li class="success" ng-show="gpodder.currentLogin.username &amp;&amp; gpodder.currentLogin.lastSuccess >= gpodder.currentLogin.lastFailure" >You are connected to your gpodder.net account {{gpodder.currentLogin.username}} to synchronize your podcast subscriptions across devices.</li>
<li class="errors" ng-show="gpodder.currentLogin.lastSuccess < gpodder.currentLogin.lastFailure" >OwnCloud could not connect to your gpodder.net accout <b>{{gpodder.currentLogin.username}}</b>, please update your password below if you have changed it.</li>
</ul>
<button ng-show="gpodder.currentLogin.username" ng-click="gpodderSettings.deleteCredentials()"><?php p($l->t('Stop using {{gpodder.currentLogin.username}}')); ?></button><img src="<?php print_unescaped(\OCP\Template::image_path('podcasts', 'loading.gif')); ?>" ng-show="gpodder.credentialsDeleting" />



<h3 ng-show="gpodder.currentLogin.username" ><?php p($l->t('Change your gpodder.net credentials')); ?></h3>
  <input type="text" id="gpodder-username--input" class="gpodder-username--input" ng-model="gpodder.loginAttempt.username" placeholder="<?php p($l->t('Enter gpodder.net user name')); ?>" />
  <input type="password" width="100%" class="gpodder-password--input" ng-model="gpodder.loginAttempt.password" placeholder="<?php p($l->t('Enter gpodder.net password')); ?>" />
  <button class="gpodder-username--login" ng-click="gpodderSettings.putCredentials()"><?php p($l->t('Save')); ?></button>
  <img src="<?php print_unescaped(\OCP\Template::image_path('podcasts', 'loading.gif')); ?>" ng-show="gpodder.credentialsChecking" />
  <span class="alert alert-success" ng-show="!gpodder.loginAttempt.success" ng-bind="gpodder.loginAttempt.errorMessage" ></span>

  <div>
<h3><?php p($l->t('Synchronization')); ?></h3>
<ul class="checklist">
<li class="success" ng-show="gpodder.currentLogin.lastSynchronization">Last synchronized: {{gpodder.currentLogin.lastSynchronizationRelative}}</span>
<li class="errors" ng-show="!gpodder.currentLogin.lastSynchronization">Never synchronized</span><br>
</ul>

<ul class="checklist">
<li  ng-repeat="syncLogEntry in gpodder.lastSyncLog">{{syncLogEntry.messageKey}}</li>
</ul>

<button  ng-show="gpodder.currentLogin.username &amp;&amp; gpodder.currentLogin.lastSuccess >= gpodder.currentLogin.lastFailure" ng-click="gpodderSettings.synchronize()"><?php p($l->t('Synchronize')); ?></button>
<img src="<?php print_unescaped(\OCP\Template::image_path('podcasts', 'loading.gif')); ?>" ng-show="gpodder.synchronizing" />


</div>