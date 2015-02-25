angular.module('myApp.filters', [])
	.filter('positive', function() {
        return function(input) {
            if (!input) {
                return 0;
            }

            return Math.abs(input);
        };
    })
    .filter('categoryName', function($preloaded){
    	return function(input) {
    		for (var i = 0; i < $preloaded.categories.length; i++) {
                if ($preloaded.categories[i].category_id == input){
                    return $preloaded.categories[i]['name'];
                }
            };
            return input;
    	}
    });