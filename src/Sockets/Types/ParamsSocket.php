<?php
/**
 * ParamsSocket
 *
 *
 *
 *
 */
	namespace Alpa\Sockets\Types;
	use Alpa\Classes\InitType;
	class ParamsSocket extends InitType
	{
        /**
         *  Defines parameters for a socket.
         *	```php
				namespace Alpa\Sockets;
					$params=[
						'socket_error_charsets'=>'WINDOWS-1251',
						'errors_map'=>__DIR__.'/errors_maps/errors_map_default.php'
					];
					$paramsSocket= new Types\ParamsSocket($params);
					$socket=new Socket($paramsSocket);
					// OR
					// $socket=new Socket($params);
					$socket->create();
			```		
         *
         *
         *
         */
		
		protected $argtoprop=false;
		protected $classes=[
			'ErrorInterface'=>'\\Alpa\\Sockets\\Interfaces\\Error',
			'EventsMap'=>'\\Alpa\\Sockets\\EventsMap',
			'EventsMapInterface'=>'\\Alpa\\Sockets\\Interfaces\\EventsMap',
			'Address'=>__NAMESPACE__.'\\Address'
		];
		
        /**
         * The domain parameter specifies the protocol family to be used by the socket. 
		 *
		 *	{@link http://php.net/manual/en/function.socket-create.php}.  
		 *	Determined by {@see \Alpa\Sockets\Socket::create()} method or specified in {@see \Alpa\Sockets\Socket::__construct()} method  in  _$params_ argument when passing  _$resource_ argument
         *
         * @var int $domain
         *
         */
		
		public $domain;
        /**
         * The type parameter selects the type of communication to be used by the socket.  
		 * {@link http://php.net/manual/en/function.socket-create.php}.  
         * Determined by {@see \Alpa\Sockets\Socket::create()} method or specified in {@see \Alpa\Sockets\Socket::__construct()} method  in  _$params_ argument when passing  _$resource_ argument  
         *
         * @var int $type
         *
         */
		
		public $type;
		
        /**
         *	The protocol parameter sets the specific protocol within the specified domain to be used when communicating on the returned socket.     
         * Determined by {@see \Alpa\Sockets\Socket::create()} method or specified in  {@see \Alpa\Sockets\Socket::__construct()} method in  _$params_ argument when passing  _$resource_ argument
         *
         *	@var int $protocol
         *
         */
		
		public $protocol;
		
        /**
          * If the socket is of the AF_INET family, 
		 * the address is an IP in dotted-quad notation (e.g. 127.0.0.1).If the socket is of the AF_UNIX family, 
		 * the address is the path of a Unix-domain socket (e.g. /tmp/my.sock).  
         * Determined by {@see \Alpa\Sockets\Socket::bind()} method or specified in {@see \Alpa\Sockets\Socket::__construct()} method  in  _$params_ argument when passing  _$resource_ argument or determined automatically
         *
         * @var string $address
         *
         */
		
		public $address;
		
        /**
         * The port parameter is only used when binding an AF_INET socket, and designates the port on which to listen for connections.
		 * Determined by {@see \Alpa\Sockets\Socket::bind()} method or specified in {@see \Alpa\Sockets\Socket::__construct()} method  in  _$params_ argument when passing  _$resource_ argument or determined automatically
         * @var int $port
         *
         */
		
		public $port;
		
        /**
         * A maximum of backlog incoming connections will be queued for processing.
         * Determined by {@see \Alpa\Sockets\Socket::listen()} method.  {@see http://php.net/manual/en/function.socket-listen.php}
         *
         *	@var int backlog
         *
         */
		
        public $backlog;
		
		/**
         * The name of socket object. If not specified, then it is automatically generated. 
         *
         *
         * @var string $name
         *
         */
		 
		public $name;
        /**
         *  Events Map. Object or file path. The default is "/../events_maps/events_map_default.php".  
         *
         *
         * @var \Alpa\Sockets\ErrorsMap|string  $errors_map
         *
         */
		
		public $events_map;
       
        /**
         *  defines the state as  created  socket. 
		 *  Be sure to specify it when passing a socket resource to {@see \Alpa\Sockets\Socket::__construct()} method.  
         *  values socket_create|socket_create_listen|socket_create_pair|socket_addrinfo_bind|socket_addrinfo_connect|socket_bind|socket_listen|socket_accept|socket_connect
         *	
         * @var string $how_created
         *
         */
		
		public $how_created;
		
        /**
         * error logging.   true- displays errors through trigger_error. false - blocks socket error output. 
         *  String type -  name class implement {@see \Alpa\Sockets\ErrorInterface()}  interface  
         *
         * @var \Alpa\Sockets\ErrorInterface|bool $error_trigger
         *
         */
		
		public $error_trigger=true;
        
		/**
         *  Indicates to suppress errors in the php functions of the Sockets application.  
         *  An example of suppressing @socket_create($ domain, $ type, $ protocol) 
         *
         * @var bool $error_stifle
         *
         */
		public $error_stifle=true;
        
		/**
         * Specifies the error encoding when they are generated.
         *
         *
         * @var string $socket_error_charsets 
         *
         */
		public $socket_error_charsets='UTF-8';
        /**
         * Assigned to client socket. address and port of the remote socket.
		 *
		 * determined via {@see \Alpa\Sockets\Socket::connect()} method or determined automatically  
         *
         * 
         * @var \Alpa\Sockets\Types\Address $distination
         *
         */		
		public $distination;
		
        /**
         * Remote client address.
		 * method result {@see  \Alpa\Sockets\Socket::getpeername()} 
		 *
         * automatically determined to inform
         *
         * @var object $peer_address
         *
         */
		public $peer_address;
		
		/**
		 * local address.
         *
		 * method result {@see  \Alpa\Sockets\Socket::getsockname()} 
		 * automatically determined to inform
         *
         * @var object $peer_address
         *
         */
		public $sock_address;
		
        /**
         * service team.
		 *
         * If it is necessary to disable the automatic execution of {@see  \Alpa\Sockets\Socket::onAction()} in the object in order to apply it in the user code.
         *
         * @var bool $trigger_action 
         *
         */		
		public $trigger_action=true;
		
        /**
         * informs whether the socket is blocked.
         *
         *
         * @var  bool $blocked
         *
         */
		public $blocked=true;
		
        /**
         * for validation $params
         * 
         * @param array|object $params 
         * 
         * @return object
         */
		public function validateProperties($params)
		{
			if(is_array($params)){
				$params=(object)$params;
			}
			if($params===$this){
				$props=$this->keysPublicProp;
			}else{
				$props=array_keys(get_object_vars($params));
			}
			foreach($props as $key){
				$value=&$params->$key;
				switch($key){
					case 'name':
						if(empty($value)){
							$value=hash('sha256',spl_object_hash($this));
						}
					break;
					case 'error_trigger':
						if(is_string($value)){
							$value=new $value();
						} 
					break;
					case 'sock_address':
					case 'peer_address':
					case 'distination':
						$Address= $this->classes->Address;
						if(is_array($value)){
							$value = new $Address($value);
						} else if(!is_object($value)){
							$value=new $Address();
						}
					break;
					case 'events_map': 
					
						if(is_string($value) && file_exists($value)){
							$value=include($value);						
						}
						$EventsMapInterface=$this->classes->EventsMapInterface;
						if(is_string($value) || !($value instanceof $EventsMapInterface)){
							$value=null;
						}
					break;
					case 'address':
					case 'port':
					break;
					case 'how_created':
						/*switch($value){
							case 'create':
							case 'create_pair':
							case 'bind':
							case 'listen':
							case 'accept':
							case 'connect':
							case null:
							break;
							default:
								trigger_error('"how_created" property must be one of "create" or "create_pair" or "bind" or "listen" or "accept" or "connect" values.');
							break;
							
						}*/
					break;
				}
				unset($value);
			}
			return $params;
		}
	}