<?php

/**
 * Jelly-based model for settings table
 */
 class Model_Loader extends Jelly_Model {

     public static function initialize(Jelly_Meta $meta){

         $meta->table('loader')
                ->fields(array(
                    'id' => new Field_Primary,
                    'key' => new Field_String,
                    'data' => new Field_Text
                ));
     }

 }
