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
        $scope.saved = false;
        $scope.deleted = false;

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
        
        $scope.$on('user:loggedout', function(event, data) {
            $scope.goto("/queue/random");
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
                if($scope.carousel.current() 
                        && $scope.carousel.current().length > 0)
                {
                    return $scope.carousel.current()[0]["status"];
                }
                
                return null;
            },
            function()
            {
                if($scope.carousel.currentKey())
                {
                    $scope.current = $scope.carousel.current();
                    if($scope.current.length > 0)
                    {
                        $scope.saved = $scope.current[0]["status"] === "1";
                        $scope.deleted = $scope.current[0]["status"] === "-1";
                    }
                }
            }
        );


        
    });
    
}};});