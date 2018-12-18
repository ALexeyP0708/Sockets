<?php
	namespace Alpa\Sockets\Interfaces;
    
	/**
     * API Sockets.  
	 *
     * The purpose of the interface is to use common API for working with sockets.
	 * You can create classes according to the principle of interface inheritance, 
	 * you can create classes based on the "factory" pattern and with which you can integrate 
	 * third-party applications for sockets into the working code.
	 * Note:Methods that are bound to PHP Sockets application functions, which in turn return a value or false (a type other than "boolean"), should return a value or NULL.
     *
     *
     */
	 
	interface Socket
	{
		
        /**
         * socket_create
		 *
		 * {@see http://php.net/manual/en/function.socket-create.php}
         * @param int $domain 
         * @param int $type 
         * @param int $protocol 
         * 
         * @return resource|null
         */
		public function create(int $domain,int $type, int $protocol);
        
		/**
         * socket_bind
         * 
		 * {@see http://php.net/manual/en/function.socket-bind.php }
		 
         * @param string $address 
         * @param int $port 
         * 
         * @return resource
         */
		public function bind(string $address,int $port):bool;
		
        /**
         * socket_listen
         * 
		 * {@see http://php.net/manual/en/function.socket-listen.php }
         * @param int $backlog  
         * 
         * @return bool
         */
		public function listen(int $backlog):bool;
        
		/**
         * socket_accept
         * 
		 * {@see http://php.net/manual/en/function.socket-accept.php}
		 *@param int $backlog
         * 
         * @return resource | null
         */
		public function accept();
        
		/**
         * socket_clear_error
         *
		 * {@see http://php.net/manual/en/function.socket-clear-error.php }
		 * @param bool $is_resource 
         * 
         * @return void
         */
		public function clearError(bool $is_resource=false);
		
        /**
         * socket_close
         *
         * {@see http://php.net/manual/en/function.socket-close.php }
         *
         * @return void
         */
		public function close();
        
		/**
         * socket_cmsg_space
         * 
		 * {@see http://php.net/manual/en/function.socket-cmsg-space.php}
         * @param int $level  
         * @param int $type  
         * @param int $n  
         * 
         * @return int
         */
		public function cmsgSpace (int $level , int $type , int $n):?int;
        
         /**
          * socket_create_listen
          * 
		  * {@see http://php.net/manual/en/function.socket-create-listen.php}
          * @param int $port 
          * @param int $backlog 
          * 
          * @return resource|null
          */
		 
		public function createListen (int $port, int $backlog);
		
		/**
         * socket_connect
         * 
		 * {@see http://php.net/manual/en/function.socket-connect.php }
         * @param string $address  
         * @param int $port  
         * 
         * @return bool
         */
		public function connect (string $address , int $port):bool;
        
		/**
         * socket_create_pair
         *
		 * {@see http://php.net/manual/en/function.socket-create-pair.php }
         * @param int $domain  
         * @param int $type  
         * @param int $protocol  
         * @param resorce[] $fd  
         * 
         * @return resource[]|null    return===$fd ||null
         */
		public function createPair (int $domain , int $type , int $protocol,array &$fd);
       
	   /**
         * socket_get_option
         * 
		 * {@see http://php.net/manual/en/function.socket-get-option.php}
         * @param int $level  
         * @param int $optname 
         * 
         * @return mixed|null
         */
		public function getOption(int $level , int $optname);
		
        /**
         * socket_getpeername
         * 
		 * {@see http://php.net/manual/en/function.socket-getpeername.php}
         * @param string $address  
         * @param int $port 
         * 
         * @return bool
         */		
		public function getpeername(string &$address , int &$port):bool;
        /**
         * socket_getsockname
         * 
		 * {@see http://php.net/manual/en/function.socket-getsockname.php}
         * @param string $addr  
         * @param int $port 
         * 
         * @return bool
         */
		
		public function getsockname(string &$addr , int &$port):bool;
        /**
         * socket_last_error
         * 
         *  {@see http://php.net/manual/en/function.socket-last-error.php}
		 *
         * @return int
         */
		
		public function lastError():int;
		
        /**
         * socket_read
         * 
		 * {@see http://php.net/manual/en/function.socket-read.php}
         * @param int $length  
         * @param int $type 
         * 
         * @return string|null
         */
		public function read(int $length , int $type=PHP_BINARY_READ):?string;
        
		/**
         * socket_recv
         * 
		 * {@see http://php.net/manual/en/function.socket-recv.php}
         * @param string $buf  
         * @param int $len  
         * @param int $flags  
         * 
         * @return int|null
         */
		public function recv(string &$buf , int $len , int $flags):?int;
        
		/**
         * socket_recvfrom
         * 
		 * {@see http://php.net/manual/en/function.socket-recvfrom.php}
         * @param string $buf  
         * @param int $len  
         * @param int $flags  
         * @param string $name  
         * @param port $port 
         * 
         * @return int|null
         */
		public function recvfrom(string &$buf , int $len , int $flags , string &$name , int &$port=null):?int;
        
		/**
         * socket_recvmsg
         * 
		 * {@see http://php.net/manual/en/function.socket-recvmsg.php}
         * @param array $message 
         * @param int $flags  
         * 
         * @return int|null
         */
		public function recvmsg (array &$message, int $flags = null):?int;
        
		/**
         * socket_select
         * 
		 * {@see http://php.net/manual/en/function.socket-select.php}
         * @param resource[]|null $read  
         * @param resource[]|null $write  
         * @param resource[]|null $except  
         * @param int $tv_sec  
         * @param int $tv_usec  
         * 
         * @return int|null
         */
		public function select(?array &$read , ?array &$write , ?array &$except , int $tv_sec , int $tv_usec ):?int;
		
        /**
         * socket_send
         * 
		 * {@see http://php.net/manual/en/function.socket-send.php }
         * @param string $buf  
         * @param int $len  
         * @param int $flags 
         * 
         * @return int|null
         */
		public function send(string $buf , int $len , int $flags):?int;
        
		/**
         * socket_sendmsg
		 *
         * {@see http://php.net/manual/en/function.socket-sendmsg.php}
         * @param array $message  
         * @param int $flags  
         * 
         * @return int|null
         */
		public function sendmsg (array $message , int $flags ):?int;
		
		
        /**
         * socket_sendto
		 *
         * {@see http://php.net/manual/en/function.socket-sendto.php }
         * @param string  $buf  
         * @param int $len  
         * @param int $flags  
         * @param string $addr  
         * @param int $port  
         * 
         * @return int|null
         */
		public function sendto( string $buf , int $len , int $flags , string $addr , int $port ):?int;
       
	   /**
         * socket_set_block / socket_set_nonblock
         * 
		 * {@see http://php.net/manual/en/function.socket-set-nonblock.php}  
		 * {@see http://php.net/manual/en/function.socket-set-block.php}
		 *
         * @param bool $check 
         * 
         * @return bool
         */
		public function setBlock(bool $check):bool;
        
		/**
         * socket_set_option
         * 
		 * {@see http://php.net/manual/en/function.socket-set-option.php}  
         * @param int $level  
         * @param int $optname  
         * @param mixed $optval  
         * 
         * @return bool
         */
		public function setOption (int $level , int $optname ,$optval ):bool;
        
		/**
         * socket_shutdown
         * 
		 * {@see http://php.net/manual/en/function.socket-shutdown.php }
         * @param int $how  
         * 
         * @return bool
         */
		public function shutdown(int $how = 2):bool;
        
		/**
         * socket_strerror
         * 
		 * {@see http://php.net/manual/en/function.socket-strerror.php }
         * @param int $errno 
         * 
         * @return string|null
         */
		public function strerror(int $errno):?string;
        
		/**
         * socket_write
         * 
		 * {@see http://php.net/manual/en/function.socket-write.php }
         * @param string $buffer 
         * @param int $length  
         * 
         * @return int|null
         */
		public function write (string $buffer, int $length = null):?int;
		
		/**
         * socket_export_stream
         * 
		 * {@see http://php.net/manual/en/function.socket-export-stream.php}
         * @param resource $stream 
         * 
         * @return resource|null
         */
		public function exportStream ();
       
		/**
         * socket_import_stream
         * 
		 * {@see http://php.net/manual/en/function.socket-import-stream.php}
         * @param resource $addr  
         * 
         * @return resource|null
         */
		public function importStream($stream);
        
		/**
		 * socket_addrinfo_bind
		 * 
		 *	{@see http://php.net/manual/en/function.socket-addrinfo-bind.php }
		 * @param resource $addr 
		 * 
		 * @return resource|null
		 */

		public function addrinfoBind ($addr);
       
	    /**
         * socket_addrinfo_connect
         * 
		 * {@see http://php.net/manual/en/function.socket-addrinfo-connect.php}
         * @param resource $addr 
         * 
         * @return resource|null
         */
		public function addrinfoConnect($addr);
		
        /**
         * socket_addrinfo_explain
         * 
		 * {@see http://php.net/manual/en/function.socket-addrinfo-explain.php}
         * @param resource $addr 
         * 
         * @return array|null
         */
		public function addrinfoExplain($addr):?array;
        
		/**
         * socket_addrinfo_lookup
         * 
		 * {@see http://php.net/manual/en/function.socket-addrinfo-lookup.php }
         * @param string $host  
         * @param string $service 
         * @param array $hints 
         * 
         * @return resource[]
         */
		public function addrinfoLookup (string $host , string $service=null, array $hints=null):?array;
        
		/**
         * Sets a socket resource as an object resource if it does not already exist
         * 
         * @param resource $socket 
         * 
         * @return bool
         */
		public function initResource($socket):bool;
		
		/**
         * Returns an object resource or NULL
         * 
         * @return bool
         */
		public function getResource();
        
		/**
         * Checks if the socket is closed.
         * 
         * @return bool
         */
		public function isClose(): bool;
        /**
         * Checks if a socket is operational.
		 *
         * Informs about activation and deactivation for working with the object 
		 *
         * @return bool
         */
		public function isWorks(): bool;

        /**
         * for processing the result. 
		 * Defines actions with errors and processes a successful result.
		 * For example, to evaluate the error that occurred or adjust the result.
         * 
         * @param mixed $answer 
         * 
         * @return bool   for making decisions - whether to process further result
         */
		
		public function onAction (&$answer):bool;
	}
	