<?php


/**
 * Define the route(s) we need in this file
 */
Route::add('loader','loader/', null, 'admin')
    ->defaults(array(
        'controller' => 'loader',
        'action' => 'index'
    ));
