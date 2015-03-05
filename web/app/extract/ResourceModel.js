FipeCrawlerApp.provider('ResourceModel', function() {

  this.$get = ['$resource', function($resource) {
    var model = $resource('resource.php?action=:action&tabela=:tabela&tipo=:tipo', {}, {
      get: {
        isArray: false,
        method: 'get'
      }
    });

    return model;
  }];
});