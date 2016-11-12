define([
    "../lib/js-common/Carousel"
], function(){return{init: function(app) {
            
    
    
    // Controller for the saved route
    app.controller("SavedCtrl", function(CarouselData, $routeParams)
    {
        CarouselData.carousel.reset();
        CarouselData.getSaved(function(){}, function(){}); 
    });
            
    // Controller for the image/:id route
    app.controller("TileCtrl", function(CarouselData, $scope, $location, $route)
    {
        var minTileWidth = 350;
        $scope.grid = [];
        $scope.carousel = CarouselData.carousel;
        $scope.bsColSize = getBootstrapColSize();
        $scope.numCols = Math.floor(12 / $scope.bsColSize);
        $scope.colWidth = Math.floor(getWindowSize().w / numCols);

        // Watch for changes in the carousel, if the current image changes then
        // update the carousel view
        $scope.$on('carousel:saved', update);

        // Update the grid
        update = function()
        {
            var images = [];
            var keys = $scope.carousel.keys();
            for(var k in keys)
            {
                images.push($scope.carousel.get(k));
            }
            
            $scope.grid = getGrid(images, $scope.numCol, minTileWidth);
        };
        
        // Returns the size of a bootstrap column given the screen size.  It is
        // gauranteed that size of the column x number of columns will be less
        // than or equal to the size of the screen.
        getBootstrapColSize = function()
        {
            var maxCols = Math.floor(getWindowSize().h / minTileWidth);
            var numCols = Math.min(maxCols, 12);
            var size =  Math.floor(12 / numCols);
            
            // Any column of size 7+ will result in a grid width of 1. So, it
            // might as well be as large as possible
            if(size >= 7)
            {
                size = 12;
            }
            
            return size;
        }
        
       // Transform the carousel into a 2d array of images that represent a
       // grid.  The width of the grid will be the number of columns displayed
       // on the page. 
       getGrid = function(images, cols, colWidth)
       {
           var grid = [];
           var currentRow = {};
           var colHeight =  Math.floor(colWidth * 720 / 1080);
           for(var i = 0; i < cols; i++)
           {
               // New row
               if(i % cols === 0)
               {
                   currentRow["images"] = [];
                   currentRow["height"] = 0;
                   currentRow["col_width"] = colWidth;
                   currentRow["col_height"] = colHeight;
                   grid.push(currentRow);
               }
               
               // Create the image object. Pick the best URL with the minimal
               // resolution.  Clamp the image and set its width and height.
               var image = {};
               var bestResolution = getBestImage(images[i], colWidth);
               image["path"] = bestResolution["path"];
               var clamped = clamp(image["width"], image["height"], colWidth, colHeight);
               image["width"] = clamped.w;
               image["height"] = clamped.h;
               
               // Add to current row
               currentRow["images"].push(image);
           }
           
           return grid;
       }
       
       // Returns the smallest image with a width greater than the provided
       // minWidth
       getBestImage = function(images, minWidth)
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
    });
}};});