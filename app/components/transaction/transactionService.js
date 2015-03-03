angular.module('myApp.transactionService', []).
   /* Drivers controller */
    factory('transactionService', function($http) {
    	return {
    		getTransactions: function(budgets){
    			if (budgets)
    				return $http.get('/budgets/api/index.php/transactions/get_by_budget/' + budgets);
				
				return $http.get('/budgets/api/index.php/transactions/get/2014-01-01/2014-05-01');
    		}
    	};
    });