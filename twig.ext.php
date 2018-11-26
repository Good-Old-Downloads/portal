<?php
class AppExtension extends \Twig_Extension {
    public function getFunctions(){
        return array(
            new \Twig_SimpleFunction('loadCSS', array($this, 'loadCSS'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('loadJS', array($this, 'loadJS'), array('is_safe' => array('html'))),
        );
    }

    public function getFilters(){
        return array(
            new \Twig_SimpleFilter('long2ip', array($this, 'long2ip')),
            new \Twig_SimpleFilter('convertBytes', array($this, 'convertBytes')),
        );
    }

    public function loadCSS($styles, $integrity = null){
        $ret = array();
        if (is_array($styles)) {
            foreach ($styles as $key => $value) {
                if ($integrity !== null) {
                    if (!empty($integrity[$key])) {
                        $integStr = ' integrity="'.$integrity[$key].'"';
                    } else {
                        $integStr = '';
                    }
                }
                if (file_exists(__DIR__.'/web/'.$value)) {
                    $ret[] = '<link rel="stylesheet" href="'.$value.'?'.filemtime(__DIR__.'/web/'.$value).'"'.$integStr.'>';
                }
            }
        }
        return join($ret, "\n");
    }
    public function loadJS($scripts, $integrity = null){
        $ret = array();
        if (is_array($scripts)) {
            foreach ($scripts as $key => $value) {
                if ($integrity !== null) {
                    if (!empty($integrity[$key])) {
                        $integStr = ' integrity="'.$integrity[$key].'"';
                    } else {
                        $integStr = '';
                    }
                }
                if (file_exists(__DIR__.'/web/'.$value)) {
                    $ret[] = '<script src="'.$value.'?'.filemtime(__DIR__.'/web/'.$value).'"'.$integStr.'></script>';
                }
            }
        }
        return join($ret, "\n");
    }
}