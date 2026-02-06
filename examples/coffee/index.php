<?php

require_once(__DIR__ . "/../vendor/autoload.php");
session_start();

use MokaPHP\Endpoint;

// MODEL

$coffee = $_SESSION['coffee'] ?? [

    'type' => '',
    'level' => 0

];

// ENDPOINT /coffee

$e = new Endpoint("../config.ini");

// GET

$e->get(function($headers, $get){

    global $coffee;

    $info = $get['info'] ?? null;

    return ($info && isset($coffee[$info]))
        ? [ $info => $coffee[$info] ]
        : $coffee;

});

// POST

$e->post(function($headers, $get, $body){

    global $coffee;

    if (
        !is_array($body) ||
        array_keys($body) !== array_keys($coffee)
    ) throw new InvalidCoffeeStructureException(); 

    $_SESSION['coffee'] = $body;

    return [

        'msg' => 'Moka filled correctly!',
        ...$_SESSION['coffee']

    ];

});

// PATCH

$e->patch(function($headers, $get, $body){

    global $coffee;

    if (
        !is_array($body) ||
        array_keys($body) !== array_keys($coffee)
    ) throw new InvalidCoffeeStructureException(); 

    $_SESSION['coffee'] = $body;

    return [

        'msg' => 'Moka refilled correctly!',
        ...$_SESSION['coffee']

    ];

});

// PUT

$e->put(function($headers, $get, $body){

    global $coffee;

    if (
        !isset($headers['X-Coffee-Type']) ||
        $headers['X-Coffee-Type'] !== $coffee['type']
    ) throw new InvalidCoffeeTypeException();

    if (!isset($body['fill']))
        throw new InvalidPouringTechniqueException();

    $fill = $body['fill'];

    $_SESSION['coffee']['level'] = $fill
        ? $coffee['level'] + 10
        : $coffee['level'] - 10;

    return [

        'msg' => ($fill ? 'Coffee added' : 'Coffee poured'),
        'new_level' => $_SESSION['coffee']['level']

    ];

});

// DELETE

$e->delete(function($headers, $get, $body){

    global $coffee;

    if (
        !isset($headers['X-Coffee-Type']) ||
        $headers['X-Coffee-Type'] !== $coffee['type']
    ) throw new InvalidCoffeeTypeException();

    $_SESSION['coffee'] = [

        'type' => '',
        'level' => 0

    ];

    return [ 'msg' => 'Moka emptied' ];

});

// HEAD

$e->head(function($headers, $get){

    // pff... just test the 204 status code

});

// OPTIONS

$e->options(function($headers, $get){

    return [ 'msg' => 'Good to go!' ];

});

$e->dispatch();

?>