
<!--Registration and login modals-->
<?php
    readfile("registration.html");
    readfile("login.html");
?>

<!--Navigation-->
<nav id="mainNav" class="navbar navbar-default navbar-fixed-top" ng-show="loaded">
    <div class="container-fluid">

        <!--Site Name-->
        <div class="navbar-header">
            <a class="navbar-brand page-scroll" href="#page-top">Wallpapers</a>
        </div>

        <!--Site Pages-->
        <ul class="nav navbar-nav navbar-right">

            <!--Exit-->
            <li ng-show="user.loggedIn && view == 'user_imgs' && enlarged">
                <a ng-cloak class="page-scroll"  ng-click="exitEnlarged()" ng-show="enlarged">Exit</a>
            </li>

            <!--Requeue-->
            <li ng-show="user.loggedIn && view === 'user_imgs' && enlarged">
                <a class="page-scroll"  ng-click='requeue()' ng-show="enlarged">Requeue</a>
            </li>

            <!--Save and Remove-->
            <li ng-show="user.loggedIn && (view !== 'user_imgs' || enlarged)">
                <a ng-cloak ng-show="image.status <= 0" class="page-scroll" ng-click="save()">Save</a>
                <a ng-cloak ng-show="image.status > 0" class="page-scroll">Saved</a>

            <li ng-show="user.loggedIn && (view !== 'user_imgs' || enlarged)">
                <a ng-cloak  ng-show="image.status >= 0" class="page-scroll" ng-click="delete()">Remove</a>
                <a ng-cloak ng-show="image.status < 0" class="page-scroll">Removed</a>
            </li>

            <!--Download-->
            <li>
                <div ng-show="enlarged" class="dropdown">
                  <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">Download
                  <span class="caret"></span></button>
                  <ul class="dropdown-menu">
                    <li><a ng-repeat="res in resolutions" href="{{res.path}}" download="" target="_blank">{{res.width}}x{{res.height}}</a></li>
                  </ul>
                </div>
            </li>

            <!--Queues-->
            <li>
                <div class="dropdown">
                  <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">Queues
                  <span class="caret"></span></button>
                  <ul class="dropdown-menu">
                    <li><a ng-click="changeView('queue', true); getBackgrounds({sort: 'random'});">Random</a></li>
                    <li><a ng-click="changeView('queue', true); getBackgrounds({sort: 'new'});">New</a></li>
                    <li><a ng-click="changeView('queue', true); getBackgrounds({sort: 'popular'});">Popular</a></li>
                    <li><a ng-click="changeView('queue', true); getBackgrounds({sort: 'unpopular'});">Unpopular</a></li>
                  </ul>
                </div>
            </li>

            <!--User-->
            <li ng-show="user.loggedIn">
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
                        {{user.name}}
                    <span class="caret"></span></button>
                  <ul class="dropdown-menu">
                    <li><a ng-click="changeView('user_imgs'); getBackgrounds({sort: 'saved'});">Saved</a></li>
                    <li><a ng-click="changeView('user_imgs'); getBackgrounds({sort: 'deleted'});">Deleted</a></li>
                    <li><a ng-click="logout()">Log Out</a></li>
                  </ul>
                </div>
            </li>

            <!--Registration and login modals-->
            <li ng-show="!user.loggedIn"><a ng-click="register()">Register</a></li>
            <li ng-show="!user.loggedIn"><a ng-click="login()">Login</a></li>

        </ul>
    </div>
</nav>

<!--Queue View-->
<header ng-show="view === 'queue' || enlarged" style="background-image: url({{image.path}}); background-size: {{width}}px {{height}}px; background-repeat: no-repeat; background-position: center;">
    <a href="" class="left-nav col-md-1" ng-click="previous()"></a>
    <div class="col-md-10"></div>
    <a href="" class="right-nav col-md-1" ng-click="next()"></a>
</header>

<!--Tile View-->
<div class = "container tile-view" ng-show="view === 'user_imgs' && !enlarged" style="width: {{ numTileCols * tileWidth }}px">
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
</div>