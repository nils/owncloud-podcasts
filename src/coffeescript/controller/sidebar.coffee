###
Copyright (c) 2016 David Prandzioch
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

class SidebarController

  @$inject: [ "$scope", "FeedService", "BroadcastService" ]
  constructor: ($scope, FeedService, BroadcastService) ->
    @scope = $scope
    @feedService = FeedService
    @broadcastService = BroadcastService

    @scope.filteredFeed = null
    @scope.feedUrl = ""
    @scope.loading = false

    @scope.$on "feedDataChanged", (e) => @onFeedDataChanged(e)

    @loadFeeds()

  onFeedDataChanged: ->
    @loadFeeds()

  getRoot: ($start) ->
    return $start.closest('#app-navigation')

  filter: (ev, feed) ->
    @_onClickItem(ev)
    if !feed || @isSelected(feed)
      @scope.filteredFeed = null
      @broadcastService.announceFeedFilterChanged(null)
    else
      @scope.filteredFeed = feed
      @broadcastService.announceFeedFilterChanged(feed.id)

  isSelected: (selection) ->
    @scope.filteredFeed == selection

  subscribeFeed: ->
    @scope.loading = yes
    @feedService.subscribe(@scope.feedUrl).then (response) =>
      @scope.loading = no
      @scope.feedUrl = ""
      @loadFeeds()
      @broadcastService.announceEpisodeDataChanged()
    , (error) ->
      alert "Could not subcribe to the feed"

  unsubscribeFeed: (id) ->
    if confirm "Do you really want to unsubscribe the selected feed?"
      @scope.loading = yes
      @feedService.unsubscribe(id).then (response) =>
        @scope.loading = no
        @loadFeeds()
        @broadcastService.announceEpisodeDataChanged()
      , (error) ->
        alert "Could not unsubcribe the feed"

  loadFeeds: () ->
    @scope.loading = yes
    @feedService.all().then (response) =>
      @scope.feeds = response.data.data
      @scope.loading = no
    , (error) ->
      alert "Could not load the feeds"

  markAllPlayed: () ->
    @scope.loading = yes
    @feedService.markAllPlayed().then (response) =>
      @scope.loading = no
      @broadcastService.announceEpisodeDataChanged()
    , (error) ->
      alert "Could not mark all episodes as played"

  getActiveContainer: () ->
    return this.$currentContent;


  getActiveItem: () ->
    return this._activeItem;


  setActiveItem: (itemId, $target, options) ->
    oldItemId = this._activeItem;
    if itemId == this._activeItem
      if (!options || !options.silent)
        @getRoot($target).trigger(
          new $.Event('itemChanged', {itemId: itemId, previousItemId: oldItemId})
        );

      return;

    @getRoot($target).find('li').removeClass('active');
    if this.$currentContent
      this.$currentContent.addClass('hidden');
      this.$currentContent.trigger(jQuery.Event('hide'));

    this._activeItem = itemId;
    @getRoot($target).find('li[data-id=' + itemId + ']').addClass('active');
    this.$currentContent = $('#app-content-' + itemId);
    $('[id^=app-content-]').addClass('hidden');
    this.$currentContent.removeClass('hidden');
    if !options || !options.silent
      this.$currentContent.trigger(jQuery.Event('show'));
      @getRoot($target).trigger(
        new $.Event('itemChanged', {itemId: itemId, previousItemId: oldItemId})
      );



  itemExists: (itemId) ->
    return this.$el.find('li[data-id=' + itemId + ']').length;

  _onClickItem: (ev) ->
    $target = $(ev.target);
    itemId = $target.closest('li').attr('data-activate-item');
    this.setActiveItem(itemId, $target);
    ev.preventDefault();


angular.module("Podcasts").controller "SidebarController", SidebarController
