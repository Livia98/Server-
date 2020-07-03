<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request; 
use Slim\Factory\AppFactory;


require __DIR__ . '/../vendor/autoload.php';
require 'rb.php';

R::setup('mysql:host=localhost;dbname=to_do_db', 'root', '');

$app = AppFactory::create();
$app->setBasePath((function () {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        return $_SERVER['SCRIPT_NAME'];
    }
    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return $scriptDir;
    }
    return '';
})());

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});


//Alle Listen zu einer Person abrufen
$app->get('/todolists/findByPerson/', function (Request $request, Response $response, $args) {
    $todoliste = R::findAll('todolist', 'person_id=:pid', [':pid'=>$request->getQueryParams()['pid']]);
    foreach($todoliste as $todolist) {
        $todolist->person;
    }
    $response->getBody()->write(json_encode(R::exportAll( $todoliste )));
    return $response;

});


//User anlegen 
$app->post('/newuser', function (Request $request, Response $response, $args) {
	$parsedBody = json_decode((string)$request->getBody(), true);
	
	$user = R::dispense('person');
	$user->name = $parsedBody['name'];
	$user->password = $parsedBody['password'];
    
	R::store($user);
	
	$response->getBody()->write(json_encode($user));
    return $response;
});


//User abrufen
$app->get('/users', function (Request $request, Response $response, $args) {
    $users = R::findAll('person');
    $response->getBody()->write(json_encode(R::exportAll($users, TRUE)));
    return $response;
});


//POST
//Neue Liste anlegen
$app->post('/newtodolist/{pid}/{name}', function (Request $request, Response $response, $args) {
	$parsedBody = $request->getParsedBody();
	
	$todolist = R::dispense('todolist');
	$todolist->name = $args['name'];
	$todolist->person_id = $args['pid'];
	R::store($todolist);
	
	$response->getBody()->write(json_encode($todolist));
    return $response;
});

//ToDo anlegen 
$app->post('/newtodo', function (Request $request, Response $response, $args) {
	$parsedBody = json_decode((string)$request->getBody(), true);
	
	$todo = R::dispense('todo');
	$todo->titel= $parsedBody['titel'];
	$todo->status = $parsedBody['status'];
	$todo->beschreibung = $parsedBody['beschreibung'];
    $todo->gewicht = $parsedBody['gewicht'];
    $todo->zeitpunkt = $parsedBody['zeitpunkt'];
    
    $l = R::load('todolist', $parsedBody['todolist_id']);
    $todo->todolist = $l;
	R::store($todo);
	
	$response->getBody()->write(json_encode($todo));
    return $response;
});


//PUT
//Liste ändern 
$app->put('/changetodolist', function (Request $request, Response $response, $args) {
	$parsedBody = json_decode((string)$request->getBody(), true);
	
	$todolist = R::load('todolist', $parsedBody['id']);
	$todolist->name = $parsedBody['name'];
	
	R::store($todolist);
	
	$response->getBody()->write(json_encode($todolist));
    return $response;
});

//PUT 
$app->put('/changetodolists/{id}', function (Request $request, Response $response, $args) {
    $parsedBody = json_decode((string)$request->getBody(), true);
   
    $todolist = R::load('todolist', $args['id']);
   
   
    $todolist->name = $parsedBody['name'];
   
    $todolist->ownTodo = [];
    foreach( $parsedBody['ownTodo'] as $t) {
    $todo = R::dispense('todo');
    $todo->titel = $t['titel'];
    $todo->status = $t['status'];
    $todo->beschreibung = $t['beschreibung'];
    $todo->gewicht = $t['gewicht'];
    $todo->zeitpunkt = $t['zeitpunkt'];
    $todolist->ownTodo[] = $todo;
    }
   
    R::store($todolist);
   
    $response->getBody()->write(json_encode($todolist));
    return $response;
   });
   

//Aufgabe ändern
$app->put('/changetodo/{id}', function (Request $request, Response $response, $args) {
	$parsedBody = json_decode((string)$request->getBody(), true);
	
	$todo = R::load('todo', $parsedBody['id']);
	$todo->titel = $parsedBody['titel'];
	$todo->status = $parsedBody['status'];
	$todo->beschreibung = $parsedBody['beschreibung'];
    $todo->gewicht = $parsedBody['gewicht'];
    $todo->zeitpunkt = $parsedBody['zeitpunkt'];

	R::store($todo);
	
	$response->getBody()->write(json_encode($todo));
    return $response;
});


//DELETE
//eine Liste löschen 
$app->delete('/deletetodolist/{todolistid}', function (Request $request, Response $response, $args) {
	$todolist = R::load('todolist', $args['todolistid']);
	R::trash($todolist);
	$response->getBody()->write(json_encode($todolist));
    return $response;
});

//eine Aufgabe löschen 
$app->delete('/deletetodo/{todoid}', function (Request $request, Response $response, $args) {
	$todo = R::load('todo', $args['todoid']);
	R::trash($todo);
	$response->getBody()->write(json_encode($todo));
    return $response;
});

$app->run(); 
?>
