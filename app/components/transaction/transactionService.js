angular.module('myApp.transactionService', []).
   /* Drivers controller */
    factory('transactionService', function($http) {
    	return {
    		getTransactions: function(start_date, end_date, category_id){
				return $http.get('/budgets/api/index.php/transactions/get/'+start_date+'/'+end_date+'/'+category_id+'/');
    		}
    	};
    });