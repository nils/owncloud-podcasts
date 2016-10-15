###
Copyright (c) 2016 Nils Schnabel
https://github.com/dprandzioch/owncloud-podcasts

This file is part of owncloud-podcasts.

owncloud-podcasts is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
###

class GpodderController

  @$inject: [ "$scope", "GpodderService", "BroadcastService"]
  constructor: ($scope, GpodderService, BroadcastService) ->
    @scope = $scope
    @gpodderService = GpodderService
    @broadcastService = BroadcastService
    that = @
    @gpodderService.all().then(
      (response) =>
        @scope.gpodder = @postprocess(response.data)
        @scope.gpodder.loginAttempt = jQuery.extend({}, response.data.currentLogin)
      (response) =>
        @scope.gpodder = @postprocess(response.data)
        @scope.gpodder.loginAttempt = jQuery.extend({}, response.data.currentLogin)
    )

  putCredentials: () ->
    @scope.gpodder.credentialsChecking = yes
    that = @
    @gpodderService.putCredentials(@scope.gpodder.loginAttempt.username, @scope.gpodder.loginAttempt.password).then(
      (response) =>
        @scope.gpodder = @postprocess(response.data)
      (response) =>
        @scope.gpodder = @postprocess(response.data)
    )
  deleteCredentials: () ->
    @scope.gpodder.credentialsDeleting = yes
    that = @
    @gpodderService.deleteCredentials().then(
      (response) =>
        @scope.gpodder = @postprocess(response.data)
        @scope.gpodder.loginAttempt = jQuery.extend({}, response.data.currentLogin)
      (response) =>
        @scope.gpodder = @postprocess(response.data)
        @scope.gpodder.loginAttempt = jQuery.extend({}, response.data.currentLogin)
    )
  synchronize: () ->
    @scope.gpodder.synchronizing = yes
    that = @
    @gpodderService.synchronize().then(
      (response) =>
        @broadcastService.announceFeedDataChanged()
        @scope.gpodder = @postprocess(response.data)
        @scope.gpodder.loginAttempt = jQuery.extend({}, response.data.currentLogin)
      (response) =>
        @broadcastService.announceFeedDataChanged()
        @scope.gpodder = @postprocess(response.data)
        @scope.gpodder.loginAttempt = jQuery.extend({}, response.data.currentLogin)
    )
  postprocess: (data) ->
    if data && data.currentLogin
      data.currentLogin.lastSynchronizationRelative = OC.Util.relativeModifiedDate(new Date(parseInt(data.currentLogin.lastSynchronization, 10)*1000))

    return data


angular.module("Podcasts").controller "GpodderController", GpodderController
