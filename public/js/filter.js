define([
], function(){return{init: function(app) {

    app.controller("FilterCtrl", function(FilterData, $uibModal, $scope)
    {
        var show = function(close) {
            $uibModal
            .open({
                templateUrl: 'html/filter.html',
                controller: 'FilterModal',
                size: 'sm',
            })
            .closed.then(function(){
                $( "#carousel" ).focus();
                if(close) {
                    close();
                }
            });
        }
        
        // Fetch user's root images, if a user is logged in
        $scope.$on('nav:filter', function(event, callback) {
            show(callback);
        });
    });
    
    app.controller("FilterModal", function(FilterData, $scope, $uibModalInstance, $rootScope)
    {
        $scope.form = FilterData.get();
        
        $scope.done = function()
        {
            $scope.loading = false;
            $uibModalInstance.dismiss('cancel');
        };
        
        $scope.save = function()
        {
            FilterData.width = $scope.form.width;
            FilterData.height = $scope.form.height;
            FilterData.ar = $scope.form.ar;
            
            $scope.loading = true;
            var data = {
                success: $scope.done,
                failure: $scope.done
            }
            
            $rootScope.$broadcast('filter:updated', data);
        };
    });
    
    app.service('FilterData', [function() 
    {
        this.width = {};
        this.height = {};
        this.ar = {};
        
        this.get = function() {
            return {
                width: {min: this.width.min, max: this.width.max},
                height: {min: this.height.min, max: this.height.max},
                ar: this.ar
            };
        };
        
        this.count = function() {
            return this._objSize(this.width) + this._objSize(this.height) + 
                    this._objSize(this.ar);
        }
        
        this._objSize = function(obj) {
            var count = 0;
            var keys = Object.keys(obj);
            for(var v in keys) {
                var k = keys[v];
                count += obj[k] || obj[k] === "0" ? 1 : 0;
            }
            
            return count;
        }
    }]);
}};});