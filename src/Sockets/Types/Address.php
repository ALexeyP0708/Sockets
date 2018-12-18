<?php
	namespace Alpa\Sockets\Types;
	use Alpa\Classes\InitType;
	class Address  extends InitType
	{
		protected $argtoprop=false;
		public $address;
		public $port;
	}