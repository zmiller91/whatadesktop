define([
    "../lib/js-common/Carousel",
    "js/utilities.js"
], function(){return{init: function(app) {

    app.controller("NotificationCtrl", function($cookies, $uibModal, $scope)
    {
        var show = function() {
            $uibModal.open({
                templateUrl: 'html/notifications.html',
                controller: 'NotificationModal',
                size: 'md'
            });
        }
        
        // If the user's never been here, then display the modal
        var name = "wad-bhb";
        if(!$cookies.get(name))
        {
            show();
        }
        
        // Set a cookie and expire it in a year
        var exp = new Date();
        exp.setFullYear(exp.getFullYear() + 1);
        $cookies.put(name, exp.getTime(), {path: "/", expires: exp });
        
        // Fetch user's root images, if a user is logged in
        $scope.$on('nav:help', function(event, data) {
            show();
        });
    });
    
    app.controller("NotificationModal", function($scope, $uibModalInstance)
    {
        $scope.close = function()
        {
            $uibModalInstance.dismiss('cancel');
        };
    });
}};});