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

function getLastDateOfMonth(date){
    var d = new Date(date);
    var d2 = new Date(d.getFullYear(), d.getMonth()+2, 0);
    var yyyy = d2.getFullYear().toString();
    var mm = (d2.getMonth()+1).toString();
    var dd  = d2.getDate().toString();
    return yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]);
}

angular.module('myApp.budgetMain', []).
   /* Drivers controller */
    controller('budgetMainController', function($scope, $preloaded, persistentSelected) {
        // Set up selected months
        $scope.selectMonths = function(months){
            // Set default for months as the last month of budgets
            months = typeof months.length !== 'undefined' ? months : [$scope.budgetedMonths[$scope.budgetedMonths.length-1].date];

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

            // Update service to share selected data
            persistentSelected.setData($scope.selected)

            $scope.updateBudgets();
        }


        // Set up budget data based on selected months
        $scope.updateBudgets = function(){
            var parents = [];
            var totals;
            var elseBudget;
            for (var i = 0; i < $scope.selected.length; i++) {

                var monthlyBudgets = $preloaded.budgets[$scope.selected[i]];

                var date = new Date();
                var daysInMonthDate = new Date(date.getFullYear(), date.getMonth()+1, 0).getDate();

                // Set up totals budget
                if (!parents.totals) parents.totals = {budgets: []};
                parents.totals.budgets.push(
                    setUpBudget(monthlyBudgets.totals, true)
                );

                for (var k = 0; k < Object.keys(monthlyBudgets).length - 1; k++) {
                    var budget = setUpBudget(monthlyBudgets[k])

                    // If else budget, break out of loop and save budget
                    if (monthlyBudgets[k].category_id === '0'){
                        elseBudget = monthlyBudgets[k];
                        break;
                    }

                    // Set up parent Object
                    var parentID = getParentId(monthlyBudgets[k].category_id);

                    if (!parents[parentID]){
                        parents[parentID] = {
                            budgets : [],
                            category_name : getCategoryValue(parentID, 'name'),
                            category_id : parentID,
                        };
                    }
                    
                    parents[parentID].budgets.push(budget);
                };

                break;
                // Set up everything else budget
                // if (!parents.else) parents.else = {budgets: []};
                // parents.else.budgets.push(elseBudget);
                var bud = new Array();
                var elseCategories = new Array();
                console.log(elseBudget)
                if (elseBudget.elseBudget){
                    for (var k = 0; k < elseBudget.else.length; k++) {
                        elseCategories.push(elseBudget.else[k].category_id);
                        var elseParentID = getParentId(elseBudget.else[k].category_id);
                        if (!bud[elseParentID]){
                            bud[elseParentID] = [];
                        }
                        // bud[elseParentID].spent += Number(elseBudget.elseBudget[k].spent);
                        // console.log(elseBudget)
                        bud[elseParentID].push({
                            category_name: getCategoryValue(elseBudget.elseBudget[k].category_id, 'name'),
                            category_id: elseBudget.elseBudget[k].category_id,
                            spent: elseBudget.elseBudget[k].spent,
                            isParent: getCategoryValue(elseBudget.elseBudget[k].category_id, 'parent_id') == 0
                        })
                    }
                }
                elseBudget.elseBudget = bud;
                elseBudget.elseBudget.elseCategories = elseCategories;
                // if (!parents.else) parents.else = {budgets: []};
                parents.push({
                    budgets:[setUpBudget(elseBudget)],
                    category_name : getCategoryValue(elseBudget.category_id, 'name'),
                    category_id : elseBudget.category_id,
                })

                // console.log({
                //     budgets:[setUpBudget(elseBudget)],
                //     category_name : getCategoryValue(elseBudget.category_id, 'name'),
                //     category_id : elseBudget.category_id,
                // })
                
            };

            // Combine budgets together for when selecting multiple months
            for (var parent_cat in parents){
                parents[parent_cat].budgets = combineBudgets(parents[parent_cat].budgets);
            };

            // Set up final scope array
            $scope.parentBudgets = [];
            for (var parent_cat in parents){
                // If array key is a number, push it to final array, otherwise keep the key (as associative)
                if (Number(parent_cat) == parent_cat){
                    $scope.parentBudgets.push(parents[parent_cat]);
                }else{
                    $scope.parentBudgets[parent_cat] = (parents[parent_cat]);
                }
            }
            // console.log($scope.parentBudgets)

        }

        var getCategoryValue = function(categoryID, value){
            for (var i = 0; i < $preloaded.categories.length; i++) {
                if ($preloaded.categories[i].category_id == categoryID){
                    return $preloaded.categories[i][value];
                }
            };
            return false;
        };

        // Get's parent category ID. If category is a parent, returns back the category ID
        var getParentId = function(categoryID){
            var parentID = getCategoryValue(categoryID, 'parent_id');
            if (parentID == '0') parentID = categoryID;
            return parentID;
        }

        var setUpBudget = function(spending, isTotals){
            isTotals = (typeof isTotals !== 'undefined') ? isTotals : false;
            // Set up budget object
            spending.rollover_amount = spending.rollover_amount ? spending.rollover_amount : 0;
            spending.extended_amount = spending.extended_amount ? spending.extended_amount : 0;
            var date = new Date();
            var daysInMonthDate = new Date(date.getFullYear(), date.getMonth()+1, 0).getDate();
            var totalAmount = - spending.rollover_amount - spending.extended_amount + spending.spent_amount
            var spentPercentage = totalAmount / spending.budgeted_amount;
            spentPercentage = spentPercentage > 1 ? 1 : spentPercentage;
            // var elseCategories = new Array();
            // if (spending.else){
            //     angular.forEach(spending.else, function(value, parentCategory) {
            //         elseCategories.push(value.category_id);
            //     });
            // }
            // console.log(spending.else.elseCategories)
            // transactionUrlCategories = spending.else.elseCategories.length ? spending.else.elseCategories.join(',') : spending.category_id;

            // if ('elseBudget' in spending && 'elseCategories' in spending.elseBudget){
            //     console.log(spending.elseBudget.elseCategories)
            // }
            transactionUrlCategories = 1;
            return {
                id: spending.budget_id,
                category_id: isTotals ? 0 : spending.category_id,
                title: isTotals ? 'Total' : getCategoryValue(spending.category_id, 'name'),
                budgetedAmount: spending.budgeted_amount,
                spentAmount: spending.spent_amount,
                extendedAmount: spending.extended_amount,
                rolloverAmount: spending.rollover_amount,
                spentPercentage: spentPercentage,
                spentColor: shadeColor(getColor(spentPercentage), -.2),
                remainingAmount: spending.budgeted_amount - totalAmount,
                monthPercentage: date.getDate() / daysInMonthDate,
                transactionUrl: getTransactionUrl(transactionUrlCategories),
                else: spending.elseBudget,
            }
        };

        var combineBudgets = function(budgets){
            var combinedBudget = {};
            for (var i = 0; i < budgets.length; i++) {
                var nextBudget = budgets[i];

                if (combinedBudget[nextBudget.category_id]){
                    var spentAmount = combinedBudget[nextBudget.category_id].spentAmount + nextBudget.spentAmount,
                        remainingAmount = combinedBudget[nextBudget.category_id].remainingAmount + nextBudget.remainingAmount,
                        budgetedAmount = combinedBudget[nextBudget.category_id].budgetedAmount + nextBudget.budgetedAmount,
                        spentPercentage = spentAmount / budgetedAmount;
                    if (spentPercentage > 1)spentPercentage = 1;

                    combinedBudget[nextBudget.category_id] = {
                        id: nextBudget.id + ',' + combinedBudget[nextBudget.category_id].id,
                        category_id: nextBudget.category_id,
                        title: nextBudget.title,
                        budgetedAmount: budgetedAmount,
                        spentAmount: spentAmount,
                        extendedAmount: combinedBudget[nextBudget.category_id].extendedAmount + nextBudget.extendedAmount,
                        rolloverAmount: combinedBudget[nextBudget.category_id].rolloverAmount + nextBudget.rolloverAmount,
                        spentPercentage: spentPercentage,
                        spentColor: shadeColor(getColor(spentAmount / budgetedAmount), -.2),
                        remainingAmount: remainingAmount,
                        transactionUrl: getTransactionUrl(nextBudget.category_id),
                    }
                }else{
                    combinedBudget[nextBudget.category_id] = nextBudget;
                }
            }
            return combinedBudget;
        };

        var getTransactionUrl = function(categories){
            if (!categories) categories = '';
            return '#/transactions/' + $scope.selected[0] +'/'+ getLastDateOfMonth($scope.selected[$scope.selected.length-1]) +'/' +categories;
        };

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

        // Set up selected months, and try and get selected data from persistent service
        $scope.selectMonths(persistentSelected.getData());

    });