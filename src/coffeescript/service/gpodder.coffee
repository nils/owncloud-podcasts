angular.module("Podcasts").factory "GpodderService", ["$http", ($http) ->
  new class GpodderService
    all: () ->
      apiUrl = OC.generateUrl("/apps/podcasts/gpodder/credentials")
      return $http.get apiUrl

    putCredentials: (username, password) ->
      apiUrl = OC.generateUrl("/apps/podcasts/gpodder/credentials")
      return $http.put apiUrl, username: username, password: password

    deleteCredentials: () ->
      apiUrl = OC.generateUrl("/apps/podcasts/gpodder/credentials")
      return $http.delete apiUrl

    synchronize: () ->
      apiUrl = OC.generateUrl("/apps/podcasts/gpodder/synchronize")
      return $http.put apiUrl
]
