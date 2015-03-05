FipeCrawlerApp.controller('ModalInstanceCtrl', [
  '$scope', '$modalInstance', 'data',
  function ($scope, $modalInstance, data) {
    "use strict";

    $scope.title = data.title;
    $scope.msg   = data.msg;

    $scope.ok = function () {
      $modalInstance.close(true);
    };

    $scope.cancel = function () {
      $modalInstance.dismiss(false);
    };

  }
]);
