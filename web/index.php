<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../config.php';
require '../twig.ext.php';

$container = new \Slim\Container();
$app = new \Slim\App($container);

// Set headers for all requests
$app->add(function ($request, $response, $next) {
    $CORS = "default-src https:; script-src 'none'; object-src 'self'; style-src 'self'; img-src 'self'; media-src 'self'; child-src 'none'; font-src 'self'; connect-src 'none'";

    $response = $next($request, $response);
    return $response
            ->withHeader('Content-Security-Policy', $CORS)
            ->withHeader('X-Content-Security-Policy', $CORS)
            ->withHeader('X-WebKit-CSP', $CORS)
            ->withHeader('X-Follow-The-White-Rabbit', "https://www.youtube.com/watch?v=6GggY4TEYbk");
});

// Register component on container
$container['view'] = function ($container) {
    global $CONFIG;
    $view = new \Slim\Views\Twig("{$CONFIG['BASEDIR']}/templates", [
        'cache' => ($CONFIG['DEV'] ? false : "{$CONFIG['BASEDIR']}/twig_cache")
    ]);
    $twig = $view->getEnvironment();

    // Add extensions
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($container->get('router'), $uri->withPort(null)));

    $twig->addExtension(new AppExtension());
    return $view;
};

// Remove slashes
$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        $uri = $uri->withPath(substr($path, 0, -1))->withPort(null);
        if ($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        } else {
            return $next($request->withUri($uri), $response);
        }
    }
    return $next($request, $response);
});

// Redirect to /enter on 404
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $response->withRedirect("/", 301);
    };
};

$app->get('/', function ($request, $response, $args) {
    // Support for old urls
    if (isset($_GET['game'])) {
        if (is_numeric($_GET['game'])) {
            $gameid = intval($_GET['game']);
            return $response->withRedirect("https://goggames.goodolddownloads.com/?game=$gameid", 301);
        }
    }
    return $response->withRedirect("/enter", 301);
});

$app->get('/enter', function ($request, $response, $args) {
    return $this->view->render($response, 'index.twig');
});
$app->run();