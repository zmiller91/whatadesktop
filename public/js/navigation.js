define([
    "../lib/js-common/Carousel",
    "js/utilities.js"
], function(){return{init: function(app) {
    
    // Controller for the image/:id route
    app.controller("NavCtrl", function(CarouselData, User, $scope, $location, $route)
    {
        $scope.display = false;
        $scope.user = User;
        $scope.carousel = CarouselData.carousel;
        $scope.current = $scope.carousel.current();

        $scope.goto = function(path)
        {
            // $location.path is a noop when the path doesnt change
            $location.path(path);
            if($location.path() === path)
            {
                $route.reload();
            }
        }

        $scope.$on('user:updated', function(event, data) {
            $scope.user = User;
        });
        
        
        $scope.$watch(
            function($scope)
            {
                return $scope.user.authorizationFinished;
            },
            function()
            {
                $scope.display = $scope.user.authorizationFinished;
            }
        );
        
        
        $scope.$watch(
            function($scope)
            {
                return $scope.carousel.currentKey();
            },
            function()
            {
                if($scope.carousel.currentKey())
                {
                    $scope.current = $scope.carousel.current();
                }
            }
        );


        
    });
    
}};});