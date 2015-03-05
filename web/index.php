<?php require_once(__DIR__ . '/../config/config.php'); ?><!DOCTYPE html>
<html lang="en" data-ng-app="FipeCrawlerApp">
<head>
    <meta charset="utf-8">
    <title>Fipe Crawler</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="deps/html5shiv/dist/html5shiv.min.js"></script>
    <![endif]-->
    <script>
        var BASE_URL = '<?php echo $baseUrl ?>';
    </script>
</head>
<body>

    <header class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <nav>
                    <ul class="nav navbar-nav">
                        <a href="#/" role="button" class="navbar-brand">
                            FipeCrawler
                        </a>
                        <li><a href="<?php echo $baseUrl ?>/#">Sobre</a></li>
                        <li><a href="<?php echo $baseUrl ?>/#/extract"><i class="fa fa-play"></i> Executar</a></li>
                        <li><a href="<?php echo $baseUrl ?>/#/csv"><i class="fa fa-table"></i> Baixar CSV</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div data-ng-view=""></div>

    <script src="deps/angular/angular.js"></script>
    <script src="deps/angular-route/angular-route.js"></script>
    <script src="deps/angular-resource/angular-resource.js"></script>
    <script src="deps/angular-bootstrap/ui-bootstrap.js"></script>
    <script src="deps/angular-bootstrap/ui-bootstrap-tpls.js"></script>
    <script src="app/FipeCrawlerApp.js"></script>
    <script src="app/errors/ErrorController.js"></script>
    <script src="app/main/MainController.js"></script>
    <script src="app/extract/ExtractController.js"></script>
    <script src="app/extract/ResourceModel.js"></script>
    <script src="app/modal/ModalController.js"></script>

</body>
</html>
