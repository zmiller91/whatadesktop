define([
    "../lib/js-common/Carousel",
    "js/utilities.js"
], function(){return{init: function(app) {
    
    // Controller for the image/:id route
    app.controller("ImageCtrl", function(CarouselData, $routeParams)
    {
        CarouselData.getImage($routeParams.id, function(){}, function(){}); 
    });

    // Controller for the queue/:sort route
    app.controller("QueueCtrl", function(CarouselData, $routeParams)
    {
        CarouselData.getQueue($routeParams.sort, function(){}, function(){});        
    });

    // Main carousel controller
    app.controller("CarouselCtrl", function (CarouselData, $scope) 
    {
        $scope.carousel = CarouselData.carousel;
        $scope.current = null;
        
        // Convenience method for updating the carousel, this includes: image,
        // image dimensions, and window dimensions
        $scope.update = function()
        {
            if($scope.carousel.currentKey())
            {
                $scope.current = $scope.carousel.current()[0];
                var winDim = getWindowSize();
                var imgDim = clamp($scope.current.width, 
                        $scope.current.height, winDim.w, winDim.h);

                $scope.width = imgDim.w;
                $scope.height = imgDim.h;
                $scope.windowWidth = winDim.w;
                $scope.windowHeight = winDim.h;
            }
        }

        // Watch for changes in the carousel, if the current image changes then
        // update the carousel view
        $scope.$watch(
            function($scope)
            {
                return $scope.carousel.currentKey();
            },
            $scope.update
        );
    });

    // Main service for the carousel. This service holds the Carousel data
    // object and is responsible for retrieving carousel data from the server
    app.service('CarouselData', ['$http', function($http) 
    {
        var $this = this;
        this.carousel = new Carousel();
        this.response = {};
        this.data = {};
        
        // Generic GET request. Should only be used for carousel data.
        this.get = function(url, params, success, error)
        {
            $http.get(url, {params:params})
            .then(function(response) 
            {
                this.response = response;
                this.data = response.data;
                for(var k in this.data)
                {
                    $this.carousel.add(k, this.data[k]);
                }
                
                success(this.data);
            }, 
            function(response) 
            {
                error(response);
            }); 
        }

        // GET request to queue/:sort
        this.getQueue =  function(type, success,error) 
        {
            get('/api/queue', {sort: type});
        };

        // GET request to image/:id
        this.getImage =  function(image, success,error) 
        {
            get('/api/image', {id: image});
        };
    }]);

    // HTML template directive
    app.directive("carousel", function() 
    {
      return {
        templateUrl: 'html/carousel.html'
      };
    });
    
    // Resize directive. Will update the carousel view whenever the window
    // resizes
    app.directive('resize', function ($window) {
        return function (scope, element) 
        {
            scope.$watch(getWindowSize, scope.update, true);
            angular.element($window).bind('resize', scope.$apply);
        };
    });
}};});