define([
    "../lib/js-common/Carousel",
    "js/utilities.js"
], function(){return{init: function(app) {
    
    // Controller for the image/:id route
    app.controller("NavCtrl", function(CarouselData, User, $scope, $location, $route, $rootScope)
    {
        $scope.display = false;
        $scope.user = User;
        $scope.carousel = CarouselData.carousel;
        $scope.current = $scope.carousel.current();
        $scope.currentLink = "";
        $scope.saved = false;
        $scope.deleted = false;
        $scope.tileView = $location.path() === "/saved" || 
                $location.path === "/removed";

        $scope.goto = function(path)
        {
            // $location.path is a noop when the path doesnt change
            $location.path(path);
            if($location.path() === path)
            {
                $route.reload();
            }
        };
        
        $scope.setStatus = function(status)
        {
            var root = CarouselData.carousel.currentKey();
            CarouselData.setImageStatus(root, status);
            if(status === "deleted")
            {
                CarouselData.carousel.deleteKey(root);
            }
        };
        
        $scope.download = function(index)
        {
            var a = document.createElement('a');
            document.body.appendChild(a);
            a.setAttribute("type", "hidden");
            a.href = $scope.current[index].path;
            a.download = $scope.current[index].root;
            a.target = "_blank";
            a.click();
        };
        
        $scope.copyLink = function() {
            document.getElementById("image-link").select();
            document.execCommand("copy");
        }
        
        $scope.help = function() {
            $rootScope.$broadcast('nav:help', null);
        }

        $scope.$on('user:updated', function(event, data) {
            $scope.user = User;
        });
        
        $scope.$on('user:loggedout', function(event, data) {
            $scope.goto("/queue/random");
        });

        $scope.$on('download', function(event, data) {
            $scope.download(0);
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
                $scope.current = $scope.carousel.current();
                if($scope.current && $scope.current.length) {
                    $scope.currentLink = "http://www.whatadesktop.com/image/" + 
                            $scope.current[0].root;
                }
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
                    if($scope.current.length)
                    {
                        $scope.saved = $scope.current[0]["status"] === "1";
                        $scope.deleted = $scope.current[0]["status"] === "-1";
                    }
                }
            }
        );
    });
}};});