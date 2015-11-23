<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once __DIR__.'/vendor/autoload.php';

$app = new Application();

$app['debug'] = true;

$app->post('/login', function (Request $request) {
    $username = $request->get('username');
    
    if ($username === "jinhua") {
        return new JsonResponse(
                array(
                    "status" => "success"
                    )
                );
    }
    return new JsonResponse(
            array(
                "status" => "fail", 
                'message' => 'incorrect credentials'
                )
            );
});



$app->run();
