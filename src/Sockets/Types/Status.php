<?php
	namespace Alpa\Sockets\Types;
	use Alpa\Classes\InitType;
	class Status extends InitType
	{
		protected $argtoprop=false;
		public 	$process=true,
		/*bool*/$socket_create=true,
		/*bool*/$socket_create_listen=true,
		/*bool*/$socket_bind=false,
		/*bool*/$socket_listen=false,
		/*bool*/$socket_accept=false,
		/*bool*/$socket_connect=false,
		/*bool*/$socket_read=false,		
		/*bool*/$socket_recv=false,
		/*bool*/$socket_recvfrom=false,
		/*bool*/$socket_recvmsg=false,
		/*bool*/$socket_write=false,
		/*bool*/$socket_send=false,
		/*bool*/$socket_sendmsg=false,
		/*bool*/$socket_sendto=false,
		/*bool*/$socket_cmsg_space=false,
		/*bool*/$socket_export_stream=false,
		/*bool*/$socket_import_stream=false,
		/*bool*/$socket_addrinfo_bind=false,
		/*bool*/$socket_addrinfo_connect=false,
		/*bool*///$socket_addrinfo_explain=false,
		/*bool*///$socket_addrinfo_lookup=false,
		/*bool*/$write=false,
		/*bool*/$read=false,
		/*bool*/$loop=false,
		/*bool*/$close=false;
		
		public function setPack(array $pack,?bool $other_status=null)
		{
			$props=$this->keysPublicProp;
			foreach($props as $name){
				if(isset($pack[$name])){
					$this->$name=$pack[$name];
				} else if($other_status!==null){
					$this->$name=$other_status;
				}
			}
			unset($status);
		}
	}