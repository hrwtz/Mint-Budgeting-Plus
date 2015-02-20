function shadeColor(color, percent) {   
    var f=parseInt(color.slice(1),16),t=percent<0?0:255,p=percent<0?percent*-1:percent,R=f>>16,G=f>>8&0x00FF,B=f&0x0000FF;
    return "#"+(0x1000000+(Math.round((t-R)*p)+R)*0x10000+(Math.round((t-G)*p)+G)*0x100+(Math.round((t-B)*p)+B)).toString(16).slice(1);
}

function getColor(ratio){

    var makeChannel = function(a, b) {
        return(a + Math.round((b-a)*(ratio)));
    }

    var makeColorPiece = function(num) {
        num = Math.min(num, 255);   // not more than 255
        num = Math.max(num, 0);     // not less than 0
        var str = num.toString(16);
        if (str.length < 2) {
            str = "0" + str;
        }
        return(str);
    };

    if (ratio < .5){
        color1 = '00FF00';
        color2 = 'FFFF00';
        ratio *= 2;
    }else{
        color1 = 'FFFF00';
        color2 = 'FF0000';
        ratio = (ratio - .5) * 2;
    }

    r = makeColorPiece(makeChannel(parseInt(color1.substring(0,2), 16), parseInt(color2.substring(0,2), 16)));
    g = makeColorPiece(makeChannel(parseInt(color1.substring(2,4), 16), parseInt(color2.substring(2,4), 16)));
    b = makeColorPiece(makeChannel(parseInt(color1.substring(4,6), 16), parseInt(color2.substring(4,6), 16)));

    return '#' + r + g + b;
}

angular.module('myApp.budgetMain', []).
   /* Drivers controller */
    controller('budgetMainController', function($scope, $preloaded) {
        console.log($preloaded)

        // Set up data for monthNav
        $scope.budgetedMonths = [];
        angular.forEach($preloaded.budgets, function(value, key){
            this.push({
                date: key,
                selected: false,
                isOver: value.totals.total_budgeted - value.totals.total_spent < 0
            });
        }, $scope.budgetedMonths);
        

        // Set latest month as selected
        $scope.budgetedMonths[$scope.budgetedMonths.length-1].selected = true;

        // Set up selected months
        $scope.selectMonths = function(months){
            // Set default for months as the last month of budgets
            months = typeof months !== 'undefined' ? months : [$scope.budgetedMonths[$scope.budgetedMonths.length-1].date];

            $scope.selected = [];
            // Set selected property of budgetedMonths if month has been selected
            for (var i = 0; i < $scope.budgetedMonths.length; i++) {
                $scope.budgetedMonths[i].selected = months.indexOf($scope.budgetedMonths[i].date) !== -1;
                if (months.indexOf($scope.budgetedMonths[i].date) !== -1){
                    $scope.selected.push($scope.budgetedMonths[i].date);
                }
            };

            var date = new Date();
            var currentMonth = date.getMonth() + 1;
            if (currentMonth < 10) currentMonth = '0' + currentMonth;
            $scope.isCurrentMonth = ($scope.selected[0] == date.getFullYear() + '-' + currentMonth + "-01");

            $scope.updateBudgets();
        }


        // Set up budget data based on selected months
        $scope.updateBudgets = function(){
            var parents = [];
            for (var i = 0; i < $scope.selected.length; i++) {
                var monthlyBudgets = $preloaded.budgets[$scope.selected[i]];

                for (var k = 0; k < Object.keys(monthlyBudgets).length - 1; k++) {
                    var spending = monthlyBudgets[k];
                    // Set up parent Object
                    var parentID = getCategoryValue(spending.category_id, 'parent_id');
                    if (parentID == '0') parentID = spending.category_id;
                    var parentCatName = getCategoryValue(parentID, 'name');

                    if (!parents[parentID]){
                        parents[parentID] = {
                            budgets : [],
                            category_name : parentCatName,
                            category_id : parentID,
                        };
                    }

                    // Set up budget object
                    var date = new Date();
                    var daysInMonthDate = new Date(date.getFullYear(), date.getMonth()+1, 0).getDate();
                    var totalAmount = - spending.rollover_amount - spending.extended_amount + spending.spent_amount
                    var spentPercentage = totalAmount / spending.budgeted_amount;
                    spentPercentage = spentPercentage > 1 ? 1 : spentPercentage;
                    var budget = {
                        id: spending.budget_id,
                        category_id: spending.category_id,
                        title: getCategoryValue(spending.category_id, 'name'),
                        budgetedAmount: spending.budgeted_amount,
                        spentAmount: spending.spent_amount,
                        extendedAmount: spending.extended_amount,
                        rolloverAmount: spending.rollover_amount,
                        spentPercentage: spentPercentage,
                        spentColor: shadeColor(getColor(spentPercentage), -.2),
                        remainingAmount: spending.budgeted_amount - totalAmount,
                        monthPercentage: date.getDate() / daysInMonthDate,
                    }
                    // var same_budget = false;
                    // for (var j = 0; j < parents[parentID].budgets.length; j++) {
                    //     if (budget.id === parents[parentID].budgets[j].id){
                    //         same_budget = parents[parentID].budgets[j];
                    //         break;
                    //     }
                    // }
                    // console.log(budget.id)
                    // if (same_budget){

                    // }else{
                        parents[parentID].budgets.push(budget);
                    // }
                };


                 // Set up totals budget
                var totalSpentPercentage = monthlyBudgets.totals.total_spent / monthlyBudgets.totals.total_budgeted;
                totalSpentPercentage = totalSpentPercentage > 1 ? 1 : totalSpentPercentage;
                if (!parents[0]) parents[0] = {category_name: "Total", budgets: []};
                parents[0].budgets.push({
                    category_id: 0,
                    budgetedAmount: monthlyBudgets.totals.total_budgeted,
                    spentAmount: monthlyBudgets.totals.total_spent,
                    spentPercentage: totalSpentPercentage,
                    spentColor: shadeColor(getColor(totalSpentPercentage), -.2),
                    remainingAmount: monthlyBudgets.totals.total_budgeted - monthlyBudgets.totals.total_spent,
                    monthPercentage: date.getDate() / daysInMonthDate,
                });
            };

            // Add budgets together for when selecting multiple months
            for (var parent_cat in parents){
                var realBudget = {};
                for (var i = 0; i < parents[parent_cat].budgets.length; i++) {
                    var currentBudget = parents[parent_cat].budgets[i];

                    if (realBudget[currentBudget.category_id]){
                        var spentAmount = realBudget[currentBudget.category_id].spentAmount + currentBudget.spentAmount,
                            remainingAmount = realBudget[currentBudget.category_id].remainingAmount + currentBudget.remainingAmount,
                            budgetedAmount = realBudget[currentBudget.category_id].budgetedAmount + currentBudget.budgetedAmount;
                        realBudget[currentBudget.category_id] = {
                            budgetedAmount: budgetedAmount,
                            category_id: currentBudget.category_id,
                            remainingAmount: remainingAmount,
                            spentAmount: spentAmount,
                            spentColor: shadeColor(getColor(spentAmount / budgetedAmount), -.2),
                            spentPercentage: spentAmount / budgetedAmount
                        }
                    }else{
                        realBudget[currentBudget.category_id] = currentBudget;
                    }
                }
                parents[parent_cat].budgets = realBudget
            };

            $scope.parentBudgets = [];
            for (var parent_cat in parents){
                $scope.parentBudgets.push(parents[parent_cat]);
            }
        }

        var getCategoryValue = function(categoryID, value){
            for (var i = 0; i < $preloaded.categories.length; i++) {
                if ($preloaded.categories[i].category_id == categoryID){
                    return $preloaded.categories[i][value];
                }
            };
            return false;
        }


        $scope.selectMonths();
    });