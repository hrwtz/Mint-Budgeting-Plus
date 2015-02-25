angular.module('myApp.transactionService', []).
   /* Drivers controller */
    factory('transactionService', function($http) {
    	return {
    		getTransactions: function(budgets){
    			return $http.get('/budgets/api/index.php/transactions/get/' + budgets);
    		}
    	};
    });