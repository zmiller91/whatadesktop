var app = angular.module('myApp', ['ui.bootstrap']);
app.controller('myCtrl', function($scope, $http, $uibModal) {
    
    $scope.objects = [];
    $scope.image = {
        path: "../lib/img/header.jpg",
        width: window.innerWidth,
        height: window.innerHeight
    };
    $scope.width = 0;
    $scope.height = 0;
    $scope.resolutions = [];
    
    $scope.queue = true;
    $scope.queueIndex = 0;
    $scope.enlarged = true;
    $scope.user = {};
    $scope.view = 'queue';
    $scope.loaded = false;
        
    $scope.register = function () {

        var modalInstance = $uibModal.open({
            templateUrl: 'RegistrationModal',
            controller: 'RegistrationCtrl',
            size: 'sm'
        });

        modalInstance.result.then(function (oUser) {
            $scope.user = oUser;
        }, function (error) {console.log(error)});
    };    
    
    $scope.login = function () {

        var modalInstance = $uibModal.open({
            templateUrl: 'LoginModal',
            controller: 'LoginCtrl',
            size: 'sm'
        });

        modalInstance.result.then(function (oUser) {
            $scope.user = oUser;
        }, function (error) {console.log(error)});
    };
    
    $scope.logout = function(){
        var oData = {user: $scope.user, method: 'logout'};
        $http.post('login.php', oData)
            .then(function(response) {
                $scope.user = response['data']['user'];
                $scope.getBackgrounds({sort: 'random'});
                $scope.queueView();
            }, function(error) {
        });
    }
        
    //should probably put a watch on queueindex
    function changeImage(){
        $scope.image = $scope.objects[$scope.queueIndex][0];
        $scope.resolutions = $scope.objects[$scope.queueIndex];
        
        switch($scope.image.status){
            
            case '1':
                $scope.image.status = 1;
                break;
            case '0':
                $scope.image.status = 0;
                break;
            case '-1':
                $scope.image.status = -1;
                break;
        }
    }
    
    $scope.changeView = function(strPageView, bEnlarged){
        $scope.view = strPageView;
        $scope.enlarged = bEnlarged;
    };
    
    //exit enlarged view
    $scope.exitEnlarged = function(){
        $scope.enlarged = false;
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
        if($scope.objects.length > 1){
            changeImage();
        }
    }
    
    //delete image
    $scope.delete = function(){
        
        var root = $scope.image.root;
        if($scope.enlarged && $scope.view === 'user_imgs'){
            $scope.exitEnlarged();
        }
        removeFromQueue();
        var oData = {method: 'set_status', root: root, status: -1};
        $http.post('api.php', oData).
            then(function(response) {
                $scope.user = $scope.user = response['data']['user'];
            }, 
            function(response) {
              console.log(response);
        });
    };
    
    //save image
    $scope.save = function(){
        
        var root = $scope.image['root'];
        if($scope.enlarged && $scope.view === 'user_imgs'){
            $scope.exitEnlarged();
            removeFromQueue();
        }
        
        var oData = {method: 'set_status', root: root, status: 1};
        $http.post('api.php', oData).
            then(function(response) {
                $scope.user = $scope.user = response['data']['user'];
                $scope.image.status = 1;
                $scope.objects[0]['status'] = 1;
            }, 
            function(response) {
              console.log(response);
        });
    };
    
    $scope.requeue = function(){
        var root = $scope.image['root'];
        $scope.exitEnlarged();
        removeFromQueue();
        
        var oData = {method: 'requeue', root: root};
        $http.post('api.php', oData).
            then(function(response) {
                $scope.user = $scope.user = response['data']['user'];
            }, 
            function(response) {
              console.log(response);
        });
    }
    
    //enlarge thumbnail
    $scope.enlarge = function(id){
        $scope.queueIndex = id;
        changeImage();
        $scope.enlarged = true;
    };
    
    //get backgrounds from db
    $scope.getBackgrounds = function(params){
        
        //should have a loading wheel here...
        params.method = 'sort';
        $scope.objects = [];
        $scope.image = [];
        $http.get('api.php', {params: params}).
                
            then(function(response) {
                $scope.loaded = true;
                $scope.queueIndex = 0;
                $scope.objects = response['data']['images'];
                $scope.user = $scope.user = response['data']['user'];
                if($scope.objects.length > 0){
                    changeImage();
                } 
                
            }, function(response) {
                $scope.loaded = true;
                console.log(response);
                return null;
            });
    };

    //should probably be on window.ready
    $scope.getBackgrounds({sort: 'random'});
});

app.controller('LoginCtrl', function ($scope, $modalInstance, $http) {

    $scope.user = {name: "", pass: ""};
    $scope.submit = function(){
        
        var oData = {user: $scope.user, method: 'login'};
        $http.post('php/user/login.php', oData)
            .then(function(response) {
                $modalInstance.close(response['data']);
            }, function(response) {
                $modalInstance.dismiss(response);
                return null;
        });
    };
});

app.controller('RegistrationCtrl', function ($scope, $modalInstance, $http) {

    $scope.user = {name: "", pass: "", verified_pass: ""};
    $scope.submit = function () {
        
        var oData = {user: $scope.user, method: 'register'};
        $http.post('login.php', oData)
            .then(function(response) {
                $modalInstance.close(response['data']);
            }, function(response) {
                $modalInstance.dismiss(response);
                return null;
        });
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
});

app.directive('appendHtml', function(){

  return {
    templateUrl: 'background.php'
  };
})

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