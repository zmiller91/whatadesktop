define([
    "../lib/js-common/Carousel",
    "js/utilities.js"
], function(){return{init: function(app) {
    

    app.controller("NotificationCtrl", function($cookies, $uibModal)
    {
        // If the user's never been here, then display the modal
        var name = "wad-bhb";
        if(!$cookies.get(name))
        {
            $uibModal.open({
                templateUrl: 'html/notifications.html',
                controller: 'NotificationModal',
                size: 'md'
            });
        }
        // Set a cookie and expire it in a year
        var exp = new Date();
        exp.setFullYear(exp.getFullYear() + 1);
        $cookies.put(name, exp.getTime(), {path: "/", expires: exp });
    });
    
    
    app.controller("NotificationModal", function($scope, $uibModalInstance)
    {
        $scope.close = function()
        {
            $uibModalInstance.dismiss('cancel');
        };
    });
}};});