angular.module('myApp.filters', [])
	.filter('positive', function() {
        return function(input) {
            if (!input) {
                return 0;
            }

            return Math.abs(input);
        };
    })
    .filter('currencyCustom', ["$filter", function($filter) {
        return function(amount, currencySymbol) {
            var currency = $filter('currency');         

            if(amount < 0){
                return currency(amount, currencySymbol).replace("(", "-").replace(")", ""); 
            }

            return currency(amount, currencySymbol);
        };
    }])
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