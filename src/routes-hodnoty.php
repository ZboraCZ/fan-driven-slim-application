<?php

use Slim\Http\Request;
use Slim\Http\Response;

//Application routes inside /auth group

$app->get('/', function (Request $request, Response $response, array $args) {
    return $response->withHeader('Location', $this->router->pathFor('index')); //display GET route /hodnoty when only / in URL set
});


$app->get('/hodnoty', function (Request $request, Response $response, array $args) {
    $posledni_hodnota = $request->getQueryParam('posledni');
    if (empty($posledni_hodnota) || !isset($posledni_hodnota) || $posledni_hodnota == ''){
        try {
            $stmt = $this->db->query('SELECT * FROM hodnoty
                    ORDER BY id 
                    DESC limit 10');
            $tplVars['hodnoty'] = $stmt->fetchAll(); //asociativni pole zaznamu
            foreach ($tplVars['hodnoty'] as $radek) {
                $date = new DateTime($radek['time']);
            }
            //return $response->withJson($stmt->fetchAll());
            return $this->view->render($response, 'mereni.latte', $tplVars);

        } catch(Exception $e) {
            //chyba na strane serveru
            return $response->withJson([
                'error' => $e->getMessage()
            ], 500);
        }
    }else{ //parametr posledni hodnoty neni prazdny, vratime json pro Javascript ajax
        try {
            $stmt = $this->db->query('SELECT * FROM hodnoty
                    ORDER BY id 
                    DESC limit 1');
            $tplVars['hodnota'] = $stmt->fetch();
            //return $response->withJson($stmt->fetchAll());
            return $response->withJson($tplVars);

        } catch(Exception $e) {
            //chyba na strane serveru
            return $response->withJson([
                'error' => $e->getMessage()
            ], 500);
        }
    }

})->setName('index');

//also moved outside from group /auth for accessibility of Raspberry Pi requests
$app->post('/hodnoty', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    //var_dump($data);
    try {
        if (isset($data['temperature']) && isset($data['humidity']) ){
            $stmt = $this->db->prepare('INSERT INTO hodnoty
			(time, temperature, humidity)
			 VALUES
			(NOW(), :t, :h)');
            $stmt->bindValue(':t', $data['temperature']);
            $stmt->bindValue(':h', $data['humidity']);
            $stmt->execute();

            return $response->withHeader('Location', $this->router->pathFor('index'));
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
})->setName('insert-values');

//GET for retreiving actual humidity and temperature level starting fan
$app->get('/vlhkost-teplota', function (Request $request, Response $response, array $args) {
    try {
        $sql = "SELECT * FROM fan_starting_values WHERE name = 'humidity'";
        $stmt = $this->db->query($sql);
        $tplVars['hodnota'] = $stmt->fetch(); //zaznam s vlhkosti
        return $response->withJson($tplVars['hodnota']);

    } catch(Exception $e) {
        //chyba na strane serveru
        return $response->withJson([
            'error' => $e->getMessage()
        ], 500);
    }
});

//POST for updating actual humidity and temperature level starting fan
$app->post('/vlhkost-teplota', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    try {
        if (isset($data['newStartFanHumidity'])){
            $stmt = $this->db->prepare("UPDATE fan_starting_values
			SET value = :h WHERE name = 'humidity'");
            $stmt->bindValue(':h', $data['newStartFanHumidity']);
            $stmt->execute();

            return $response->withHeader('Location', $this->router->pathFor('index'));
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
})->setName('vlhkost-teplota');

//GET for index page, where table of newest 10 values is displayed
$app->get('/hodnoty10', function (Request $request, Response $response, array $args) {
    try {
        $stmt = $this->db->query('SELECT * FROM hodnoty
                    ORDER BY id 
                    DESC limit 10');
        $tplVars['hodnoty'] = $stmt->fetchAll(); //asociativni pole zaznamu
        foreach ($tplVars['hodnoty'] as $radek) {
            $date = new DateTime($radek['time']);
        }
        return $response->withJson($tplVars['hodnoty']);

    } catch(Exception $e) {
        //chyba na strane serveru
        return $response->withJson([
            'error' => $e->getMessage()
        ], 500);
    }
});

