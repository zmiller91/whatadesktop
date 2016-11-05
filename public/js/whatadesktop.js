define([
    'carousel',
],

  function(carousel){
      
    // Create the base module for the page
    var wad = angular.module('whatadesktop', ['ngRoute']);
    
    // Init the controllers, directives, and services for all the components
    // on the page
    carousel.init(wad);


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

            .otherwise({
                    redirectTo: '/queue/random'
            });

        $locationProvider.html5Mode(true);
    });
    
    // Bootstrap the page
    angular.bootstrap(document, ['whatadesktop']);
});