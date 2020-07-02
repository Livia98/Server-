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

//Alle Listen mit Aufgaben und Person, der die Liste gehört, werden angezeigt
$app->get('/todolist', function (Request $request, Response $response, $args) {
    $todolist = R::findAll('todolist');
    //foreach($todolist as $todolist) {
    //    $todolist->person;
    //}
    $response->getBody()->write(json_encode(R::exportAll($todolist, TRUE)));
    return $response;
});

//Eine bestimmte Liste abrufen
$app->get('/todolist/{todolistid}', function (Request $request, Response $response, $args) {
	$todolist = R::load('todolist', $args['todolistid']);
	$first = reset( $todolist->ownTodoList );
	$last = end( $todolist->ownTodoList ); 
	$todolist->person;
	$response->getBody()->write(json_encode($todolist));
    return $response;
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

/*Alle Todos zu einer Liste
$app->get('/todo/findBylist/', function (Request $request, Response $response, $args) {
    $todos = R::findAll('todo', 'id=:pid', [':pid'=>$request->getQueryParams()['pid']]);
    foreach($todos as $todo) {
        $todo->todolist;
    }
    $response->getBody()->write(json_encode(R::exportAll( $todos )));
    return $response;

}); */

/*Alle Listen (brauchen wir das überhaupt??)
$app->get('/lists', function (Request $request, Response $response, $args) {
    $todolists = R::findAll('todolist');
    $response->getBody()->write(json_encode(R::exportAll( $todolists )));
    return $response;
});*/

/*Alle Todos zu einer Person unabhängig von Listen
$app->get('/todo/findByperson', function (Request $request, Response $response, $args) {
    $todos = R::findAll('todo', 'id=:pid', [':pid'=>$request->getQueryParams()['pid']]);
    foreach($todos as $todo) {
        $todo->person;
    }
    $response->getBody()->write(json_encode(R::exportAll( $todos )));
    return $response;
});*/

//User anlegen 
$app->post('/newuser/{name}/{password}', function (Request $request, Response $response, $args) {
	$parsedBody = $request->getParsedBody();
	
	$user = R::dispense('person');
	$user->name = $args['name'];
	$user->password = $args['password'];
	
	// $p = R::load('todolist', $parsedBody['todolist_id']);
	// $user->todolist = $p;
    
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

//ToDo anlegen neu
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

//PUT von Marc konstruiert um auf Overview Listenname und Aufgaben zu editieren
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
