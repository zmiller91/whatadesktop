define([
    "../lib/js-common/Carousel"
], function() {
    return {
        init: function(app) {
            
            app.controller("CarouselCtrl", function ($scope) {

                var carousel = new Carousel();
                carousel.add("1", "1");
                carousel.add("2", "2");
                carousel.add("3", "3");
                carousel.add("4", "4");
                
                
                $scope.current = carousel.current();
                $scope.key = "key";
                $scope.value = "value";
                
                $scope.next = function()
                {
                    $scope.current = carousel.next();
                }
                
                $scope.previous = function()
                {
                    $scope.current = carousel.previous();
                }
                
                $scope.deleteCurrent = function()
                {
                    carousel.delete();
                    $scope.current = carousel.current();
                }
                
                $scope.delete = function()
                {
                    carousel.deleteKey(this.key);
                    $scope.current = carousel.current();
                }
                
                $scope.add = function()
                {
                    carousel.add(this.key, this.value);
                    $scope.current = carousel.current();
                }
                
            })

            .directive("carousel", function() {
              return {
                templateUrl: 'html/carousel.html'
              };
            });
        }
    };
});