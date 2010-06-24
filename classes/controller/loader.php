<?php

class Controller_Loader extends Controller_Site {


    public function before(){
        $this->session = Session::instance();
    }

    public function action_index() {

        $config = Kohana::config('loader');

        
require_once 'helpers/'.$cclass.'.php';
$cache = new $cclass($cconfig);

if (!isset($_SESSION['included'])) {
    $_SESSION['included'] = array();
}

//get variables
$mode = strtoupper(get_by_key('mode','PROD'));
$files = get_by_key('file',array());
$repos = get_by_key('repo', array());
$type = strtolower(get_by_key('type','js'));
$compress = (bool)get_by_key('compress',true);
$algorithm = get_by_key('alg','jsmin');
$depsOnly = (bool)get_by_key('depsOnly',false);
$rebuild = (bool)get_by_key('rebuild',false);
$opts = (bool)get_by_key('opts',false);
//$clearCache = (bool)get_by_key('clearCache',false);
$theme = get_by_key('theme','');
$allDeps = (bool)get_by_key('allDeps', false);
$clearSession = (bool)get_by_key('clearSession', false);
$page = get_by_key('page','');
$key = get_by_key('key','');

$isLoader = false;

if (empty($page)) {
    //generate a GUID
    $page = guid();
}

if (count($files) == 1 && strtolower($files[0]) == 'loader') {
    $mode = 'PROD';
    $isLoader = true;
}

if ($mode == 'DEV') {
    //load the main loader class
    require_once 'loader.class.php';

    $loader = new Loader($lconfig);

    if ($rebuild) {
        $loader->rebuild();
    }

    //unset session
    if ($clearSession && isset($_SESSION['included'][$page])) {
        unset($_SESSION['included'][$page]);
    }
    //get exclude list...
    $exclude = isset($_SESSION['included'][$page]) ? $_SESSION['included'][$page] : array();


    //in development mode
    if ($depsOnly) {
        $deps = $loader->compile_deps($files, $repos, 'jsdeps', $opts, $exclude);
        //setup deps properly
        $d = array();
        $flat = $loader->get_flat_array();
        foreach ($deps as $dep) {
            $css = !empty($flat[$dep]['css']) && count($flat[$dep]['css']) > 0;
            $d[] = $dep.':'.$css;
        }
        //send back as json... this would have been called to get deps by loader.js
        $data = new stdClass();
        $data->deps = $d;
        $data->key = $key;
        header('Content-type:application/json');
        echo json_encode($data);
    } else {
        //var_dump($exclude);
        $ret = $loader->compile($files, $repos, $type, false, $theme, $exclude, $opts);
        //var_dump($ret);
        if ($ret) {
            $source = $ret['source'];
            $included = array_merge($exclude,$ret['included']);
            $_SESSION['included'][$page] = $included;
            //send back with no compression...
            if ($type == 'js') { $type = 'javascript';}
            header('Content-type:text/'.$type);
            echo $source;
        }
    }
} else {
    //in production mode
    //echo "<br>In production code...";

    //load the main loader class
    require_once 'loader.class.php';

    $loader = new Loader($lconfig);
    //echo "<br>load class...";
    if ($rebuild) {
        $loader->rebuild();
        //echo "<br>rebuild class...";
    }


    //unset session
    if ($clearSession && isset($_SESSION['included'][$page])) {
        unset($_SESSION['included'][$page]);
    }
    //get exclude list...
    $exclude;
    if (!$allDeps) {
        $exclude = isset($_SESSION['included'][$page]) ? $_SESSION['included'][$page] : array();
    } else {
        $exclude = array();
    }
    //echo "<br>exclude = <pre>";var_dump($exclude); echo "</pre>";
    $ret = $loader->compile($files, $repos, $type, true, $theme, $exclude, $opts);
    $source = $ret['source'];
    //echo "<br>included = <pre>";var_dump($ret['included']); echo "</pre>";
    if (is_null($ret['included'])) {
        $ret['included'] = array();
    }
    $_SESSION['included'][$page] = array_merge($exclude,$ret['included']);

    if (empty($source)) {
        $source = "/* No source to return */";
    } else if ($isLoader) {
        $source = str_replace('%page%', $page, $source);
    }
    if ($compress) {
        //echo "<br>Compressing....";
        if ($type == 'js') {
            switch ($algorithm){
                case 'jsmin':
                    require_once 'helpers/jsmin-1.1.1.php';
                    $source = JSMin::minify($source);
                    break;
                case 'packer':
                    require_once 'helpers/class.JavaScriptPacker.php';
                    $packer = new JavaScriptPacker($source, $encoding, $fast_decode, $special_char);
                    $source = $packer->pack();
                    break;
            }
        } else {
             require_once 'helpers/minify_css.php';
             $source = Minify_CSS_Compressor::process($source);
        }

    }

    //send the file
    if ($type == 'js') { $type = 'javascript';}
    header('Content-type:text/'.$type);
    echo $source;
}


//TODO: need to figure out how to get ie-specific css files.
    }


    private function guid(){
        $g = '';
        if (function_exists('com_create_guid')){
            $g = com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
                    .substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12)
                    .chr(125);// "}"
            $g = $uuid;
        }

        $g = str_replace('{','',$g);
        $g = str_replace('}','',$g);
        $g = str_replace('-','',$g);
        return $g;
    }
}
