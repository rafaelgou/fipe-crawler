var APP_PATH = BASE_URL + '/app';

var FipeCrawlerApp = angular.module('FipeCrawlerApp', [
  'ngRoute',
  'ngResource',
  'ui.bootstrap',
  'ngCsv'
]);

FipeCrawlerApp.config(['$routeProvider', function ($routeProvider) {

  "use strict";

  $routeProvider
    .when('/',
    {
      controller: 'MainController',
      templateUrl: APP_PATH + '/main/index.html'
    })
    .when('/extract',
    {
      controller: 'ExtractController',
      templateUrl: APP_PATH + '/extract/index.html'
    })
    .when('/csv',
    {
      controller: 'ExtractController',
      templateUrl: APP_PATH + '/csv/index.html'
    })
    .when('/404',
    {
      controller: 'ErrorController',
      templateUrl: APP_PATH + '/errors/404.html'
    })
//    .when('/500',
//    {
//      controller: 'ErrorController',
//      templateUrl: APP_PATH + '/errors/500.html'
//    })
    .otherwise({ redirectTo: '/404' })
  ;

}]);
