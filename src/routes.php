<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function (Request $request, Response $response, $args){
    if (!empty($_SESSION['user'])){
        return $response->withHeader('Location', $this->router->pathFor('index'));
    } else {
        return $response->withHeader('Location', $this->router->pathFor('login'));
    }
});

$app->get('/login', function (Request $request, Response $response, $args) {
    return $this->view->render($response, 'login.latte');
})->setName('login');


$app->post('/login', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    if (!empty($data['login']) && !empty($data['pass'])) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM account WHERE login = :l');
            $stmt->bindValue(':l', $data['login']);
            $stmt->execute();

            $user = $stmt->fetch();
            if ($user) {
                if (password_verify($data['pass'], $user['password'])) {
                    $_SESSION['user'] = $user;
                    return $response->withHeader('Location', $this->router->pathFor('index'));
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            exit($e->getMessage());
        }
        return $response->withHeader('Location', $this->router->pathFor('login'));
    }
});


//Routes for authenticated users only
$app->group('/auth', function() use ($app) {

    include('routes-hodnoty.php');

    //=============== POST LOGOUT =====================
    $app->post('/logout', function (Request $request, Response $response, $args) {
        session_destroy();
        return $response->withHeader('Location', $this->router->pathFor('login'));
    })->setName('logout');

})->add(function (Request $request, Response $response, $next){
   if (!empty($_SESSION['user'])){
       return $next($request, $response);
   } else {
       return $response->withHeader('Location', $this->router->pathFor('login'));
   }
});


//////////////////////////// ROUTY POUZE PRO Raspberry Pi  projekt předmětu Praxe vývojových technik Kybernetiky //////////////////////////////
$app->get('/hodnoty', function (Request $request, Response $response, array $args) {
    try {
        $stmt = $this->db->query('SELECT * FROM hodnoty
                    ORDER BY id 
                    DESC limit 10');
        return $response->withJson($stmt->fetchAll());
    } catch (Exception $e) {
        //chyba na strane serveru
        return $response->withJson([
            'error' => $e->getMessage()
        ], 500);
    }
});


$app->post('/hodnoty', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    try {
        if (isset($data['temperature']) && isset($data['humidity']) ){
            $stmt = $this->db->prepare('INSERT INTO hodnoty
			(time, temperature, humidity)
			 VALUES
			(NOW(), :t, :h)');
            $stmt->bindValue(':t', $data['temperature']);
            $stmt->bindValue(':h', $data['humidity']);
            $stmt->execute();

            return $response->withStatus(201);
        } else {
            //chyba na strane klienta
            return $response->withStatus(400);
        }

    } catch(Exception $e) {
        //chyba na strane serveru
        return $response->withJson([
            'error' => $e->getMessage()
        ], 500);
    }
});