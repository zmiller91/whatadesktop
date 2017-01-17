define([
    '../lib/js-common/user/user',
    'carousel',
    'navigation',
    'tileview',
    'notifications',
    'filter'
],

  function(user, carousel, nav, tileview, notifications, filter){
      
    // Create the base module for the page
    var wad = angular.module('whatadesktop', ['ngRoute', 'ui.bootstrap', 'ngCookies']);
    
    // Init the controllers, directives, and services for all the components
    // on the page
    user.init(wad);
    tileview.init(wad);
    carousel.init(wad);
    nav.init(wad);
    notifications.init(wad);
    filter.init(wad);
    
    wad.config(function($routeProvider, $locationProvider) {
        $routeProvider
                  
            .when('/image/:id', {
                    templateUrl: 'html/whatadesktop.html',
                    controller: 'ImageCtrl'
            })
            
            .when('/queue/:sort', {
                    templateUrl: 'html/whatadesktop.html',
                    controller: 'QueueCtrl'
            })
            
            .when('/saved', {
                    templateUrl: 'html/whatadesktop.html',
                    controller: 'SavedCtrl'
            })
            
            .when('/removed', {
                    templateUrl: 'html/whatadesktop.html',
                    controller: 'RemovedCtrl'
            })
            
            .when('/expand/:view', {
                    templateUrl: 'html/whatadesktop.html',
                    controller: 'ExpandCtrl'
            })
            
            .when('/expand/:view/:id', {
                    templateUrl: 'html/whatadesktop.html',
                    controller: 'ExpandImgCtrl'
            })

            .otherwise({
                    redirectTo: '/queue/random'
            });

        $locationProvider.html5Mode(true);
    });
    
    // Bootstrap the page
    angular.bootstrap(document, ['whatadesktop']);
});