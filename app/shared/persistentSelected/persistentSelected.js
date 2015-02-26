angular.module('myApp.persistentSelected', []).
   /* Drivers controller */
    factory('persistentSelected', function() {
    	var selected = {};

    	return {
    		getData: function(){
    			return selected;
    		},
    		setData: function(newSelected){
    			selected = newSelected;
    		},
    	};
    });