<?php
	namespace Alpa\Classes;
	abstract class  InitType 
	{	
		protected $argtoprop=false;
		protected $defaults=[];
		protected $classes=[];
		protected $keysPublicProp;
		public function __construct (...$rest)
		{	
			$this->defaults=(object)$this->defaults;
			$this->classes=(object)$this->classes;
			$this->keysPublicProp=$this->getKeysPublicPropSelf();
			$params=[];
			if($this->argtoprop){
				$params=$rest;
				$defaults=[];
			} else {
				$params=!empty($rest[0])?$rest[0]:[];
				$defaults=!empty($rest[1])?$rest[1]:[];
			}
			$this->init($params,$defaults);
		}
		public function init($params,$defaults=[])
		{	
			foreach($defaults as $key=>&$value){
				$this->defaults->$key=&$value;
			}
			unset($value);
			$type='enumerate';
			if(is_array($params)){
				$keys=array_keys($params);
				foreach($keys as $value){
					if(!is_int($value)){
						$type='assoc';
						break;
					}
				}
				if($type=='assoc'){
					$params=(object)$params;
				}
			}else if(is_object($params))
			{
				$type='assoc';
			}
			
			//$props=array_keys(get_object_vars($this));
			
			$props=$this->keysPublicProp;
			foreach($props as $k=>$key){
				if($type=='assoc' && isset($params->$key)){
					$this->$key=&$params->$key;
				} else if($type=='enumerate' && isset($params[$k])){
					$this->$key=&$params[$k];
				} else if(isset($this->defaults->$key)){
					$this->$key=&$this->defaults->$key;
				} 
			}
			$this->validateProperties($this);
		}
		
		 /**
         * 
         * 
         * @param object|array $props
         * 
         * @return void
         */
		public function validateProperties($props)
		{
				return $props;
		}
		protected function getKeysPublicPropSelf(){
			$props=[];
			$reflectProps=(new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
			foreach($reflectProps as $reflect){
				$props[]=$reflect->getName();
			}
			return $props;
		}
	}