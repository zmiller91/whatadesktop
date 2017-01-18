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
        $scope.errors = [];
        
        $scope.done = function()
        {
            $scope.loading = false;
            $uibModalInstance.dismiss('cancel');
        };
        
        
        $scope.save = function()
        {
            if(!validate()){
                return;
            }
            
            
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
        
        var validate = function() {
            $scope.errors = [];
            var valid = true;
            
            // Aspect ratio must have both fields, or not
            if(xor($scope.form.ar.width, $scope.form.ar.height)) {
                $scope.errors.push("Both aspect ratio fields must exist");
                valid = false;
            }
            else if($scope.form.ar.width && $scope.form.ar.height) 
            {
                // Aspect ratio fields must be greater than 0
                if(!(validateNumber($scope.form.ar.width, 0) && 
                        validateNumber($scope.form.ar.height, 0))) {
                    $scope.errors.push("Both aspect ratio fields must be numbers greater than 1");
                    valid = false;
                }
            }
            
            // All other fields must be greater than or equal to 0
            if( !(validateMinMax($scope.form.width) &&
                    validateMinMax($scope.form.height))) {
                $scope.errors.push("All min/max fields must be numbers greater than 0");
                valid = false;
            }   
            
            return valid;
        }
        
        var validateMinMax = function(obj) {
            
            var valid = true;
            if(obj) {
                if(obj.min) {
                    valid = valid &&
                            validateNumber(obj.min, -1);
                }
                if(obj.max) {
                    valid = valid && 
                            validateNumber(obj.max, -1);
                }
            }
            
            return valid;
        }
        
        var validateNumber = function(number, minimum) {
            
            if (isNaN(number)) {
                return false;
            }
            else if(number <= minimum)
            {
                return false;
            }
            
            return true;
        }
        
        var xor = function(a, b) {
            return ( a || b ) && !( a && b );
        }
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
                    (this.ar.width && this.ar.height ? 1 : 0);
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