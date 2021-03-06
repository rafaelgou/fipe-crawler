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
                        <li><a href="#/sobre">Sobre</a></li>
                        <li><a href="#/extract"><i class="fa fa-play"></i> Executar</a></li>
                        <li><a href="#/csv"><i class="fa fa-table"></i> Baixar CSV</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div data-ng-view=""></div>

    <script src="js/app.js"></script>

</body>
</html>
