'use strict';

/* Directives */


angular.module('myApp.monthNavItem', []).
    directive('monthnavitem', ['$document', function($document) {
        return {
        	templateUrl: 'app/shared/monthnavitem/monthNavItemView.html',
        	replace: true,
        	scope: {
                data: '=',
                selectMonths: '&click',
                selected: '=',
                budgetedMonths: '=',
            },
        	link: function(scope, element, attrs){
                // Check if control or shift keys are pressed
                var ctrlIsPressed = false,
                    searchIsPressed = false;

                // On month nav item click select the correct months
        		element.bind('click', function(e){
                    scope.$apply(function(){
                        var months = scope.data.date,
                            selected = scope.selected;
                        if (e.shiftKey){
                            var allMonths = [],
                                k;

                            for (var i = 0; i < scope.budgetedMonths.length; i++) {
                                allMonths.push(scope.budgetedMonths[i].date);
                            };
                            
                            // If selected dates were consecutive, add all months between selected and clicked dates
                            months = selected;
                            var clickedIndex = allMonths.indexOf(scope.data.date);
                            var selectedFirstIndex = allMonths.indexOf(scope.selected[0]);
                            var selectedLastIndex = allMonths.indexOf(scope.selected[scope.selected.length-1]);
                            var isClickedBeforeSelected = clickedIndex < selectedFirstIndex;
                            var monthCountToAdd = isClickedBeforeSelected ? selectedFirstIndex - clickedIndex : clickedIndex - selectedLastIndex;
                            var monthIndexToAdd = isClickedBeforeSelected ? clickedIndex : selectedLastIndex + 1;

                            for (var i = 0; i < monthCountToAdd; i++) {
                                months.push(allMonths[i + monthIndexToAdd]);
                            }
                        }

                        scope.selectMonths({months: months})
                    })
                });
        	}
        }
    }]);