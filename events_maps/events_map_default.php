<?php
	use Alpa\Sockets\EventsMap;
	// keys=>[0] - 0 for success event
	$actions=[
			'global'=>function(string $event,int $key,string $action,$socket,&$params,&$answer){
				$check=true;
				switch($action){
					case 'close_socket':
						$socket->close();
					break;
					case'shutdown_socket':
						$socket->shutdown();
					break;	
					case 'exit':
						exit;
					break;
					case 'accept_on':
						$socket->status->accept=true;
					break;
					case 'accept_off':
						$socket->status->accept=false;
					break;
					case 'connect_on':
						$socket->status->connect=true;
					break;
					case 'connect_off':
						$socket->status->connect=false;
					break;
					case 'process_on':
						$socket->status->process=true;
					break;
					case 'process_off':
						$socket->status->process=false;
					break;
					case 'read_on':
						$socket->status->read=true;
					break;
					case 'read_off':
						$socket->status->read=false;
					break;
					case 'write_on':
						$socket->status->write=true;
					break;
					case 'write_off':
						$socket->status->write=false;
					break;
					case 'loop_on':
						$socket->status->loop=true;
					break;
					case 'loop_off':
						$socket->status->loop=false;
					break; 
				}
				return $check;
			},
			'close_socket'=>'global',
			'shutdown_socket'=>'global',
			'method_return'=>'global',
			'accept_on'=>'global',
			'accept_off'=>'global',
			'connect_on'=>'global',
			'connect_off'=>'global',
			'process_on'=>'global',
			'process_off'=>'global',
			'read_on'=>'global',
			'read_off'=>'global',
			'write_on'=>'global',
			'write_off'=>'global',
			'loop_on'=>'global',
			'loop_off'=>'global',
	];
	$kits=[
		'shutdown_process'=>['keys'=>['*',10054],'actions'=>['shutdown_socket','close_socket']],
		'overlook'=>['keys'=>[0,10061],'actions'=>[function (){	return false; }]],
	];
	$map=[
			'global'=>['shutdown_process','overlook'],
			'socket_create'=>[],
			'socket_bind'=>[],
			'socket_listen'=>[],
			'socket_accept'=>[],
			'socket_connect'=>[],
			'socket_select'=>[],
			'socket_set_block'=>[],
			'socket_set_nonblock'=>[],
			'socket_set_option'=>[],
			'socket_getpeername'=>[],
			'socket_read'=>[],
			'socket_write'=>[],
			'socket_recv'=>[],
			'socket_recvfrom'=>[],
			'socket_recvmsg'=>[],
			'socket_send'=>[],
			'socket_sendmsg'=>[],
			'socket_sendto'=>[]
	];
	return new EventsMap($map,$actions,$kits);