var app = angular.module('myApp', []);
app.controller('myCtrl', function($scope, $http) {
    
    $scope.objects = [];
    $scope.image = {
        path: "img/header.jpg",
        width: window.innerWidth,
        height: window.innerHeight
    };
    $scope.width = 0;
    $scope.height = 0;
    $scope.resolutions = [];
        
    function changeImage(){
        console.log('width: ' + window.innerWidth);
        console.log('height: ' + window.innerHeight);
        
        $scope.image = $scope.objects[0][0];
        $scope.resolutions = $scope.objects[0];
    }
    
    $scope.next = function(){
        var old = $scope.objects.shift();
        $scope.objects.push(old);
        changeImage();
    }
    
    $scope.previous = function(){
        var old = $scope.objects.pop();
        $scope.objects.unshift(old);
        changeImage();
    }
    
    $scope.delete = function(){
        var deleted = $scope.objects.shift();
        var previous = $scope.objects.pop();
        $scope.objects.unshift(previous);
        var root = deleted[0]['root'];
        changeImage();
        $http.get('delete.php?root=' + root).
            then(function(response) {}, 
            function(response) {
              console.log(response);
        });
    }
    


   $http.get('background.php').
       then(function(response) {
         // this callback will be called asynchronously
         // when the response is available
         $scope.objects = response['data'];
         if($scope.objects.length > 0){
             changeImage();
         }


       }, function(response) {
         console.log(response);
       });
  
});

app.directive('resize', function ($window) {
    return function (scope, element) {
        
        scope.setDimensions = function(img){
            
            //get the dimensions
            var window = scope.getWindowDimensions();
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
            
            scope.width = iImgWidth;
            scope.height = iImgHeight;
        }
        
        
        var w = angular.element($window);
        scope.getWindowDimensions = function () {
            return {
                'h': window.innerHeight,
                'w': window.innerWidth
            };
        };
        scope.$watch(scope.getWindowDimensions, function (newValue, oldValue) {
            scope.windowHeight = newValue.h;
            scope.windowWidth = newValue.w;

            scope.style = function () {
                return {
                    'height': (newValue.h - 100) + 'px',
                        'width': (newValue.w - 100) + 'px'
                };
            };
            
            scope.setDimensions(scope.image);

        }, true);

        w.bind('resize', function () {
            scope.$apply();
        });
        
        //Adjusts the image to fit the screen without overflowing and without
        //losing aspect ratio
        scope.$watchCollection("image", scope.setDimensions);
    }
})