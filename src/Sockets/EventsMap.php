<?php
	namespace Alpa\Sockets;
	/*
		- Добавить активацию - деактивацию ключа в конкретном событии для конкретной сборки. Такой подход позволяет динамически пропускать действия для ошибок в конкретных случаях
		- Добавить разовую деактивацию ключа.
		- добавить разовую активацию ключа. 
		- удалять сборки с событий.
	*/
	class EventsMap implements Interfaces\EventsMap
	{
		protected $kits;
		protected $actions;
		protected $map;
		public function __construct($errors_map=[],$actions=[],$kits=[])
		{
			$this->actions=(object)[];
			$this->kits=(object)[];
			$this->map=(object)[];
			$this->init($errors_map,$actions,$kits);
		 }
         /**
          * Generates data for work.
          * 
          * @param object $errors_map Structure - ['event'=>['name_kit',['keys'=>['key',...],'actions'=>['name_action',function(){}, ...]],...] 
          * @param object $actions  Structure - ['actions'=>['name_action'=>'mixed data',...]]
          * @param object $kits  Structure - ['name_kit'=>['keys'=>['key',...],'actions'=>['name_action',function(){}, ...]]]
          * 
          * @return void
          */
		protected function init($errors_map,$actions,$kits)
		{
			$this->setActions($actions);
			foreach($kits as $name=>$kit){
				$this->setKit($name,$kit);
			}
			foreach($errors_map as $event=>$kits){
				foreach($kits as $kit){
					$this->addEvent($event,$kit);
				}
			}
		}
        /**
         * Adds an kit for the event.
         * 
         * @param string $event  The event for which the corresponding kit will be used 
         * @param object|string $kit if the type is equal to the string, it will find a kit with the corresponding name
         * @param string $position last|first  It determines the position of the kit  in the kits stack. 
         * 
         * @return void
         */
		
		public function addEvent($event,$kit,$position='last')
		{
			if(!isset($this->map->$event)){
				$this->map->$event=[];
			}
			if(is_string($kit)){
				if($kit=$this->getKit($kit)){
					if($position=='last'){
						$this->map->$event[]=$kit;
					} else {
						array_splice($this->map->$event,0,0,$kit);
					}
				}
			} else {
				if(is_array($kit)){
					$kit=(object)$kit;
				}
				$check=true;
				foreach(['keys','actions'] as $prop){
					if(!isset($kit->$prop)){
						trigger_error("No \"{$prop}\" property  in map for \"{$event}\" event.");
						$check=false;
					} 
				}
				if($check==true){
					foreach($kit->actions as $key=>&$action){
						if(is_string($action) ){
							if(isset($this->actions->$action)){
								$action=$this->actions->$action;
							} 
						}
					}
					unset($action);
					if($position=='last'){
						$this->map->$event[]=$kit;	
					} else {
						array_splice($this->map->$event,0,0,$kit);
					}
				}		
			}
		}
		public function getMap()
		{
			return $this->map;
		}
        /**
         * for key created a kit  from kits associated with the event.
         * Enumerates collections (kits) fore event and searches for the key in the "keys" property of each collection. If the key is present, then the collection is merged with the result set.
         * @param string $event 
         * @param string|int $key  
         * 
         * @return object|bool Structure (object)['keys'=>[$key],'actions'=>['name_action'.......]]
         */
		
		public function getKitForKey($event,$key)
		{ 
			$answer=false;
			$key=(string)$key;
			if(isset($this->map->$event)){
				foreach($this->map->$event as $kit){
					if(in_array($key,$kit->keys)){
						if($answer==false){
							$answer=(object)['keys'=>[$key],'actions'=>[]];
						} 
						foreach($kit->actions as $name=>$action){
							if(!is_numeric($name)){
								$answer->actions[$name]=$action;
							} else {
								$answer->actions[]=$action;
							}
						}
					}
				}
				unset($kit);
			} 
			return $answer;
		}
		 /**
         * for key created a kit  from kits associated with the event.
         * Enumerates collections (kits) fore event and searches for the key in the "keys" property of each collection. If the key is present, then the collection is merged with the result set.
		 * Difference from getKitForKey.
		 * if the key is not found for the specified event, then it looks for the key in the event "global".If the key is not found, it will look for the key "*".
		 * FIND Map:  Find name_key In "name_event" --> Find name_key In"global" -->Find * In "name_event" -- > Find * In "global"
         * @param string $event 
         * @param string|int $key  
         * 
         * @return object|bool Structure (object)['keys'=>[$key],'actions'=>['name_action'.......]]
         */
		public function toFormKitForKey($event,$key)
		{
			$answer=(object)[];
			if($kit=$this->getKitForKey($event,$key)){
				$answer=$kit;
			} else if($global_kit=$this->getKitForKey('global',$key)){
				$answer=$global_kit;
			} else if($kit=$this->getKitForKey($event,'*')){
				$answer=$kit;
			} else if( $global_kit=$this->getKitForKey('global','*')){
				$answer=$global_kit;
			}
			return $answer;
		}
		
		public function runActionsForKey($event,$key,&...$args) :bool
		{
			$kit=$this->toFormKitForKey($event,$key);
			$result=true;
			$check=true;
			foreach($kit->actions as $name=>$action){
				if(is_string($action)){
					$call=$this->actions->$action;
					if(isset($this->actions->$action) && is_callable($call)){
						$check=$call($event,$key,$name,...$args);
					}
				}if(is_callable($action)){
					$check=$action($event,$key,$name,...$args);
				}					
				if($check===false){
					$result=false;
				}
			}
			return $result;
		}
        /**
         *  Add or edit  kit 
         * @param string $name 
         * @param array|object $kit 
         * 
         * @return void
         */
		public function setKit($name,$kit)
		{
			if(is_array($kit)){
				$kit=(object)$kit;
			}
			$new_kit=(object)['keys'=>[],'actions'=>[]];
			$check=true;
			foreach(['keys','actions'] as $prop){
				if(!isset($kit->$prop)){
					trigger_error("No \"{$prop}\" property  in map for \"{$event}\" event.");
					$check=false;
				} 
			}
			if($check==true){
				$new_kit->keys=$kit->keys;
				foreach($kit->actions as $key=>$action){
					if(is_string($action) ){
						if(is_numeric($key)){
							$key=$action;
						}
						if(isset($this->actions->$action)){
							$new_kit->actions[$key]=$this->actions->$action;
						}
					} else if(is_callable($action) ){
						$new_kit->actions[]=$action;
					}
				}
				if(!isset($this->kits->$name)){
					$this->kits->$name=(object)[];
				}
				$this->kits->$name=$new_kit;
			}
		}
		public function getKit($name)
		{
			if(isset($this->kits->$name)){
				return $this->kits->$name;
			} else {
				return false;
			}
		}
        /**
         *  Add or replace  actions
         * 
         * @param array $actions Structure -['name_action'=>'action'] 
         * 
         * @return void
         */
		public function setActions ($actions)
		{
			foreach($actions as $name=>$action){
				$this->actions->$name=$action;
			}
		}
        /**
         * In kit add action 
         * If there is no kit, then will create
         * @param string $name 
         * @param mixed $action if the type is equal to the string, it will find a action with the corresponding name
         * 
         * @return void
         */
		public function addActionInKit($name,$action)
		{
			if(!isset($this->kits->$name)){
				$this->kits->$name=(object)['keys'=>[],'actions'=>[]];
			}
			if(!in_array($action,$this->kits->$name->actions)){
				if(is_string($action) && isset($this->actions->$action)){
					$this->kits->$name->actions[]=$this->actions->$action;
				} else {
					$this->kits->$name->actions[]=$action;
				}
			}	
		}
		
		public function removeActionInKit($name,$action)
		{
			
		}
		
		 /**
         * In kit add key . 
		 * if there is no kit, then will create
         * @param string $name 
         * @param mixed $key 
         * 
         * @return void
         */
		public function addKeyInKit($name,$key)
		{
			if(!isset($this->kits->$name)){
				$this->kits->$name=(object)['keys'=>[],'actions'=>[]];
			}
			if(!in_array($key,$this->kits->$name->keys)){
				$this->kits->$name->keys[]=$key;
			}	
		}
		public function removeKeyInKit($name,$key)
		{
			
		}
		public function importMaps()
		{
			
		}
		public function exportMaps(ErrorMaps $map)
		{
			
		}
	}