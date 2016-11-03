define([
    "../lib/js-common/Carousel"
], function() {
    return {
        init: function(app) {
            
            app.controller("CarouselCtrl", function (CarouselData, $scope, $routeParams, $location) {
                
                $scope.id = $routeParams.id;
                
                if($scope.id != null)
                {
                    if(CarouselData.carousel.contains($scope.id))
                    {
                        CarouselData.carousel.seekTo($scope.id);
                    }
                    else
                    {
                        CarouselData.carousel.add($scope.id, $scope.id)
                    }
                }
                else
                {
                    CarouselData.carousel.add("1", "1");
                    CarouselData.carousel.add("2", "2");
                    CarouselData.carousel.add("3", "3");
                    CarouselData.carousel.add("4", "4");
                    
                    CarouselData.get(function(data){
                        console.log(data);
                    }, function(){});
                    
                }
                
                $scope.current = CarouselData.carousel.current();
                $scope.id = CarouselData.carousel.currentKey();
                $location.path('/Image(' + $scope.id + ")");
                $scope.key = "key";
                $scope.value = "value";
                
                $scope.next = function()
                {
                    $scope.current = CarouselData.carousel.next();
                    $scope.id = CarouselData.carousel.currentKey();
                    $location.path('/Image(' + $scope.id + ")");
                }
                
                $scope.previous = function()
                {
                    $scope.current = CarouselData.carousel.previous();
                    $scope.id = CarouselData.carousel.currentKey();
                    $location.path('/Image(' + $scope.id + ")");
                }
                
                $scope.deleteCurrent = function()
                {
                    CarouselData.carousel.deleteCurrent();
                    $scope.current = CarouselData.carousel.current();
                    $scope.id = CarouselData.carousel.currentKey();
                    $location.path($scope.id != null ? "/Image(" + $scope.id + ")" : "/");
                }
                
                $scope.delete = function()
                {
                    CarouselData.carousel.deleteKey(this.key);
                    $scope.current = CarouselData.carousel.current();
                    $scope.id = CarouselData.carousel.currentKey();
                    $location.path($scope.id != null ? "/Image(" + $scope.id + ")" : "/");
                }
                
                $scope.add = function()
                {
                    CarouselData.carousel.add(this.key, this.value);
                    $scope.current = CarouselData.carousel.current();
                    $scope.id = CarouselData.carousel.currentKey();
                    $location.path('/Image(' + $scope.id + ")");
                }
                
            })

            .directive("carousel", function() {
              return {
                templateUrl: 'html/carousel.html'
              };
            })

            .service('CarouselData', ['$rootScope', '$http', function($rootScope, $http) {
                this.carousel = new Carousel();
                this.response = {};
                this.data = {};
                
                this.get =  function(success,error) {
                    $http.get('/api/image', {params:{method: "notifications"}})
                        .then(function(response) {
                            this.response = response;
                            this.data = response.data;
                            success(this.data);
                        }, function(response) {
                            error(response);
                        }
                    );
                };
            }]);
        }
    };
});