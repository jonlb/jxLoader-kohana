<?php


//add routes here

Route::add('loader','loader', null, 'admin')
    ->defaults(array(
        'controller' => 'loader',
        'action' => 'index'
    ));
