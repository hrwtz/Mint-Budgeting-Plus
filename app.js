'use strict';

// Declare app level module which depends on views, and components
angular.module('myApp', [
  'myApp.budgetMain',
  'myApp.preload',
  'myApp.monthNavItem',
  'myApp.filters',
  // 'budgetLineDirective',
  // 'budgetService',
  'ngRoute',
]).
config(['$routeProvider', function($routeProvider) {
  $routeProvider.otherwise({
  	templateUrl: 'app/components/budgetMain/budgetMainView.html',
  	controller: 'budgetMainController',
  });
}]);
