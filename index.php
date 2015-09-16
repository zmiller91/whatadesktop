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
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.5/angular.min.js"></script>
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    
    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="css/bootstrap.css" type="text/css">

    <!-- Custom Fonts -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="background.css" type="text/css">
    <script src="background.js"></script>
    
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

<body id="page-top" ng-app="myApp" ng-controller="myCtrl">

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
                
                <li>
                    <a  ng-click='save()'>Save</a>
                </li>
                
                <li>
                    <a class="page-scroll" ng-click='delete()'>Remove</a>
                </li>
                
                <li>
                    <div class="dropdown">
                      <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">Download
                      <span class="caret"></span></button>
                      <ul class="dropdown-menu">
                        <li><a ng-repeat="res in resolutions" href="{{res.path}}"  target="_blank">{{res.width}}x{{res.height}}</a></li>
                      </ul>
                    </div>
                </li>
                
                <li>
                    <div class="dropdown">
                      <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">Queues
                      <span class="caret"></span></button>
                      <ul class="dropdown-menu">
                        <li><a>Random</a></li>
                        <li><a>New</a></li>
                        <li><a>Popular</a></li>
                        <li><a>Unpopular</a></li>
                      </ul>
                    </div>
                </li>
                
                <li>
                    <div class="dropdown">
                      <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">User
                      <span class="caret"></span></button>
                      <ul class="dropdown-menu">
                        <li><a>Saved</a></li>
                        <li><a>Deleted</a></li>
                      </ul>
                    </div>
                </li>
            </ul>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <header  style="background-image: url({{image.path}}); background-size: {{width}}px {{height}}px; background-repeat: no-repeat; background-position: center;"  resize>
        <div data-slide="next" href="" class="left-nav col-md-1" ng-click="next()"></div>
        <div class="col-md-10"></div>
        <div data-slide="next" href="" class="right-nav col-md-1" ng-click="previous()"></div>
    </header>

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
