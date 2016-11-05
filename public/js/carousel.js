define([
    "../lib/js-common/Carousel",
    "js/utilities.js"
], function(){return{init: function(app) {

    app.controller("ImageCtrl", function(CarouselData, $routeParams)
    {
        // Get the image from the server   
        CarouselData.getImage($routeParams.id, function(){}, function(){}); 
    });

    app.controller("QueueCtrl", function(CarouselData, $routeParams)
    {
        // Get the queue from the server   
        CarouselData.getQueue($routeParams.sort, function(){}, function(){});        
    });

    app.controller("CarouselCtrl", function (CarouselData, $scope) 
    {
        $scope.carousel = CarouselData.carousel;
        $scope.current = {};

        $scope.$watch(

            // Variable to watch
            function($scope)
            {
                return $scope.carousel.currentKey();
            },

            // Update on change
            function()
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
        );
    });

    app.service('CarouselData', ['$http', function($http) 
    {
        var $this = this;
        this.carousel = new Carousel();
        this.sort = "";
        this.response = {};
        this.data = {};
        
        parseResponse = function(response)
        {
            this.response = response;
            this.data = response.data;
            for(var k in this.data)
            {
                $this.carousel.add(k, this.data[k]);
            }
        };

        this.getQueue =  function(type, success,error) 
        {
            $http.get('/api/queue', {params:{sort: type}})
            .then(function(response) 
            {
                parseResponse(response);
                success(this.data);
            }, 
            function(response) 
            {
                error(response);
            });
        };

        this.getImage =  function(image, success,error) 
        {
            $http.get('/api/image', {params:{id: image}})
            .then(function(response) 
            {
                parseResponse(response);
                success(this.data);
            }, 
            function(response) 
            {
                error(response);
            });
        };
    }]);

    app.directive("carousel", function() 
    {
      return {
        templateUrl: 'html/carousel.html'
      };
    });
}};});