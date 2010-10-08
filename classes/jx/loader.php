<?php


class Jx_Loader {

    public static function uri(array $options) {
        $uriParts = array();
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $uriParts[] = $key . '[]=' . $val;
                }
            } else {
                $uriParts[] = $key . '=' . $value;
            }
        }
        return '/loader?'.implode('&',$uriParts);
    }
    
}
