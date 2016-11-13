define([
    "../lib/js-common/Carousel"
], function(){return{init: function(app) {
    
    // Controller for the saved route
    app.controller("SavedCtrl", function(CarouselData, TileData)
    {
        TileData.getSavedGrid(CarouselData);
    });    
    
    // Controller for the removed route
    app.controller("RemovedCtrl", function(CarouselData, TileData)
    {
        TileData.getRemovedGrid(CarouselData);
    });
    
    // Controller for the expand/:view
    app.controller("ExpandCtrl", function(CarouselData, TileData, $routeParams)
    {    
        var view = $routeParams.view;
        if(view === "saved")
        {
            TileData.getSavedGrid(CarouselData);
        }
        else if(view === "removed")
        {
            TileData.getRemovedGrid(CarouselData);
        }
    });
    
    // Controller for the expand/:view/:id route
    app.controller("ExpandImgCtrl", function(CarouselData, TileData, $routeParams, $location)
    {    
        var view = $routeParams.view;
        var id = $routeParams.id;
        var callback = function()
        {
            // Look for the requested image in the queue
            if(CarouselData.carousel.contains(id))
            {
                CarouselData.carousel.seekTo(id);
            }
            else
            {
                // Something weird happened, go to random
                CarouselData.carousel.reset();
                $location.path("/queue/random");
            }
        };
        
        if(view === "saved")
        {
            if(!TileData.savedGrid.length)
            {
                TileData.getSavedGrid(CarouselData, callback);
            }
            
            callback();
        }
        else if(view === "removed")
        {
            if(!TileData.removedGrid.length)
            {
                TileData.getRemovedGrid(CarouselData, callback);
            }
            
            callback();
        }
    });
            
    // Controller for the image/:id route
    app.controller("TileCtrl", function(TileData, $scope, $route, $location)
    {
        // Mark the view as visible if the current path is in the list below
        var currentPath = $route.current.$$route.originalPath;
        var showOnPaths = ["/saved", "/removed"];
        $scope.visible  = showOnPaths.indexOf(currentPath) > -1;
        
        $scope.grid = [];
        $scope.tileData = TileData;
        $scope.bsColSize = TileData.bsColSize;
        $scope.numCols = TileData.numCols;
        $scope.colWidth = TileData.colWidth;
        
        $scope.$watch(
            function()
            {
                return $scope.tileData.activeGrid;
            },
            function()
            {
                $scope.grid = $scope.tileData.activeGrid;
                $scope.bsColSize = TileData.bsColSize;
                $scope.numCols = TileData.numCols;
                $scope.colWidth = TileData.colWidth;
            },
            true
        );
        
        $scope.enlarge = function(image)
        {
            $location.path("/expand" + $location.$$url + "/" + image["root"]);
        }
    });
    
    app.service('TileData', [function() 
    {
        var $this = this;
        $this.savedGrid = [];
        $this.removedGrid = []; 
        $this.activeGrid = [];
        
        $this.minTileWidth = 350;
        $this.bsColSize = 1;
        $this.numCols = 0;
        $this.colWidth = 0;
        $this.colHeight = 0;
        
        $this.init = function()
        {
            $this.bsColSize = $this.getBootstrapColSize();
            $this.numCols = Math.floor(12 / $this.bsColSize);
            $this.colWidth = Math.floor(getWindowSize().w / $this.numCols);
            $this.colHeight =  Math.floor($this.colWidth * 720 / 1080);
        }
        
        $this.getSavedGrid = function(CarouselData, success, error)
        {
            $this.activeGrid = [];
            CarouselData.carousel.reset();
            CarouselData.get("/api/saved", {}, function()
            {
                $this.init();
                var grid = $this.getGrid(CarouselData.carousel);
                $this.savedGrid = grid;
                $this.activeGrid = grid;
                
                if(success){success();}
            }, 
            error);  
        }
        
        $this.getRemovedGrid = function(CarouselData, success, error)
        {
            $this.activeGrid = [];
            CarouselData.carousel.reset();
            CarouselData.get("/api/removed", {}, function()
            {
                $this.init();
                var grid = $this.getGrid(CarouselData.carousel);
                $this.removedGrid = grid;
                $this.activeGrid = grid;
                
                if(success){success();}
            }, 
            error);  
        }
        
       // Transform the carousel into a 2d array of images that represent a
       // grid.  The width of the grid will be the number of columns displayed
       // on the page. 
       $this.getGrid = function(carousel)
       {
            var grid = [];
            var images = [];
            var keys = carousel.keys();
            var currentRow = {};
            
            for(var k in keys)
            {
                images.push(carousel.get(keys[k]));
            }
           
            for(var i = 0; i < images.length; i++)
            {
                 // New row
                 if(i % $this.numCols === 0)
                 {
                     currentRow = {};
                     currentRow["images"] = [];
                     currentRow["colWidth"] = $this.colWidth;
                     currentRow["colHeight"] = $this.colHeight;
                     grid.push(currentRow);
                 }

                 // Create the image object. Pick the best URL with the minimal
                 // resolution.  Clamp the image and set its width and height.
                 var image = {};
                 var bestResolution = $this.getBestImage(images[i], $this.colWidth);
                 image["path"] = bestResolution["path"];
                 image["root"] = bestResolution["root"];
                 var clamped = clamp(bestResolution["width"] - 17, bestResolution["height"], 
                         $this.colWidth - 30, $this.colHeight);

                 image["width"] = clamped.w;
                 image["height"] = clamped.h;

                 // Add to current row
                 currentRow["images"].push(image);
             }

           return grid;
       } 
       
        // Returns the size of a bootstrap column given the screen size.  It is
        // gauranteed that size of the column x number of columns will be less
        // than or equal to the size of the screen.
        $this.getBootstrapColSize = function()
        {
            var maxCols = Math.floor(getWindowSize().w / $this.minTileWidth);
            var numCols = Math.min(maxCols, 12);
            while(12 % numCols !== 0) // must be a factor of 12, for beautification
            {
                numCols--;
            }
            
            var size =  Math.floor(12 / numCols);
            
            // Any column of size 7+ will result in a grid width of 1. So, it
            // might as well be as large as possible
            if(size >= 7)
            {
                size = 12;
            }
            
            return size;
        }
       
       // Returns the smallest image with a width greater than the provided
       // minWidth
       $this.getBestImage = function(images, minWidth)
       {
           var best = {};
           for(var i = 0; i < images.length; i++)
           {
               // Found
               if(images[i]["width"] < minWidth)
               {
                   break;
               }
               
               best = images[i];
           }
           
           return best;
       }
    }]);
    
    // HTML template directive
    app.directive("tileView", function() 
    {
      return {
        templateUrl: 'html/tileview.html'
      };
    });
}};});