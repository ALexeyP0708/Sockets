<?php
	namespace Alpa\Sockets\Interfaces;

	interface EventsMap
	{
		public function runActionsForKey($event,$key,&...$args) :bool;
	}