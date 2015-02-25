'use strict';

// Declare app level module which depends on views, and components
angular.module('myApp', [
    'myApp.budgetMain',
    'myApp.transaction',
    'myApp.transactionService',
    'myApp.preload',
    'myApp.monthNavItem',
    'myApp.filters',
    'ngRoute',
]).
config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/transactions/:budgets', {
            templateUrl: 'app/components/transaction/transactionView.html',
            controller: 'transactionController',
        })
        .otherwise({
  	         templateUrl: 'app/components/budgetMain/budgetMainView.html',
  	         controller: 'budgetMainController',
        });
}]);
