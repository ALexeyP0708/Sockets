<?php
	# php -f test_connect_socket_class.php
	//include_once __DIR__.'/../my_autoload.php'; // autoload for test. PSR-4
	include_once __DIR__.'/../vendor/autoload.php';// composer autoload
	use Alpa\Sockets\Socket;
	use Alpa\Sockets\Types;
	$params=[
		'socket_error_charsets'=>'WINDOWS-1251',
		'events_map'=>__DIR__.'\..\events_maps\events_map_default.php'
	];	
	$is_connect=false;
	$clients=[];
	//$params= new Types\ParamsSocketClient($params);	
	$socket=new Socket($params);
	$socket->create();
	//$socket->create();
	$init_connect=false;
	//$clients=new Types\SelectedPorts();
	while($socket->isWorks() && true){
		if($is_connect===false){
			$is_connect=$socket->connect('127.0.0.71','8000');
			if($is_connect===true){
				$clients[]=$socket->getResource();
			}
		} else {
			$read=$clients;
			$write=$clients;
			$selected=$socket->select($read,$write); //['read','write','mixed','all','except']
			if(!empty($selected)){
				if((in_array($socket->getResource(),$read))){
					$answer=$socket->read();
					$answer=iconv('IBM866', 'UTF-8', $answer);
					echo $answer;
				} 
				if((in_array($socket->getResource(),$write) && $init_connect==false)){
					$init_connect=true;
					$socket->write("Hello! My name Alx!\r\n");
				}
			}
		}
		usleep(10);
	}