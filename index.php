<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Creative - Start Bootstrap Theme</title>
    
    <!-- angular/jquery -->
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.5/angular.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.4.6/angular-animate.js"></script>
    <script src="//angular-ui.github.io/bootstrap/ui-bootstrap-tpls-0.14.0.js"></script>
    <script src="background.js"></script>
    <link rel="stylesheet" href="css/bootstrap.css" type="text/css">
    <link rel="stylesheet" href="background.css" type="text/css">

    <!-- Custom Fonts -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css" type="text/css">
    
    <!-- Plugin CSS -->
    <link rel="stylesheet" href="css/animate.min.css" type="text/css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/creative.css" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body id="page-top" ng-app="myApp" ng-controller="myCtrl" resize>
    
    <?php
        readfile("registration.html");
        readfile("login.html");
    ?>

    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">

        
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand page-scroll" href="#page-top">Wallpapers</a>
                        

            </div>
            <ul class="nav navbar-nav navbar-right">
                <li ng-show="user.loggedIn">
                    <a class="page-scroll"  ng-click='requeue()' ng-show="enlarged">Requeue</a>
                </li>
                
                <li ng-show="user.loggedIn">
                    <a ng-cloak ng-show="image.status <= 0" class="page-scroll" ng-click="save()">Save</a>
                    <a ng-cloak ng-show="image.status > 0" class="page-scroll">Saved</a>
                
                <li ng-show="user.loggedIn">
                    <a ng-cloak  ng-show="image.status >= 0" class="page-scroll" ng-click="delete()">Remove</a>
                    <a ng-cloak ng-show="image.status < 0" class="page-scroll">Removed</a>
                </li>
                
                <li ng-show="user.loggedIn">
                    <a ng-cloak class="page-scroll"  ng-click="exitEnlarged()" ng-show="enlarged">Exit</a>
                </li>
                
                <li>
                    <div ng-cloak ng-show="queue || enlarged" class="dropdown">
                      <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">Download
                      <span class="caret"></span></button>
                      <ul class="dropdown-menu">
                        <li><a ng-repeat="res in resolutions" href="{{res.path}}" download="" target="_blank">{{res.width}}x{{res.height}}</a></li>
                      </ul>
                    </div>
                </li>
                
                <li>
                    <div class="dropdown">
                      <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">Queues
                      <span class="caret"></span></button>
                      <ul class="dropdown-menu">
                        <li><a ng-click="queueView(); getBackgrounds({sort: 'random'});">Random</a></li>
                        <li><a ng-click="queueView(); getBackgrounds({sort: 'new'});">New</a></li>
                        <li><a ng-click="queueView(); getBackgrounds({sort: 'popular'});">Popular</a></li>
                        <li><a ng-click="queueView(); getBackgrounds({sort: 'unpopular'});">Unpopular</a></li>
                      </ul>
                    </div>
                </li>
                
                <li ng-show="user.loggedIn">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
                            {{user.name}}
                        <span class="caret"></span></button>
                      <ul class="dropdown-menu">
                        <li><a ng-click="savedView(); getBackgrounds({sort: 'saved'});">Saved</a></li>
                        <li><a ng-click="deletedView(); getBackgrounds({sort: 'deleted'});">Deleted</a></li>
                        <li><a ng-click="logout()">Log Out</a></li>
                      </ul>
                    </div>
                </li>
                
                <li ng-show="!user.loggedIn"><a ng-click="register()">Register</a></li>
                <li ng-show="!user.loggedIn"><a ng-click="login()">Login</a></li>
            </ul>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <header ng-show="queue" style="background-image: url({{image.path}}); background-size: {{width}}px {{height}}px; background-repeat: no-repeat; background-position: center;">
        <a href="" class="left-nav col-md-1" ng-click="previous()"></a>
        <div class="col-md-10"></div>
        <a href="" class="right-nav col-md-1" ng-click="next()"></a>
    </header>
    
    <div class = "container tile-view" ng-show="!queue" style="width: {{ numTileCols * tileWidth }}px">
        <div class="row" ng-repeat="row in rows">
            <div class="col-md-{{colsize}}" ng-repeat="img in row" >
                <img class="tile" src="{{img.path}}" 
                    style="width: {{img.tileWidth}}px; 
                    height: {{img.tileHeight}}px;
                    margin-top: {{5 + (tileHeight - img.tileHeight) / 2}}px;
                    margin-bottom: {{(tileHeight - img.tileHeight) / 2}}px;
                    margin-left: {{5 + (tileWidth - img.tileWidth) / 2}}px;
                    margin-right: {{5 + (tileWidth - img.tileWidth) / 2}}px;
                    cursor: pointer;" 
                    ng-click="enlarged = true; enlarge(img.id)"
                    />
            </div>
    </div>
    
    
    <!-- Define Responsive Image Tiles: For Best Effect Use Square Images and a lot more Images -->
<!--   <div class="gridWrapper">
    <div class="tile" ng-repeat="obj in objects" style="background-image: url({{obj[0].path}}); background-size: 320px 180px; background-repeat: no-repeat; background-position: center;">
      <div class="tileInner">
        <img src="{{obj[0].path}}" />
      </div>
    </div>
</div>-->
    <!-- jQuery -->
<!--    <script src="js/jquery.js"></script>-->

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
<!--    <script src="js/jquery.easing.min.js"></script>
    <script src="js/jquery.fittext.js"></script>
    <script src="js/wow.min.js"></script>-->

    <!-- <script src="js/creative.js"></script> -->
    

</body>

</html>
