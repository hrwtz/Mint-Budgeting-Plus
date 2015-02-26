<!DOCTYPE html>
<!--[if lt IE 7]>      <html lang="en" ng-app="myApp" class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html lang="en" ng-app="myApp" class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html lang="en" ng-app="myApp" class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en" ng-app="myApp" class="no-js"> <!--<![endif]-->
<head>

  <meta charset="utf-8">

  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <title>Mint Budgeting Plus</title>

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="HandheldFriendly" content="True">
  <meta name="MobileOptimized" content="320">

  <link rel="stylesheet" href="assets/css/application.css">

</head>
<body>
  
  <div ng-view></div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.5/angular.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.5/angular-route.js"></script>
  <script src="app.js"></script>
  <script src="app/components/budgetMain/budgetMainController.js"></script>
  <script src="app/components/transaction/transactionController.js"></script>
  <script src="app/components/transaction/transactionService.js"></script>
  <script src="app/shared/monthnavitem/monthNavItemDirective.js"></script>
  <script src="app/shared/filters/filters.js"></script>
  <script src="app/shared/persistentSelected/persistentSelected.js"></script>
  <?php 
  function get_curl($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
  }
  ?>
  <script>
    angular.module('myApp.preload', [])
    .constant('$preloaded', {
      'categories' : <?php echo get_curl('http://localhost/budgets/api/index.php/categories/get'); ?>,
      'budgets' : <?php echo get_curl('http://localhost/budgets/api/index.php/budgets/get'); ?>
    });
  </script>
</body>
</html>
