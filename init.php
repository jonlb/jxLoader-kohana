<?php


<<<<<<< HEAD
//add routes here

Route::add('loader','loader', null, 'admin')
=======
/**
 * Define the route(s) we need in this file
 */
Route::add('loader','loader/', null, 'admin')
>>>>>>> origin/master
    ->defaults(array(
        'controller' => 'loader',
        'action' => 'index'
    ));
