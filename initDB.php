<?php

//RedBean Funktionaliäten einbinden

require 'rb.php';

R::setup('mysql:host=localhost;dbname=to_do_db', 'root', '');

// Neue Person anlegen
$user = R::dispense('person');


//gewünschte Attribute des Users
$user->name = "Perschke";
$user->password = "12345";

//Neue Liste anlegen

$todolist = R::dispense('todolist');
$todolist->name = "Hausaufgaben für die Schule";
$todolist2 = R::dispense('todolist');
$todolist2->name = "Reise organisieren für Sommer 2020";

//Neue Aufgabe hinzufügen

$todo = R::dispense('todo');
$todo2 = R::dispense('todo');
$todo3 = R::dispense('todo');
$todo4 = R::dispense('todo');
$todo5 = R::dispense('todo');
$todo6 = R::dispense('todo');

//Atrribute einer Aufgabe
$todo->titel = "Mathehausaufgaben machen";
$todo->status = "Offen"; //insgesamt 5 Status
$todo->beschreibung = "Seite 245 Nr. 1-4";
$todo->gewicht = "4"; //1 bis 5 
$todo->endzeitpunkt = "28.06.2020";

$todo2->titel = "Englischhausaufgaben machen";
$todo2->status = "erledigt"; //insgesamt 5 Status
$todo2->beschreibung = "S.76 Nr. 1 und 2";
$todo2->gewicht = "5"; //1 bis 5 
$todo2->endzeitpunkt = "28.08.2020";

$todo3->titel = "Vokabeln lernen für Spanisch";
$todo3->status = "in Bearbeitung"; //insgesamt 5 Status
$todo3->beschreibung = "Buch S. 230-235 Kapitel: La historia de España";
$todo3->gewicht = "3"; //1 bis 5 
$todo3->endzeitpunkt = "16.07.2020";

$todo4->titel = "Flugticket kaufen";
$todo4->status = "offen"; //insgesamt 5 Status
$todo4->beschreibung = "Flug am 24.10.2020 nach LAX";
$todo4->gewicht = "3"; //1 bis 5 
$todo4->endzeitpunkt = "20.09.2020";

$todo5->titel = "Auto mieten für 2 Wochen";
$todo5->status = "abgebrochen"; //insgesamt 5 Status
$todo5->beschreibung = "SIXXT";
$todo5->gewicht = "1"; //1 bis 5 
$todo5->endzeitpunkt = "10.10.2020";

$todo6->titel = "Hotelzimmer buchen";
$todo6->status = "offen"; //insgesamt 5 Status
$todo6->beschreibung = "Hilton Hotel in LAX";
$todo6->gewicht = "2"; //1 bis 5 
$todo6->endzeitpunkt = "20.10.2020";



//Aufgabe einer Liste zuordnen (1:n)

$todolist->xownTodoList[] = $todo;
$todolist->xownTodoList[] = $todo2;
$todolist->xownTodoList[] = $todo3;

$todolist2->xownTodoList[] = $todo4;
$todolist2->xownTodoList[] = $todo5;
$todolist2->xownTodoList[] = $todo6;

//Person einer Liste zuordnen (1:n)

$todolist->person = $user;
$todolist2->person = $user;

$id = R::store($todolist);
$id2 = R::store($todolist2);
$todolist = R::load('todolist', $id); 
$todolist2 = R::load('todolist2', $id2); 

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
echo "Name: " . $todolist->name;

R::close();
?>