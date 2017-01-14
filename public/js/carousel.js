define([
    "../lib/js-common/Carousel",
    "js/utilities.js"
], function(){return{init: function(app) {
    
    // Controller for the image/:id route
    app.controller("ImageCtrl", function(CarouselData, $routeParams)
    {
            CarouselData.carousel.reset();
            CarouselData.getImage($routeParams.id, function(){}, function(){}); 
    });

    // Controller for the queue/:sort route
    app.controller("QueueCtrl", function(CarouselData, $routeParams)
    {
        CarouselData.carousel.reset();
        CarouselData.getQueue($routeParams.sort, function(){}, function(){});        
    });
    
    // Main carousel controller
    app.controller("CarouselCtrl", function (CarouselData, $scope, $route, $rootScope) 
    {
        // Mark the view as visible if the current path is in the list below
        var currentPath = $route.current.$$route.originalPath;
        var showOnPaths = [
            "/queue/:sort",
            "/image/:id",
            "/expand/:view",
            "/expand/:view/:id"
        ];
        
        $scope.visible  = showOnPaths.indexOf(currentPath) > -1;
        
        var winDim = getWindowSize();
        $scope.windowWidth = winDim.w;
        $scope.windowHeight = winDim.h;
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
        };
        
        // Enable hotkeys so a user can navigate the carousel without using a
        // mouse.  Allow the user to scroll left and right as well as save or
        // remove images
        $scope.keypress = function(keyEvent)
        {
            switch(keyEvent.keyCode) 
            {
                case 38: // Up Arrow
                case 87: // W
                    CarouselData.setImageStatus($scope.carousel.currentKey(), "saved"); 
                    break;

                case 40: // Down Array
                case 83:// S
                    CarouselData.setImageStatus($scope.carousel.currentKey(), "deleted");
                    $scope.carousel.deleteCurrent();
                    break;

                case 39: // Right Key
                case 68: // D
                    $scope.carousel.next();
                    break;

                case 37: // Let Key
                case 65: // A
                    $scope.carousel.previous();
                    break;  
                
                case 82: // R
                    $route.reload();
                    break;
                    
                case 67: // C
                    $rootScope.$broadcast('download', null);
                    break;
            }
        };
        
        // Fetch user's root images, if a user is logged in
        $scope.$on('user:loggedin', function(event, data) {
            if(data["loggedIn"])
            {
                CarouselData.getUserRoots();
            }
        });

        // Watch for changes in the carousel, if the current image changes then
        // update the carousel view
        $scope.$watch(
            function($scope)
            {
                return $scope.carousel.currentKey();
            },
            $scope.update
        );

        // Focus on the carousel object to enable hotkeys 
        $( "#carousel" ).focus();

    });

    // Main service for the carousel. This service holds the Carousel data
    // object and is responsible for retrieving carousel data from the server
    app.service('CarouselData', ['$rootScope', '$http', function($rootScope, $http) 
    {
        var $this = this;
        this.data = {};
        this.response = {};
        this.userRoots = [];
        
        this.carousel = new Carousel();
        this._preloadedKeys = {};

        // GET request to queue/:sort
        this.getQueue =  function(type, success, error) 
        {
            $this.get('/api/queue', {sort: type}, success, error);
        };

        // GET request to image/:id
        this.getImage =  function(image, success, error) 
        {
            $this.get('/api/image', {id: image}, success, error);
        };
        
        // Generic GET request. Should only be used for carousel data.
        this.get = function(url, params, success, error)
        {
            $http.get(url, {params:params})
            .then(function(response) 
            {
                $this.response = response;
                $this.data = response.data;
                for(var k in $this.data)
                {
                    $this.carousel.add(k, $this.data[k]);
                }
                
                $this.preload();
                if(success){success($this.data);};
            }, 
            function(response) 
            {
                if(error){error(response);};
            });
        };
        
        this.setImageStatus = function(root, status, success, error)
        {
            $http.put("/api/imagestatus", {root: root, status: status})
            .then(function(response) 
            {
                // Iterate over every saved or deleted root
                var root = response.data.root;
                if($this.carousel.contains(root))
                {
                    var images = $this.carousel.get(root);
                    var status = response.data.status;
                    var status = status === "saved" ? "1" :
                            status === "deleted" ? "-1" : "0";
                    
                    for(var i in images)
                    {
                        images[i]["status"] = status;
                    }
                    
                    $this.carousel.add(root, images);
                }
                
                var data = response.data;
                if(success){success($this.data);}
            }, 
            function(response) 
            {
                if(error){error(response);}
            });
        }
        
        // Get the user's list of saved and deleted roots, update the carousel
        // images accordingly
        this.getUserRoots = function(success, error)
        {
            $http.get("/api/imagestatus")
            .then(function(response) 
            {
                // Iterate over every saved or deleted root
                $this.userRoots = response.data;
                for(var r in $this.userRoots)
                {
                    // If a saved or deleted root exists in the carousel, then
                    // update its status
                    var root = response.data[r];
                    var images = $this.carousel.get(root["root"]);
                    if(images)
                    {
                        for(var i in images)
                        {
                            images[i]["status"] = root["status"];
                        }

                        $this.carousel.add(root["root"], images);
                    }
                }
                
                if(success)
                {
                    success($this.data);
                }
            }, 
            function(response) 
            {
                if(error)
                {
                    error(response);
                }
            });
        };
        
        // Method for preloading the next and previous images
        this.preload = function()
        {
            var keys = $this.carousel.preview(-5)
                    .concat($this.carousel.preview(6));
            
            for(var k in keys)
            {
                k = keys[k];
                if(!(k in $this._preloadedKeys))
                {
                    var url = $this.carousel.get(k)[0]["path"];
                    $this.load(k, url);
                    
                }
            }
        };
        
        this.load = function(key, url)
        {
            var image = new Image();
            image.src = url;
            $this._preloadedKeys[key] = true;
        };
        
        // Watch for changes in the current image, preload the next images 
        // if there are any changes
        $rootScope.$watch(
            function(scope)
            {
                return $this.carousel.currentKey();
            }, $this.preload);
        
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
        return function (scope) 
        {
            scope.$watch(getWindowSize, scope.update, true);
            angular.element($window).bind('resize', 
                function()
                {
                    if(scope.current !== null)
                    {
                        var winDims = getWindowSize();
                        var clamped = clamp(scope.current, scope.current.width,
                            scope.current.height, winDims.w, winDims.h);

                        scope.width = clamped.width;
                        scope.height = clamped.height;
                        scope.windowWidth = winDims.w;
                        scope.windowHeight = winDims.h;

                        scope.$digest();
                    }
                }
            );
        };
    });
}};});