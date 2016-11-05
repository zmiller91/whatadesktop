define([
    "../lib/js-common/Carousel",
    "js/utilities.js"
], function() {
    return {
        init: function(app) {
            
            app.controller("CarouselCtrl", function (CarouselData, $scope, 
                    $routeParams, $location) {
                
                $scope.id = $routeParams.id;
                $scope.current = "fuck";
                
                if($scope.id != null)
                {
                    if(CarouselData.carousel.contains($scope.id))
                    {
                        CarouselData.carousel.seekTo($scope.id);
                        $scope.id = CarouselData.carousel.currentKey();
                        $scope.current = CarouselData.carousel.current()[0];
                        setDimensions($scope.current);
                    }
                    else
                    {
                        CarouselData.carousel.add($scope.id, $scope.id)
                        $scope.id = CarouselData.carousel.currentKey();
                        $scope.current = CarouselData.carousel.current()[0];
                        setDimensions($scope.current);
                    }
                }
                else
                {   
                    CarouselData.get(function(){
                        this.update();
                    }, function(){});
                    
                }

                update = function()
                {
                    $scope.id = CarouselData.carousel.currentKey();
                    $location.path('/Image(' + $scope.id + ")");
                }

                setDimensions = function(img){
                    var winDim = getWindowSize();
                    var imgDim = clamp(img.width, img.height, winDim.w, winDim.h);
                    $scope.width = imgDim.w;
                    $scope.height = imgDim.h;
                    $scope.windowWidth = winDim.w;
                    $scope.windowHeight = winDim.h;
                };
                
                $scope.next = function()
                {
                    CarouselData.carousel.next();
                    update();
                }
                
                $scope.previous = function()
                {
                    CarouselData.carousel.previous();
                    update();
                }
                
                $scope.deleteCurrent = function()
                {
                    CarouselData.carousel.deleteCurrent();
                    update();
                }  
            })

            .directive("carousel", function() {
              return {
                templateUrl: 'html/carousel.html'
              };
            })

            .service('CarouselData', ['$rootScope', '$http', function($rootScope, $http) {
                var $this = this;
                this.carousel = new Carousel();
                this.response = {};
                this.data = {};
                this.get =  function(success,error) 
                {
                    $http.get('/api/queue', {params:{sort: "random"}})
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
                            }
                        );
                };
            }]);
        }
    };
});