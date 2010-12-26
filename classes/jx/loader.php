<?php


class Jx_Loader implements Jx_Module_Interface {

    private static $_version = '0.1';

    public static function deactivate() {
        // TODO: Implement deactivate() method.
    }

    public static function activate() {
        // TODO: Implement activate() method.
    }

    public static function uninstall() {
        // TODO: Implement uninstall() method.
    }

    public static function install() {
        // TODO: Implement install() method.
    }

    public static function getVersion() {
        return self::$_version;
    }



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
