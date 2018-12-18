<?php
	namespace Alpa\Sockets;
	
    /**
     * Class for working with sockets. 
	 * Performs as a wrapper for the basic PHP Sockets extension.
     * At the same time, it expands the functionality for working with the result.
	 * consists of 2 stages:
	 * Stage 1 - work with the code.
	 * Stage 2 - Hidden Stage. working on errors and on the result through the EventsMap class.
     * @autor Alexey Pakhomov <alexeyP0708@gmail.com>
     *
     */
	 
	class Socket implements Interfaces\Socket
	{
		protected $resource;
		protected $params;
		protected $status;
		protected $last_event;
		public $control_objects;
		protected $classes=[
			'ParamsSocket'=>__NAMESPACE__.'\\Types\\ParamsSocket',
			'Status'=>__NAMESPACE__.'\\Types\\Status',
			'ErrorInterface'=>__NAMESPACE__.'\\Interfaces\\Error',
			'EventsMapInterface'=>__NAMESPACE__.'\\Interfaces\\EventsMap'
		];
		public function __construct (array $params=[],$resource=null)
		{
			$this->classes=(object)$this->classes;
			$runClass=$this->classes->ParamsSocket;
			$this->params=new $runClass();
			$runClass=$this->classes->Status;
			$this->status=new $runClass();
			if(is_resource($resource)){
				$this->initResource($resource,$params);
			} else {
				if(!empty($params)){
					$this->setSettings($params,'params');
				}
			}
		}
		
        /**
         * for processing the result. 
		 *  
		 * {@inheritdoc} | This method starts automatically. Therefore, there is no need to specify it in the code. 
		 * Even if you specify it in the code, it will have no effect if  argument $activated = false 
		 *
         * @param mixed $answer return  socket function
         * @param bool $activated  (service argument) the default is false - the method will not work.
		 *	 This is necessary in cases where it is necessary to specify this method in the code, but there is no need to run it.
		 *	 This is necessary when this class is replaced by another and vice versa according to the uniform {@see \Alpa\Sockets\Interfaces\Socket()} interface
         * @param array $args (service argument) Arguments that were passed in the socket function
         * @param int $err (service argument) var link - through this argument, you can get the error number.
         * 
         * @return bool If it is true, will handle errors in case of error  or start the data registration process in case of success.
         */
		public function onAction(&$answer,bool $activated=false,array &$args=[],&$err=null):bool
		{
			$result=true;
			if($activated===false){
				if($answer===false || $answer===null){
					$result=false;
				}
			} else {
				$event=$this->last_event;
				if($answer===false || $answer===null){
					// error block
					switch ($event) {
						case 'socket_select':
						case 'socket_create':
						case 'socket_create_listen':
						case 'socket_create_pair':
							$err=$this->lastError();
						break;
						default:
						//case 'socket_listen':
						//case 'socket_bind':
							$err=$this->lastError(true);
						break;
					}
					if($err!=0){
						$key=$err;
					}
				} else {
					// success block
					$key=0;					
				}
				if(isset($key)&& isset($this->params->events_map) && ($this->params->events_map instanceof $this->classes->EventsMapInterface)){
					$result=$this->params->events_map->runActionsForKey($event,$key,$this,$args,$answer);
				} 
			}
			return $result;
		}
        /**
         * triggers actions, data register , activates and deactivates methods
         * 
         * @param string $event name socket function
		 * @param mixed|null $answer return  socket function
		 * @param array $args Arguments that were passed in the socket function
         * @return bool
         */
		protected function trigger(string $event,&$answer,&$args=null) :bool
		{
			$result=false;
			$type=($answer===false || $answer===null)?'error':'success';
			$this->last_event=$event;
			$err=null;
			if($result=$this->onAction($answer,$this->params->trigger_action,$args,$err)){
				switch($type){
					case 'success':
						$is_status=false;
						switch($event){
							case 'socket_create':
								$prop_args=['domain','type','protocol'];
								$point=&$this->params;
							break;
							case 'socet_create_listen':
								$prop_args=['port','backlog'];
								$point=&$this->params;
							break;							
							case 'socket_getpeername':
								$prop_args=['resource','address','port'];
								$point=&$this->params->peer_address;
							break;		
							case 'socket_getsockname':
								$prop_args=['resource','address','port'];
								$point=&$this->params->sock_address;
									
							break;
							case 'socket_bind':
								$is_status=true;
								$prop_args=['resource','address','port'];
								$point=&$this->params;
							break;
							case 'socket_listen':
								$is_status=true;
								$prop_args=['resource','backlog'];
								$point=&$this->params;
							break;
							case 'socket_connect':
								$is_status=true;
								$prop_args=['resource','address','port'];
								$point=&$this->params->distination;
							break;
						}
						if(isset($point)){
							foreach($args as $k=>&$v){
								if($prop_args[$k]=='resource'){
									continue;
								}
								$point->{$prop_args[$k]}=&$v;
							}
							unset($v);
						}
						// Для socket_create и socet_create_listen socket_addrinfo_connect socket_addrinfo_bind статусы уже определил initResource
						if($is_status){
							$this->params->how_created=$event;
							$this->status->$event=false;
						}
					break;
					case 'error':
					if($err!==0 && $err!==null){
						$this->last_error=$err;
						$this->displayError($err);
					}
					break;
				}
			}
			return $result;
		}
		
        /**
         * Сalls a function by event name and starts the process of generating object data and actions for the result
         *
         * @param string $event 
         * @param mixed[] $args 
         * 
         * @return mixed
         */
		
		protected function callFunction (string $event,&...$args)
		{
			if(in_array($event,['socket_create','socket_create_listen','socket_addrinfo_bind','socket_addrinfo_connect','socket_addrinfo_explain','socket_addrinfo_lookup','socket_create_pair','socket_select'])){
				$this->clearError();
			} else {
				$this->clearError(true);
			}
			$answer=$this->params->error_stifle?@$event(...$args):$event(...$args);
			//$answer=$event(...$args);
			$this->trigger($event,$answer,$args);
			return $answer;
		}

        /**
         * displays an error.
		 *		 
		 * Displays an error through a class belonging to the interface {@see Alpa\Sockets\Interfaces\Error} or through trigger_error
         *
		 * @param string|int $err Error code or message 
         * @param int $level for display specifig line errors from debug_backtrace 
         * 
         * @return void
         */		
		protected function displayError($err,$level=4)
		{
			if($this->params->error_trigger!==false){
				if(is_numeric($err)){
					$err=(int)$err;
					$err_str=socket_strerror($err);
					$err_str=mb_convert_encoding($err_str, mb_internal_encoding(),$this->params->socket_error_charsets);
				} else {
					$err_str=$err;
				}
				$traces=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,$level);
				$trace=isset($traces[$level-1])?$traces[$level-1]:$traces[0];
				if($this->params->error_trigger instanceof $this->classes->ErrorInterface){
					$this->params->error_trigger->trigger($err,$err_str,$trace);
				}
				else {
					trigger_error("[{$err}]:{$err_str}[{$trace['class']}::{$trace['function']}=>{$trace['file']}:{$trace['line']}]\r\n");
				}
			}
		}

		public function initResource($socket,$params=[]):bool
		{
			$answer=false;
			if(is_null($this->resource)){
				$this->resource=$socket;
				if(!empty($params)){
					$this->setSettings($params,'params');
				}
				switch($this->params->how_created){
					case 'socket_accept':
					case 'socket_connect':
						$this->status->setPack([
							'socket_create'=>false,
							'socket_create_listen'=>false,
							'socket_addrinfo_bind'=>false,
							'socket_addrinfo_connect'=>false,
							'socket_bind'=>false,
							'socket_listen'=>false,
							'socket_assign'=>false,
							'socket_connect'=>false,
							'close'=>false
						],true);
					break;
					case 'socket_create':
						$this->status->setPack([
							'socket_create'=>false,
							'socket_create_listen'=>false,
							'socket_addrinfo_bind'=>false,
							'socket_addrinfo_connect'=>false,
							'close'=>false
						],true);
					break;
					case 'socket_create_pair':
					case 'socket_create_listen':
						$this->status->setPack([
							'socket_create'=>false,
							'socket_create_listen'=>false,
							'socket_listen'=>false,
							'socket_bind'=>false,
							'socket_addrinfo_bind'=>false,
							'socket_addrinfo_connect'=>false,
							//'socket_connect'=>false,//?? 
							'close'=>false
						],true);
					break;
					case 'socket_bind':
						$this->status->setPack([
							'socket_create'=>false,
							'socket_create_listen'=>false,
							'socket_addrinfo_bind'=>false,							
							'socket_addrinfo_connect'=>false,
							'socket_bind'=>false,
							//'socket_connect'=>false,//?? 
							'close'=>false
						],true);
					break;
					case 'socket_listen':
						$this->status->setPack([
							'socket_create'=>false,
							'socket_create_listen'=>false,
							'socket_addrinfo_bind'=>false,							
							'socket_addrinfo_connect'=>false,
							'socket_bind'=>false,
							'socket_listen'=>false,	
							//'socket_connect'=>false,//?? 
							'close'=>false
						],true);
					break;
					case 'socket_addrinfo_bind':
						$this->status->setPack([
							'socket_create'=>false,
							'socket_create_listen'=>false,
							'socket_addrinfo_bind'=>false,							
							'socket_addrinfo_connect'=>false,
							'socket_bind'=>false,							
							'socket_connect'=>false,
							'close'=>false
						],true);
					break;
					case 'socket_addrinfo_connect':
						$this->status->setPack([
							'socket_create'=>false,
							'socket_create_listen'=>false,
							'socket_addrinfo_bind'=>false,
							'socket_addrinfo_connect'=>false,
							'socket_bind'=>false,
							'socket_listen'=>false,
							'socket_assign'=>false,
							'socket_connect'=>false,
							'close'=>false
						],true);
					break;
					default:
						$this->status->setPack(['socket_create'=>false,'socket_create_listen'=>false,'close'=>false],true);
					break;
				}
				$answer=true;
			} else {
				trigger_error(__METHOD__.' => Resource is already attached to the object.');
			}
			return $answer;
		}

		public function getResource()
		{
			$answer=null;
			if(is_resource($this->resource)){
				$answer=$this->resource;
			}
			return $answer;
		}

		public function isClose(): bool
		{
			return $this->status->close;
		}

		public function isWorks($label=null): bool
		{ 
			if(
				!$this->status->close && $this->status->process && 
				($label===null || isset($this->status->$label)&&$this->status->$label==true ) && 
				(!in_array($label,['socket_read','socket_recv','socket_recvfrom','socket_recvmsg']) || $this->status->read==true) &&
				(!in_array($label,['socket_write','socket_send','socket_sendmsg','socket_sendto']) || $this->status->write==true)
			){
				return true;
			} else {
				return false;
			}
		}
        /**
         * set settings 
         * 
         * @param array $params 
         * @param string $type params|classes|status for params {@see Alpa\Sockets\Types\ParamsSocket}  
         * 
         * @return void
         */
		
		public function setSettings(array $params,$type='params')
		{
			if(!in_array($type,['params','classes','status'])){
				return;
			};
			if($type==='params'){
				$params=$this->params->validateProperties($params);
			}
			foreach($params as $key=>$value){
					$this->$type->$key=$value;
			}
		}
		 /*  
		 * return settings for object
		 * @param string $type params|classes|status for params {@see Alpa\Sockets\Types\ParamsSocket}  
         * 
         * @return void
         */
		public function getSettings ($type='params') 
		{
			if(!in_array($type,['params','classes','status'])){
				return;
			}
			return $this->$type;
		}
		
		public function create(int $domain=AF_INET,int $type=SOCK_STREAM,int $protocol=SOL_TCP)
		{
			$answer=null;
			if($this->isWorks('socket_create')){
				if(is_null($this->resource)){
					$answer=$this->callFunction('socket_create',$domain,$type,$protocol);
					if($answer===false){
						$answer=null;
					}else {
						$this->initResource($answer,['how_created'=>'socket_create']);
					}							
				} 
			}
			return $answer;			
		}
		
		public function createListen (int $port, int $backlog=128)
		{
			$answer=null;
			if($this->isWorks('socket_create_listen')){
				if(is_null($this->resource)){
					$answer=$this->callFunction('socket_create_listen ',$port,$backlog);
					if($answer===false){
						$answer=null;
					} else {
						$this->initResource($answer,['how_created'=>'socket_create_listen']);
					}							
				} 
			}
			return $answer;
		}
		
		public function createPair (int $domain=AF_INET , int $type=SOCK_STREAM , int $protocol=SOL_TCP,array &$fd=[])
		{
			$answer=null;
			if($this->isWorks()){
				$answer=$this->callFunction('socket_create_pair',$domain,$type,$protocol,$fd);
				$answer=$answer!==false?$fd:null;
			} 
			return $answer;	
		}

		public function bind(string $address,int $port=0):bool
		{
			$answer=false;
			if($this->isWorks('socket_bind')){
				$answer=$this->callFunction('socket_bind',$this->resource,$address,$port);
			}
			return $answer;
		}
		
		public function listen(int $backlog = 0):bool
		{
			$answer=false;
			if($this->isWorks('socket_listen')){
				$answer=$this->callFunction('socket_listen',$this->resource,$backlog);
			}
			return $answer;
		}
        
		public function accept()
		{
			$answer=null;
			if($this->isWorks('socket_accept')){
				$answer=$this->callFunction('socket_accept',$this->resource);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
       
		public function close ()
		{
			$this->status->setPack(['close'=>true],false);
			if(is_resource($this->resource)){
				socket_close($this->resource);
				$this->resource=null;
			}
		}
       
		public function shutdown(int $how = 2):bool
		{
			$answer=false;
			if($this->isWorks()){
				$answer=$this->callFunction('socket_shutdown',$this->resource,$how);
				if($answer!==false){
					switch($how){						
						case 0:$this->status->read=false;
						break;
						case 1:$this->status->write=false;
						break;
						default:
							$this->status->read=false;
							$this->status->write=false;
						break;	
					}
				}
			}
			return $answer;
		}
		
		public function connect (string $address , int $port =0):bool // //проверить выдаст ли assign подсоедененный порт если сервер (bind+listen+assign) инициализирует коннект
		{
			$answer=false;
			if($this->isWorks('socket_connect')){
				$answer=$this->callFunction('socket_connect',$this->resource,$address,$port);
			}
			return $answer;
		}
    
	   public function getOption(int $level , int $optname=SOL_SOCKET)
		{
			$answer=null;
			if($this->isWorks()){
				$answer=$this->callFunction('socket_get_option',$this->resource,$level ,$optname);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		 
		public function setOption (int $level , int $optname ,$optval ):bool
		{
			$answer=false;
			if($this->isWorks()){
				$answer=$this->callFunction('socket_get_option',$this->resource,$level,$optname,$optval);
			}
			return $answer;
		}
		
		public function getpeername(string &$address , int &$port=null):bool
		{
			$answer=false;
			if($this->isWorks()){
				$answer=$this->callFunction('socket_getpeername',$this->resource,$address,$port);
			}
			return $answer;
		}
       	
		public function getsockname(string &$address , int &$port=null):bool
		{
			$answer=false;
			if($this->isWorks()){
				$answer=$this->callFunction('socket_getsockname',$this->resource,$address,$port);
			}
			return $answer;
		}
		
		public function clearError(bool $is_resource=false) 
		{
			if($this->isWorks()){
				if($is_resource){
					socket_clear_error($this->resource);
				} else {
					socket_clear_error();
				}
				$this->last_error=0;
			}
		}
		       
		public function lastError(bool $is_resource=false) :int
		{
			$answer=0;
			if($this->isWorks()){
				if($is_resource){
					$answer=socket_last_error($this->resource);
				} else {
					$answer=socket_last_error();
				}
				//$this->last_error=$answer;
			}
			return $answer;
		}
		
		public function setBlock(bool $check):bool
		{
			$answer=false;
			if($this->isWorks()){
				if($check===true){
					$event='socket_set_block';
					$answer=$this->callFunction('socket_set_block',$this->resource);
				} else {
					$event='socket_set_nonblock';
				}
				$answer=$this->callFunction($event,$this->resource);
				if($answer!==false){
					$this->params->blocked=$check;
				}
			}
			return $answer;
		}
		
		public function select(?array &$read=null , ?array &$write=null , ?array &$except=null , int $tv_sec=0 , int $tv_usec = 0):?int
		{
			$answer=null;
			if($this->isWorks()){
				if(!empty($read)||!empty($write)||!empty($exept)){
					$answer=$this->callFunction('socket_select',$read,$write,$except,$tv_sec,$tv_usec);
					$answer=($answer!==false)?$answer:null;
				}
			}
			return $answer;
		}
        
		public function read(int $length=1024 , int $type=PHP_BINARY_READ):?string
		{
			$answer=null;
			if($this->isWorks('socket_read')){
				$answer=$this->callFunction('socket_read',$this->resource,$length,$type);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		 
		public function write (string $buffer, int $length = null):?int
		{
			$answer=null;
			if($this->isWorks('socket_write')){
				if($length!==null){
					$answer=$this->callFunction('socket_write',$this->resource,$buffer,$length);
				} else {
					$answer=$this->callFunction('socket_write',$this->resource,$buffer);
				}
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
        
		public function recv(string &$buf , int $len , int $flags):?int
		{
			$answer=null;
			if($this->isWorks('socket_recv')){
				$answer=$this->callFunction('socket_recv',$this->resource,$buf,$len,$flags);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
        
		public function recvfrom(string &$buf , int $len , int $flags , string &$name , int &$port=null):?int
		{
			$answer=null;
			if($this->isWorks('socket_recvfrom')){
				$answer=$this->callFunction('socket_recvfrom',$this->resource,$message,$flags);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		
		public function recvmsg (array &$message, int $flags = null):?int
		{	
			$answer=null;
			if($this->isWorks('socket_recvmsg')){
				$answer=$this->callFunction('socket_recvmsg',$this->resource,$message,$flags);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		public function send(string $buf , int $len , int $flags):?int
		{
			$answer=null;
			if($this->isWorks('socket_send')){
				$answer=$this->callFunction('socket_send',$this->resource,$buf,$len,$flags);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		public function sendmsg (array $message , int $flags = null ):?int
		{
			$answer=null;
			if($this->isWorks('socket_sendmsg')){
				$answer=$this->callFunction('socket_sendmsg',$this->resource,$message,$flags);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		public function sendto ( string $buf , int $len , int $flags , string $addr , int $port = null):?int
		{
			$answer=null;
			if($this->isWorks('socket_sendto')){
				$answer=$this->callFunction('socket_sendto',$this->resource,$buf,$len,$flags,$addr,$port);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}

		public function strerror(int $errno):?string
		{
			return socket_strerror ($errno);
		}

		
		public function cmsgSpace (int $level , int $type , int $n =0):?int
		{
			$answer=null;
			if($this->isWorks('socket_cmsg_space')){
				$answer=$this->callFunction('socket_cmsg_space',$level,$type ,$n);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		
		public function exportStream ()
		{
			$answer=null;
			if($this->isWorks('socket_export_stream')){
				$answer=$this->callFunction('socket_export_stream',$this->resource);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		public function importStream($stream)
		{
			$answer=null;
			if($this->isWorks('socket_import_stream')){
				$answer=$this->callFunction('socket_import_stream',$stream);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		public function addrinfoBind ($addr)
		{
			$answer=null;
			if($this->isWorks('socket_addrinfo_bind')){
				$answer=$this->callFunction('socket_addrinfo_bind',$addr);
				if($answer!==false){
					$his->initResource($answer,['how_created'=>'socket_addrinfo_bind']);
				}
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		public function addrinfoConnect($addr)
		{
			$answer=null;
			if($this->isWorks('socket_addrinfo_connect')){
				$answer=$this->callFunction('socket_addrinfo_connect',$addr);
				if($answer!==false){
					$his->initResource($answer,['how_created'=>'socket_addrinfo_connect']);
				}
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		public function addrinfoExplain($addr):?array
		{
			if($this->isWorks()){
				$answer=$this->callFunction('socket_addrinfo_explain',$addr);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
		public function addrinfoLookup (string $host , string $service=null, array $hints=null):?array
		{
			if($this->isWorks()){
				$answer=$this->callFunction('socket_addrinfo_lookup',$host,$service,$hints);
				$answer=($answer!==false)?$answer:null;
			}
			return $answer;
		}
	}