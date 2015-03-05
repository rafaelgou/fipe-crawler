FipeCrawlerApp.controller('ExtractController', [
  '$scope', '$route', '$modal', '$timeout', 'ResourceModel',
  function ($scope, $route, $modal, $timeout, ResourceModel) {
    "use strict";

    $scope.options = {
      tabelas: [],
      tipos: [
        { id: 999, lbl: 'Todos'},
        { id: 1, lbl: 'Carro'},
        { id: 2, lbl: 'Moto'},
        { id: 3, lbl: 'Caminhão'},
      ]
    };

    $scope.data = {
      tabela: null,
      marcas: null,
      tipo: 1
    };

    $scope.results = {
      carro: [],
      moto: [],
      caminhao: []
    };

    $scope.totals = {
      carro: { modelos: 0, veiculos: 0 },
      moto: { modelos: 0, veiculos: 0 },
      caminhao: { modelos: 0, veiculos: 0 }
    };

    $scope.extracting = false;

    $scope._init = function _init() {

      // Get the routes to fetch any changes on $scope.route
      $scope.routes  = {
        extract: '/extract',
        csv: '/csv'
      };

      // Run actions by route
      if ( $route.current.originalPath === $scope.routes.extract ) {
        $scope.extract();
      } else if ( $route.current.originalPath === $scope.routes.csv ) {
        $scope.csv();
      } else if ( $route.current.originalPath === $scope.routes.index ) {
        $scope.index();
      }

    };

    $scope.extract = function extract() {
      _getTabelas();
    };

    $scope.csv = function csv() {};

    $scope.index = function index() {};

    var _getTabelas = function _getTabelas() {

      var params = { action: 'tabelas' };

      ResourceModel.get( params )
        .$promise
        .then(function ( response ) {
          console.log($scope.options.tabelas);
          $scope.options.tabelas = response.results;
        })
        .catch(function ( error ) {
          console.log(error);

          $scope.onError( error );
        });
    };

    $scope.doExtractMarcas = function extract() {

      var params = $scope.data;
      params.action ='extract_marcas';
      $scope.extracting = true;

      ResourceModel.get( params )
        .$promise
        .then(function ( response ) {
          switch($scope.data.tipo) {
            case 1:
              $scope.results.carro = response.results;
              break;
            case 2:
              $scope.results.moto = response.results;
              break;
            case 3:
              $scope.results.caminhao = response.results;
              break;
          }
        })
        .then(function() {
          var results = [];
          switch($scope.data.tipo) {
            case 1:
              results = $scope.results.carro;
              break;
            case 2:
              results = $scope.results.moto;
              break;
            case 3:
              results = $scope.results.caminhao;
              break;
          }
          for (var i in results) {
            var result = results[i];
            result.status = 'run';
            $scope.doExtractModelos( result );
          }
        })
        .catch(function ( error ) {
          $scope.onError( error );
        });
    };

    $scope.doExtractModelos = function doExtractModelos( marca ) {

      if (!$scope.extracting) {
        return;
      }

      var params = $scope.data;
      params.action ='extract_modelos';
      params.marca  = marca.id;

      ResourceModel.get( params )
        .$promise
        .then(function ( response ) {
          marca.status = 'ok';
          marca.modelos = response.results;
          switch($scope.data.tipo) {
            case 1:
              $scope.totals.carro.modelos = $scope.totals.carro.modelos + response.results.length + 1;
              break;
            case 2:
              $scope.totals.moto.modelos  = $scope.totals.carro.moto + response.results.length + 1;
              break;
            case 3:
              $scope.totals.carro.modelos = $scope.totals.carro.caminhao + response.results.length + 1;
              break;
          }
        })
        .catch(function ( error ) {
          $scope.onError( error );
        });

    };

    $scope.doExtractVeiculos = function extract() {

      if (!$scope.extracting) {
        return;
      }

      var params = $scope.data;
      params.action ='extract_veiculos';

      ResourceModel.get( params )
        .$promise
        .then(function ( response ) {
//          $scope.data.marcas = response;
        })
        .catch(function ( error ) {
          $scope.onError( error );
        });
    };

    $scope.cancelExtract = function cancelExtract() {

      $scope.extracting = false;

      var results = [];
      switch($scope.data.tipo) {
        case 1:
          results = $scope.results.carro;
          break;
        case 2:
          results = $scope.results.moto;
          break;
        case 3:
          results = $scope.results.caminhao;
          break;
      }
      for (var i in results) {
        var result = results[i];
        if (result.status === 'run') {
          result.status = 'cancel';
        }
        $scope.doExtractModelos( result );
      }

    };


    /**
     * Error Handling
     *
     * @param error
     */
    $scope.onError = function onError( error ) {

      var modalInstance = $modal.open({
        templateUrl: APP_PATH + '/modal/alert.html',
        controller: 'ModalInstanceCtrl',
        resolve: {
          data: function () {
            return {
              title: error.status + ' ' + error.statusText,
              msg:   error.data.msg

            };
          }
        }
      });

      modalInstance.result.then(function (response) {

      }, function (response) {

      });

    };

    $scope._init();

  }
]);
