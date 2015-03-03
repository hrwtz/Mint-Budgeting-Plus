'use strict';

/* Directives */


angular.module('myApp.budgetLine', []).
    directive('budgetline', [function($document) {
        return {
        	templateUrl: 'app/shared/budgetLine/budgetLineView.html',
        	replace: true,
        	scope: {
                budget: '=',
            },
        	link: function(scope, element, attrs){
                
        	}
        }
    }]);