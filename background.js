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
    
    $scope.queue = true;
    $scope.queueIndex = 0;
    $scope.enlarged = false;
    $scope.saved = false;
    $scope.deleted = false;
        
    function changeImage(){
        
        $scope.image = $scope.objects[$scope.queueIndex][0];
        $scope.resolutions = $scope.objects[$scope.queueIndex];
        
        $scope.image.saved = 
                ($scope.image.saved === true ||$scope.image.saved == 1) 
                ? true 
                : false;
        $scope.image.deleted = 
                ($scope.image.deleted === true || $scope.image.deleted == 1)
                ? true 
                : false;
    }
    
    //activate queue view
    $scope.queueView = function(){
        $scope.queue = true;
        $scope.deleted = false;
        $scope.saved = false;
        $scope.enlarged = false;
    };
    
    //activate saved view
    $scope.savedView = function(){
        $scope.saved = true;
        $scope.deleted = false;
        $scope.queue = false;
        $scope.enlarged = false;
    };
    
    //activate deleted view
    $scope.deletedView = function(){
        $scope.deleted = true;
        $scope.saved = false;
        $scope.queue = false;
        $scope.enlarged = false;
    };
    
    //exit enlarged view
    $scope.exitEnlarged = function(){
        $scope.enlarged = false; 
        $scope.queue = false;
    };
    
    //go to next image
    $scope.next = function(){
        $scope.queueIndex = $scope.queueIndex + 1 >= $scope.objects.length ?
            0:
            $scope.queueIndex + 1;
        changeImage();
    };
    
    //go to previous image
    $scope.previous = function(){
        $scope.queueIndex = $scope.queueIndex - 1 >= 0 ?
            $scope.queueIndex - 1:
            $scope.objects.length - 1;
        changeImage();
    };
    
    function removeFromQueue(){
        $scope.objects.splice($scope.queueIndex, 1);
        $scope.queueIndex = $scope.queueIndex >= $scope.objects.length ?
            0 :
            $scope.queueIndex;
        changeImage();
    }
    
    //delete image
    $scope.delete = function(){
        
        var root = $scope.image.root;
        if($scope.enlarged){
            $scope.exitEnlarged();
        }
        removeFromQueue();
        $http.get('delete.php?root=' + root).
            then(function(response) {}, 
            function(response) {
              console.log(response);
        });
    };
    
    //save image
    $scope.save = function(){
        
        var root = $scope.image['root'];
        if($scope.enlarged){
            $scope.exitEnlarged();
            removeFromQueue();
        }
        
        $http.get('save.php?root=' + root).
            then(function(response) {
                $scope.image.saved = true;
                $scope.image.deleted = false;
                $scope.objects[0]['saved'] = true;
                $scope.objects[0]['deleted'] = false;
            }, 
            function(response) {
              console.log(response);
        });
    };
    
    $scope.requeue = function(){
        var root = $scope.image['root'];
        $scope.exitEnlarged();
        removeFromQueue();
        
        $http.get('requeue.php?root=' + root).
            then(function(response) {
            }, 
            function(response) {
              console.log(response);
        });
    }
    
    //enlarge thumbnail
    $scope.enlarge = function(id){
        $scope.queueIndex = id;
        changeImage();
        $scope.queue = true;
        $scope.enlarged = true;
    };
    
    //get backgrounds from db
    $scope.getBackgrounds = function(params){
        
        //should have a loading wheel here...
        $scope.objects = [];
        $scope.image = [];
        $http.get('background.php', {params: params}).
                
            then(function(response) {
             $scope.objects = response['data'];
              if($scope.objects.length > 0){
                  changeImage();
              }
                
            }, function(response) {
                console.log(response);
                return null;
            });
    };

    //should probably be on window.ready
    $scope.getBackgrounds({sort: 'random'});
  
  
});

app.directive('resize', function ($window) {
    return function (scope, element) {
        scope.colsize = 1;
        scope.rows = [];
        scope.tileInfo = {};
        scope.tileWidth = 320;
        scope.tileHeight = 180;
        scope.numTileCols = 0;
        
        scope.getImageDimensions = function(img, width, height){
        
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
        
        scope.setDimensions = function(img){
            var winDim = scope.getWindowDimensions();
            var imgDim = scope.getImageDimensions(img, winDim.w, winDim.h);
            scope.width = imgDim.w;
            scope.height = imgDim.h;
        };
        
        scope.setTileDimensions = function(objects){
            var dim = scope.getWindowDimensions();
            var room = Math.floor(dim.w / 350);
            var numcols = Math.min(room, 12);
            
            
            while(12 % numcols !== 0){
                numcols -= 1;
            }
            scope.numTileCols = numcols;
            scope.colsize = 12 / numcols;
            var tiles = [];
            var row = [];

            for(var i = 1; i <= objects.length; i++){
                
                var img = objects[i - 1][0];
                var rowHeight = scope.tileHeight;
                var colWidth = scope.tileWidth;
                var imgDim = scope.getImageDimensions(img, colWidth, rowHeight);
                img['tileWidth'] = imgDim.w;
                img['tileHeight'] = imgDim.h;
                img['id'] = i - 1;

                row.push(img);
                if(i % numcols === 0){
                    tiles.push(row);
                    row = [];
                }
            }

            if(row.length > 0){
                tiles.push(row);
                row = [];
            }

            scope.colsize = 12 / numcols;
            scope.rows = tiles;
        };
        
        
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
            scope.setTileDimensions(scope.objects);

        }, true);

        w.bind('resize', function () {
            scope.$apply();
        });
        
        //Adjusts the image to fit the screen without overflowing and without
        //losing aspect ratio
        scope.$watchCollection("image", scope.setDimensions);
        
        scope.$watchCollection("objects", scope.setTileDimensions);
    };
});