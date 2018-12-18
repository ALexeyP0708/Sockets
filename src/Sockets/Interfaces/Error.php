<?php
	namespace Alpa\Sockets\Interfaces;

	interface Error
	{	
		public function trigger( int $errno ,string $err_str, array $trace_point);
	}