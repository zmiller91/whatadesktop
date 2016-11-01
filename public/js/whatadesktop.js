define([
    'carousel',
],

  function(carousel){
      
    // Create the base module for the page
    var wad = angular.module('whatadesktop', []);
    
    // Init the controllers, directives, and services for all the components
    // on the page
    carousel.init(wad);
    
    // Bootstrap the page
    angular.bootstrap(document, ['whatadesktop']);
});