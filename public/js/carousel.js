define([
    "../lib/js-common/Carousel"
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
                
                getWindowDimensions = function () {
                    return {
                        'h': window.innerHeight,
                        'w': window.innerWidth
                    };
                };
                
                getImageDimensions = function(img, width, height){
        
                    //get the dimensions
                    var window = {w: width, h: height};
                    var iImgWidth = parseInt(img.width);
                    var iImgHeight = parseInt(img.height);

                    //gotta do this twice to ensure both dimensions are in view
                    //this helps when h > 0 and w < 0 or the other way around
                    for (i = 0; i < 2; i++) {

                        //get the differences and aspect ratio
                        var iWDiff = window.w - iImgWidth;
                        var iHDiff = window.h - iImgHeight;
                        var iRatio = iImgWidth / iImgHeight;

                        //but only try twice if one of the dimensions is larger
                        //than the screen
                        if(i === 1 && (iWDiff > 0 || iHDiff > 0)){
                            break;
                        }

                        //solve for height
                        if (Math.abs(iWDiff) > Math.abs(iHDiff)){
                            var w = iImgWidth + iWDiff;
                            var h = iImgHeight + iWDiff * 1/iRatio;
                            iImgWidth = w;
                            iImgHeight = h;

                        //solve for width
                        }else{
                            var w = iImgWidth + iHDiff * iRatio;
                            var h = iImgHeight + iHDiff;
                            iImgHeight = h;
                            iImgWidth = w;
                        }
                    }

                    return {w: iImgWidth, h: iImgHeight};
                };

                setDimensions = function(img){
                    var winDim = getWindowDimensions();
                    var imgDim = getImageDimensions(img, winDim.w, winDim.h);
                    $scope.width = imgDim.w;
                    $scope.height = imgDim.h;
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