<?php

//RedBean Funktionaliäten einbinden

require 'rb.php';

R::setup('mysql:host=localhost;dbname=to_do_db', 'root', '');

// Neue Person anlegen
$user = R::dispense('person');

//gewünschte Attribute des Users
$user->name = "Testuser";
$user->password = "123polizei";

//Neue Liste anlegen

$todolist = R::dispense('todolist');
$todolist->name = "Hausaufgaben für die Schule";

//Neue Aufgabe hinzufügen

$todo = R::dispense('todo');

//Atrribute einer Aufgabe
$todo->titel = "Mathehausaufgaben machen";
$todo->status = "Offen"; //insgesamt 5 Status
$todo->beschreibung = "Seite 245 Nr. 1-4";
$todo->gewicht = "4"; //1 bis 5 
$todo->zeitpunkt = "228.06.2020";



//Aufgabe einer Liste zuordnen (1:n)

$todolist->xownTodoList[] = $todo;

//Person einer Liste zuordnen (1:n)

$todolist->person = $user;

$id = R::store($todolist);
$todolist = R::load('todolist', $id); 

//Ausgabe

echo "<h3>To-Do</h3>";
echo "Titel: " . $todo->titel . "<br>";
echo "Status: " . $todo->status . "<br>";
echo "Beschreibung: " . $todo->beschreibung . "<br>";
echo "Gewicht: " . $todo->gewicht . "<br>";
echo "Zeitpunkt: " . $todo->zeitpunkt . "<br>";

echo "--------------";

echo "<h3>Person</h3>";
$user = $todolist->person;
echo "Name: " . $user->name . "<br>";
echo "Passwort: " . $user->password . "<br>";


echo "--------------";

echo "<h3>Liste</h3>";
// foreach($todolist->xownTodoList as $v) {
//     echo "Name: " . $v->name . "<br>";
// }
echo "Name: " . $todolist->name;

R::close();
?>