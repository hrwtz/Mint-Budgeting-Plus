angular.module('myApp.transaction', []).
   /* Drivers controller */
    controller('transactionController', function($scope, $routeParams, transactionService) {
    	$scope.transactionDates = {};

        transactionService.getTransactions($routeParams.budgets).success(function(data){
        	for (var i = 0; i < data.length; i++) {
        		if (!$scope.transactionDates[data[i].date])
        			$scope.transactionDates[data[i].date] = [];

        		$scope.transactionDates[data[i].date].push(data[i]);
        	};
        	console.log($scope.transactionDates)
        })

    });