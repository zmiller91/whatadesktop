
<?php 
    require_once("../app/ApplicationAutoloader.php");
    
    // Format the request path and break it into parts. For debugging purposes, 
    // remove the public directory portion of the URL, if it exists.
    $publicDirectory = trim($_SERVER["PUBLIC_DIR"], "/");
    $requestURI = ltrim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/"); 
    $uri = preg_replace("/^" . preg_quote($publicDirectory, "/") . "/", "", $requestURI);
    $uri =  trim($uri, "/");
    
    // If it's not an api call, then return main.html
    $apiRegex = "/^" . preg_quote("api/", "/") . "/";
    if(preg_match($apiRegex, $uri) === 0)
    {
        readfile('html/main.html');
        return;
    }
    
    // If the service doesnt exist, then its a 404
    $class = preg_replace($apiRegex, "", $uri);
    if(!class_exists($class))
    {
        http_response_code(404);
        return;
    }
    
    // Assemble all the input
    $post = json_decode(file_get_contents('php://input'),true);
    $input = array_merge_recursive( 
            !empty($post) ? $post : array(), 
            !empty($_GET) ? $_GET : array());
    
    // Run the service
    $service = new $class($_SERVER["REQUEST_METHOD"], $input);
    $service->run();