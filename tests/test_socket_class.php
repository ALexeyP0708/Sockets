<?php
	#! php -f test_socket_class.php
	use Alpa\Sockets\Socket;
	use Alpa\Sockets\Types;
	//include_once __DIR__.'/../my_autoload.php'; // autoload for test. PSR-4
	include_once __DIR__.'/../vendor/autoload.php';// composer autoload
	//include_once __DIR__.'/../../../Alpa/PhpDump/include_file_in_index.php'; // Компонент phpDump для дебага - отоброжение в chrome console.
	
	$params=[
		'socket_error_charsets'=>'WINDOWS-1251',
		'error_stifle'=>true,
		'events_map'=>__DIR__.'\..\events_maps\events_map_default.php'
	];
	$server=new Socket($params);
	//$answer=$server->createPair(); // good
	$server->create(); // good
	$server->bind('127.0.0.71',8000); // good
	$server->listen(); // good
	$server->setBlock(false); //good
	$clients=[];
	$resources=[];
	$read=[];
	$write=[];
	while($server->isWorks() && true){
		do {
			$client=$server->accept(); // good
			if(!is_null($client)){
				// наследуем параметры сокет сервера
				$client_params=array_merge($params,['how_created'=>'socket_accept','domain'=>AF_INET,'type'=>SOCK_STREAM,'protocol'=>SOL_TCP]);
				// создаем привязываем клиентский ресурс к обькту
				$client=new Socket($client_params,$client);
				$clients[]=$client;
				$str="Hello!\r\n";
				$client->write($str); //good
			} 
		} while(!is_null($client));
		if(empty($clients)){
			continue;
		}
		$on_resources=[];
		$on_clients=[];
		foreach($clients as $key=>$client){ // исключаем сокеты которые закрыты и выбираем только рабочие сокеты.
			if($client->isClose()){
				unset($clients[$key]);
				continue;
			}
			if($client->isWorks()){
				$on_resources[]=$client->getResource();
				$on_clients[]=$client;
			}
		}
		$read=$on_resources;
		$write=$on_resources;
		$except=null;
		$check=$server->select($read,$write); //good
		//$check=socket_select($read,$write,$except,0); //good
		$pool=$read;
		foreach($write as $k=>$r){
			if(!in_array($r,$pool)){
				$pool[]=$r;
			}
		}			
		if(!empty($pool) && !empty($check)){
			foreach($pool as $resource){
				if(in_array($resource,$read)){
					if(($key=array_search($resource,$on_resources))!==false){
						$client=$on_clients[$key];
						if($answer=$client->read()){							
							$answer=iconv('IBM866', 'UTF-8', $answer);// if run telnet in windows and/or Russia locale. 
							echo $answer;
						}
					}
				}
			}
		}
		usleep(5);
	}
	/**/