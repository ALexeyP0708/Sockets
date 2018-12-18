<?php
	use Alpa\DSockets\ErrorsMap;
include_once __DIR__.'/../autoload.php'; // autoload for test. PSR-4
	//include_once __DIR__.'/../../../autoload.php';// composer autoload
	//include_once __DIR__.'/../../../Alpa/PhpDump/include_file_in_index.php'; // для дебага - отоброжение в chrome console.
	$actions=[
		'test_action'=>function(){
			echo "test_action \r\n";
		}
	];
	$kits=[
		'test_kit'=>['keys'=>['*','test_key'],'actions'=>['test_action']]//'method_return','close_socket',
	];
	$map=[
			'global'=>['test_kit'],
	];
	$emap=new ErrorsMap($map,$actions,$kits);
	//\deb::print($emap,'init error map');
	$emap->setActions(['test_action2'=>function(){
		echo "test_action2 \r\n";
	}]);
	$emap->setKit('test_kit2',['keys'=>['test_key2'],'actions'=>['test_action2']]);
	$kit=$emap->getKit('test_kit2');
	$emap->addEvent('test_map','test_kit2');
	$emap->addActionInKit('test_kit2',function(){
		echo "test_action3 \r\n";
	});
	$emap->addKeyInKit('test_kit2','test_key3');
	
	$map=$emap->getKitForKey('test_map','test_key');
	//\deb::print($map,'Kit for Key');
	$map=$emap->toFromKitForKey('test_map','test_key');
	//\deb::print($map,'to from kit for key ');
	foreach($actions as $action){
		$action();
	}
	