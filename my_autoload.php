<?php
$dir=__DIR__.'/../';
spl_autoload_register(function($class)use($dir){
		$class=explode('\\',$class);
		$class[0]='src';
		$link=$dir.'/'.implode('/',$class).'.php';
		if(file_exists($link)){
			include_once $link;
		}
	}
);