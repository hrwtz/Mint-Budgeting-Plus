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
                selected: '='
            },
        	link: function(scope, element, attrs){
                // Check if control or shift keys are pressed
                var ctrlIsPressed = false,
                    searchIsPressed = false;
                $document
                    .on('keydown', function(e){
                        if(event.which=="17")
                            ctrlIsPressed = true;
                        if(event.which=="16")
                            searchIsPressed = true;
                    }).on('keyup', function(){
                        ctrlIsPressed = false;
                        searchIsPressed = false;
                    });


                // On month nav item click select the correct months
        		element.bind('click', function(){
                    scope.$apply(function(){
                        var months = scope.data.date,
                            selected = scope.selected;
                        if (ctrlIsPressed){
                            // Add clicked date if date wasn't already selected or only 1 date was selected
                            if (selected.indexOf(scope.data.date) === -1 || selected.length === 1){
                                months = selected.concat(scope.data.date);
                            // Else remove clicked date
                            }else{
                                selected.splice(selected.indexOf(scope.data.date), 1);
                                months = selected;
                            }
                        }else if (searchIsPressed){
                            
                        }

                        scope.selectMonths({months: months})
                    })
                });
        	}
        }
    }]);