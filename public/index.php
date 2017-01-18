<?php
require '../required/flight/Flight.php';
# config

Flight::set('flight.views.path', '../views/');
Flight::set('filename', '../storage/temperature.txt');
Flight::set('secret', "abf3fc0750a104077349");
# routes

Flight::route('/', 'renderHome');
Flight::route('POST /temp', 'postTemp');
Flight::route('GET /temp', 'getTemp');
# functions

function postTemp() {
	$request = Flight::request();
	if($request->data->secret != Flight::get('secret')){
		return Flight::json(array('success' => false));
	}
	writeTemp($request->data->temp);
    return Flight::json(array('temp' => readTemp()));
}

function getTemp() {
	$request = Flight::request();
	$temp = readTemp();
	if(strtolower($request->query->unit) == 'f') {
		$temp = celsiusToFarenheight($temp);
	}
	return Flight::json(array('temp' => $temp));
}

function renderHome() {
	Flight::render('partials/temperature', array('name' => 'Jamal'), 'body_content');
    Flight::render('layouts/main', array('data' => array('temp' => readTemp()) ));
}

function writeTemp($temp) {
	# writes temperature to file - this would connect to the db ideally
	if(isset($temp)) {
		$fp = fopen(Flight::get('filename'), 'w');
		if($fp == false){
			return false;
		}
		fwrite($fp, $temp);
		fclose($fp);
	}
}

function readTemp() {
	# reads our temperature from file - this would connect to the db ideally
	$filename = Flight::get('filename');
	$handle = fopen($filename, "r");
	if($handle == false){
		return false;
	}
	$contents = fread($handle, filesize($filename));
	fclose($handle);
	return $contents;
}

function celsiusToFarenheight($temp) {
	# move this to an object for more detailed functions
	return ($temp * 9/5) + 32;
}
# initialize framework

Flight::start();
?>
