<?php

class Controller_Loader extends Controller_Site {

    private $config;
    private $session;

    public function before(){
        $this->session = Session::instance();
        $this->config = Kohana::config('loader');

        //search all activated modules for media and normalize in the
        //config array
        //Jx_Debug::dump(Jx_Modules::get_all());
        foreach (Jx_Modules::get_all() as $mod => $arr) {
            $basePath = MODPATH.$mod.DS.'media'.DS.$mod.DS;

            if ($arr['activated'] && file_exists($basePath)) {

                $create = false;
                $jsPath = null;
                $cssPath = null;
                $imgPath = null;
                $imgUrl = null;
                if (is_dir($basePath.'js')) {
                    $jsPath = $basePath.'js'.DS;
                    $create = true;
                }
                if (is_dir($basePath.'css')) {
                    $cssPath = $basePath.'css'.DS;
                    $create = true;
                }
                $img = false;
                if (is_dir($basePath.'images')) {
                    $imgPath = $basePath.'images'.DS;
                    $imgUrl = '../images/';
                    $create = true;
                    $img = true;
                }
                if ($create && $img) {
                    $this->config['repos'][$mod] = array(
                        'imageUrl' => $imgUrl,
                        'paths' => array(
                            'js' => $jsPath,
                            'css' => $cssPath,
                            'images' => $imgPath
                        )
                    );
                    //Jx_Debug::dump($this->config['repos'][$mod], 'config for '.$mod);
                } elseif ($create) {
                     $this->config['repos'][$mod] = array(
                        'paths' => array(
                            'js' => $jsPath,
                            'css' => $cssPath
                        )
                    );
                    //Jx_Debug::dump($this->config['repos'][$mod], 'config for '.$mod);
                }
            }

        }

        //Jx_Debug::dump($this->config);

    }

    public function action_index() {

        $config = $this->config;
        $path = Kohana::find_file('vendors', 'loader/loader.class');

        if ($path === FALSE) {
            throw new Kohana_Exception('Unable to find the loader class.');
        }
        require_once $path;

        //get variables
        $mode = strtoupper($this->request->get_param('mode','PROD'));
        $files = $this->request->get_param('file',array());
        $repos = $this->request->get_param('repo', array());
        $type = strtolower($this->request->get_param('type','js'));
        $compress = (bool)$this->request->get_param('compress',true);
        $algorithm = $this->request->get_param('alg','jsmin');
        $depsOnly = (bool)$this->request->get_param('depsOnly',false);
        $rebuild = (bool)$this->request->get_param('rebuild',false);
        $opts = (bool)$this->request->get_param('opts',false);
        //$clearCache = (bool)$this->request->param('clearCache',false);
        $theme = $this->request->get_param('theme','');
        $allDeps = (bool)$this->request->get_param('allDeps', false);
        $clearSession = (bool)$this->request->get_param('clearSession', false);
        $page = $this->request->get_param('page','');
        $key = $this->request->get_param('key','');

        //Jx_Debug::log($type,'Type to load');
        $isLoader = false;

        if (empty($page)) {
            //generate a GUID
            $page = $this->guid();
        }

        $dbkey = $page.'-'.$type;
        //Jx_Debug::log($dbkey,'DB Key');
        $l = Jelly::select('loader')->where('key','=',$dbkey)->limit(1)->execute();
        //Jx_Debug::log($l,'return from Jelly::select');
        if ($l->loaded()) {
            $included = unserialize($l->data);
        } else {
            $included = array();
        }

        if (count($files) == 1 && strtolower($files[0]) == 'loader') {
            $mode = 'PROD';
            $isLoader = true;
        }

        $loader = new Loader($config);

        if ($rebuild) {
            $loader->rebuild();
        }

        //Jx_Debug::log($clearSession, 'Clear Session?');

        //unset session
        if ($clearSession && isset($included[$page])) {
            $included = array();
            //TODO: clear css and js from database
            $dbk = $page . '-js';
            $l = Jelly::select('loader')->where('key','=',$dbk)->limit(1)->execute();
            if ($l->loaded()) {
                $l->delete();
            }

            $dbk = $page . '-css';
            $l = Jelly::select('loader')->where('key','=',$dbk)->limit(1)->execute();
            if ($l->loaded()) {
                $l->delete();
            }

        }

        //Jx_Debug::log($included, 'classes included last time');

        if ($mode == 'DEV') {

            //get exclude list...
            $exclude = $included;

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
                //TODO: save included classes
                header('Content-type:application/json');
                echo json_encode($data);
                exit();
            } else {
                $ret = $loader->compile($files, $repos, $type, false, $theme, $exclude, $opts);
                if ($ret) {
                    $source = $ret['source'];
                    $incl = array_merge($exclude,$ret['included']);
                    //$this->session->set($type.'-included',$included);
                    if (!$l->loaded()) {
                        Jelly::factory('loader')
                            ->set(array(
                                'key' => $dbkey,
                                'data' => serialize($incl)
                        ))->save();
                    } else {
                        $l->key = $dbkey;
                        $l->data = serialize($incl);
                        $l->save();
                    }
                    //send back with no compression...
                    if ($type == 'js') { $type = 'javascript';}
                    header('Content-type:text/'.$type);
                    echo $source;
                    exit();
                }
            }
        } else {
            //in production mode

            //get exclude list...
            $exclude;
            if (!$allDeps) {
                $exclude = $included;
            } else {
                $exclude = array();
            }
            //Jx_Debug::log($exclude, 'excluded classes');
            $ret = $loader->compile($files, $repos, $type, true, $theme, $exclude, $opts);
            //Jx_Debug::log($ret['included'], 'returned from compile');
            $source = $ret['source'];
            if (is_null($ret['included'])) {
                $ret['included'] = array();
            }
            $included = array_merge($exclude,$ret['included']);
            //Jx_Debug::log($included, 'saved to exclude next time');
            if (!$l->loaded()) {
                Jelly::factory('loader')
                    ->set(array(
                        'key' => $dbkey,
                        'data' => serialize($included)
                ))->save();
            } else {
                $l->data = serialize($included);
                $l->save();
            }

            if (empty($source)) {
                $source = "/* No source to return */";
            } else if ($isLoader) {
                $source = str_replace('%page%', $page, $source);
            }
            if ($compress) {
                if ($type == 'js') {
                    switch ($algorithm){
                        case 'jsmin':
                            $path = Kohana::find_file('vendors','loader/includes/jsmin-1.1.1');
                            require_once $path;
                            $source = JSMin::minify($source);
                            break;
                        case 'packer':
                            $path = Kohana::find_file('vendors','loader/includes/class.JavaScriptPacker');
                            require_once $path;
                            $packer = new JavaScriptPacker($source, $encoding, $fast_decode, $special_char);
                            $source = $packer->pack();
                            break;
                    }
                } else {
                    $path = Kohana::find_file('vendors','loader/includes/minify_css');
                    require_once $path;
                    $source = Minify_CSS_Compressor::process($source);
                }

            }

            //send the file
            if ($type == 'js') { $type = 'javascript';}
            header('Content-type:text/'.$type);
            echo $source;
            exit();
        }

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

    private function getKey($page,$type){
        return $this->session->id().'-'.$page.'-'.$type;
    }
}
