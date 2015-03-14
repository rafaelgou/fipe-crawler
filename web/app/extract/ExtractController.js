FipeCrawlerApp.controller('ExtractController', [
  '$scope', '$route', '$modal', '$timeout', 'ResourceModel',
  function ($scope, $route, $modal, $timeout, ResourceModel) {
    "use strict";

    $scope.progress = {
      'active': false,
      'type'  : 'info',
      'max'   : 1,
      'val'   : 1,
      'prc'   : 0,
      'msg'   : 'Sem Atividade',
      'bar'   : '0/0'
    };

    $scope.options = {
      tabelas: [],
      tipos: [
        //{ id: 999, lbl: 'Todos'},
        { id: 1, lbl: 'Carro'},
        { id: 2, lbl: 'Moto'},
        { id: 3, lbl: 'Caminhão'},
      ]
    };

    $scope.data = {
      tabela: null,
      marcas: null,
      tipo  : 1
    };

    $scope.results = {
      carro   : [],
      moto    : [],
      caminhao: []
    };

    $scope.totals = {
      carro   : { marcas: 0, modelos: 0, veiculos: 0 },
      moto    : { marcas: 0, modelos: 0, veiculos: 0 },
      caminhao: { marcas: 0, modelos: 0, veiculos: 0 }
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
          //console.log($scope.options.tabelas);
          $scope.options.tabelas = response.results;
        })
        .catch(function ( error ) {
          console.log(error);
          $scope.onError( error );
        });
    };

    $scope.doExtractMarcas = function doExtractMarcas() {

      $scope.totals = {
        carro   : { marcas: 0, modelos: 0, veiculos: 0 },
        moto    : { marcas: 0, modelos: 0, veiculos: 0 },
        caminhao: { marcas: 0, modelos: 0, veiculos: 0 }
      };

      var params = $scope.data;
      params.action ='extract_marcas';
      $scope.extracting = true;
      var count = 0;
      ResourceModel.get( params )
        .$promise
        .then(function ( response ) {
          var marcas = response.results;
          switch($scope.data.tipo) {
            case 1:
              $scope.results.carro       = marcas;
              $scope.totals.carro.marcas = marcas.length;
              break;
            case 2:
              $scope.results.moto       = marcas;
              $scope.totals.moto.marcas = marcas.length;
              break;
            case 3:
              $scope.results.caminhao    = marcas;
              $scope.totals.carro.marcas = marcas.length;
              break;
          }
          $scope.setProgress(true, 'info', marcas.length, 0, 'Extraindo modelos de marcas');

          async.eachSeries(marcas, function(marca, callbackMarcas) {
            count++;
            $scope.updateProgress(count, 'Extraindo modelos/veículos da marca ' + marca.lbl);
            marca.status = 'run';

            // TODO
            $scope.doExtractModelos( marca , callbackMarcas );

          }, function ( error ) {
            $scope.onError( error );
          });

        })
        .then(function ( response ) {
          $scope.updateProgress(count, 'Todos modelos/veículos extraídos!');
        })
        .catch(function ( error ) {
          $scope.onError( error );
        });
    };

    $scope.doExtractModelos = function doExtractModelos( marca, callbackMarcas ) {

      if (!$scope.extracting) {
        return;
      }

      var params = $scope.data;
      params.action ='extract_modelos_veiculos';
      params.marca  = marca.id;

      return ResourceModel.get( params )
        .$promise
        .then(function ( response ) {
          marca.modelos = response.results;
          marca.veiculosTotal = response.veiculosTotal;
          switch($scope.data.tipo) {
            case 1:
              $scope.totals.carro.modelos = $scope.totals.carro.modelos + marca.modelos.length;
              $scope.totals.carro.veiculos = $scope.totals.carro.veiculos + marca.veiculosTotal;
              break;
            case 2:
              $scope.totals.moto.modelos  = $scope.totals.moto.modelos + marca.modelos.length;
              $scope.totals.moto.veiculos = $scope.totals.moto.veiculos + marca.veiculosTotal;
              break;
            case 3:
              $scope.totals.caminhao.modelos = $scope.totals.caminhao.modelos + marca.modelos.length;
              $scope.totals.moto.veiculos = $scope.totals.moto.veiculos + marca.veiculosTotal;
              break;
          }
        })
        .then(function () {
          callbackMarcas();
          marca.status = 'ok';
        }).catch(function ( error ) {
          $scope.onError( error );
        });
    };

    //$scope.doExtractVeiculos = function doExtractVeiculos(marca, callbackMarcas) {
    //
    //  //async.eachSeries(/* ... */, function(/* ... */, cb1) {
    //  //  async.eachSeries(/* ... */, function(/* ... */, cb2) {
    //  //    async.eachSeries(/* ... */, function(/* ... */, cb3) {
    //  //      cb3(/* ... */);
    //  //    }, cb2);
    //  //  }, cb1);
    //  //}, callback);
    //
    //  if (!$scope.extracting) {
    //    return;
    //  }
    //
    //  var params    = $scope.data;
    //  params.marca  = marca.id;
    //  params.action ='extract_veiculos';
    //
    //  async.eachSeries(marca.modelos, function(modelo, callbackModelos) {
    //
    //    params.modelo = modelo.id;
    //    $scope.updateProgressMsg('Extraindo veículos do modelo ' + modelo.lbl + ' / marca ' + marca.lbl);
    //
    //    //switch($scope.data.tipo) {
    //    //  case 1:
    //    //    $scope.totals.carro.veiculos = $scope.totals.carro.veiculos + 10;
    //    //    break;
    //    //  case 2:
    //    //    $scope.totals.moto.veiculos  = $scope.totals.moto.veiculos + 10;
    //    //    break;
    //    //  case 3:
    //    //    $scope.totals.caminhao.veiculos = $scope.totals.caminhao.veiculos + 10;
    //    //    break;
    //    //}
    //    //eachCb();
    //
    //    ResourceModel.get( params )
    //      .$promise
    //      .then(function ( response ) {
    //        marca.veiculosTotal = response.results.length;
    //        switch($scope.data.tipo) {
    //          case 1:
    //            $scope.totals.carro.veiculos = $scope.totals.carro.veiculos + marca.veiculosTotal;
    //            break;
    //          case 2:
    //            $scope.totals.moto.veiculos  = $scope.totals.moto.veiculos + marca.veiculosTotal;
    //            break;
    //          case 3:
    //            $scope.totals.caminhao.veiculos = $scope.totals.caminhao.veiculos + marca.veiculosTotal;
    //            break;
    //        }
    //      })
    //      .then(function () {
    //        callbackModelos();
    //      })
    //      .catch(function ( error ) {
    //        $scope.onError( error );
    //      });
    //
    //  }, function( error ){
    //    $scope.onError( error )
    //  });
    //
    //  callbackMarcas();
    //
    //  // TODO
    //  marca.status = 'ok';
    //
    //  return true;
    //};

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

      modalInstance.result.then(function ( response ) {

      }, function ( response ) {

      });

    };

    $scope.setProgress = function ( active, type, max, val, msg ) {
      $scope.progress = {
        'active': active,
        'type'  : type,
        'max'   : max,
        'val'   : val,
        'msg'   : msg
      };
    };

    $scope.updateProgress = function ( val, msg ) {
      if (typeof msg !== 'undefined') {
        $scope.progress.msg = msg;
      }
      $scope.progress.val = val;
      $scope.progress.bar = $scope.progress.val + ' de ' + $scope.progress.max;
      $scope.progress.prc = Math.floor($scope.progress.val / $scope.progress.max * 100);
    };

    $scope.updateProgressMsg = function ( msg ) {
      $scope.progress.msg = msg;
    };

    $scope.toggleProgress = function ( bool ) {
      $scope.active = (typeof bool !== 'undefined') ? bool : !$scope.active;
    };

    $scope._init();

  }
]);
