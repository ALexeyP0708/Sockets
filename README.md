# PHP Alpa\Sockets  
 ### Быстрый старт.  
 \>=PHP 7.1   
Данное приложение упрощает работу с функциями PHP расширения Sockets, применяя объектно-ориентированный подход. *( Проект является на стадии разработки. )*
  
*Перед ознакомлением необходимо знать как работать с PHP расширением Sockets.*   
Документацию по API Можно сформировать посредством PHPDocumentor v.3 Alpha-3
##### Файлы:
	events_maps\events_map_default.php - формирует карту событий c помощью EventsMap
##### NameSpaces:
Alpa\Sockets  
Alpa\Sockets\Interfaces    
Alpa\Sockets\Types  
##### Интерфейсы:
**Alpa\Sockets\Interfaces\\**  
* **Error** -  Предназначен для написания класса отслеживания ошибок PHP расширения Sockets.  
* **Socket** -  Для описания собственного класса Сокетов.
* **EventsMap** - Для  описания класса событий 

##### Классы:
**Alpa\Sockets\\**
*  **Socket** - Класс реализующий методы работы с функциями PHP расширения Sockets.

>> ##### Методы для быстрого старта:
>* **create($domain=AF_INET,$type=SOCK_STREAM,$protocol=SOL_TCP)** реализует socket_create 
>* **bind($address,$port=0)** реализует  socket_bind
>* **listen($backlog=0)** реализует  socket_listen 
>* **setBlock($block=false)** реализует  socket_set_block  и socket_set_nonblock 
>* **accept ()** реализует socket_accept 
>* **connect($address,$port=0)** реализует  socket_connect
>* **select(?array &$read=null , ?array &$write=null , ?array &$except=null , int $tv_sec=0 , int $tv_usec = 0)** реализует socket_select  
>* **getpeername (&$address=null,&$port=null)** реализует socket_getpeername  
>* **getsockname (&$address=null,&$port=null)** реализует socket_getsockname  
>* **setOption($optname,$optval,$level=SOL_SOCKET)** реализует socket_set_option  
>* **getOption($optname,$level=SOL_SOCKET)** реализует socket_get_option  
>* **isWork()**  возвращает статус процесса. если true то будет выполняться. если false то заблокирован.  $name - create|bind|listen|accept|select|process|loop|close  
>* **isClose()**  возвращает статус сокета закрыт или нет  
>* **getResource()** возвращает ресурс сокета.  возвращает
>* **initResource($socket,$params=[])** инициализирует ресурс сокета
>* **read ($length=1024, $type = PHP_BINARY_READ)** -реализует  socket_read 
>* **write ($buffer, $length=null)** - реализует socket_write
>* **setOption($optname,$optval,$level=SOL_SOCKET)** 
>* **getOption($optname,$level=SOL_SOCKET)** 
* **EventsMap** -  класс который характеризует поведение сокетов при отлавливании ошибок или при формировании результата. 

**Alpa\Sockets\Types\\**
* **ParamsSocket** - Определяет параметры для  сокета.
* **Address** - Инициализирует свойства для работы с параметрами адреса.
* **Pair** - инициализирует свойства master и slave для пары ресурсов. созданные через метод createPair
* **Status** -  определяет состояние методов
>>##### Методы:

	
##### Принцип действия на примере:  
Карта событий - файл events_maps\events_map_default.php где под каждую ошибку сформированы дейстия. 
и создан обьект класса EventsMap.

 *Внимание карта событий еще не сформирована. В качестве примера, пока сформирована карта для 2х ошибок в среде Windows. 10054 - разрыв соединения и 10061 - невозможность подключения.  Для Linux возможны другие ошибки. Их необходимо указать в карте.*  
 Весь код предоставлен в ознакомительных целях. Его работа не гарантируется. Рабочие коды в одноименных файлах.
 ```PHP
<?php 
<?php
	#! php -f test_socket_class.php
	use Alpa\Sockets\Socket;
	use Alpa\Sockets\Types;
	include_once __DIR__.'/../autoload.php'; // autoload for test. PSR-4
	//include_once __DIR__.'/../../../autoload.php';// composer autoload
	$params=[
		'socket_error_charsets'=>'WINDOWS-1251', // устанавливаем кодировку для ошибок
		'error_stifle'=>true, // подавление ошибок вкл.выкл. пример @socket_create()  
		'events_map'=>__DIR__.'\..\events_maps\events_map_default.php' // карта событий
	];
	$server=new Socket($params);
	$server->create(); 
	$server->bind('127.0.0.71',8000);
	$server->listen(); 
	$server->setBlock(false);
	$clients=[];
	$resources=[];
	$read=[];
	$write=[];
	while($server->isWorks()){ // запускаем цыкл isWork - определяет рабочее состояние
		do { //отлавливаем клиентские сокеты которые подсоеденились
			$client=$server->accept(); 
			if(!is_null($client)){
				// наследуем параметры сокет сервера
				$client_params=array_merge($params,['how_created'=>'socket_accept','domain'=>AF_INET,'type'=>SOCK_STREAM,'protocol'=>SOL_TCP]);
				// привязываем клиентский сокет ресурс к обькту
				$client=new Socket($client_params,$client);
				$clients[]=$client;
				$str="Hello!\r\n";// шлем приветствие
				$client->write($str);
			} 
		} while(!is_null($client));
		if(empty($clients)){
			continue;
		}
		$on_resources=[];
		$on_clients=[];
		foreach($clients as $key=>$client){ // исключаем сокеты которые закрыты и выбираем только рабочие сокеты.
			if($client->isClose()){
				unset($clients[$key]);
				continue;
			}
			if($client->isWorks()){
				$on_resources[]=$client->getResource();
				$on_clients[]=$client;
			}
		}
		$read=$on_resources;
		$write=$on_resources;
		$check=$server->select($read,$write); // проверяем сокеты на состояние чтение записи
		$pool=$read; // создаем общий пул сокетов
		foreach($write as $k=>$r){
			if(!in_array($r,$pool)){
				$pool[]=$r;
			}
		}			
		if(!empty($pool) && !empty($check)){
			foreach($pool as $resource){ // 
				if(in_array($resource,$read)){ // проверяем на состояние чтения сокета 
					if(($key=array_search($resource,$on_resources))!==false){ // определяем обьект ресурса
						$client=$on_clients[$key]; 
						if($answer=$client->read()){ // читаем данные и преобразуем в конкретный формат.							
							$answer=iconv('IBM866', 'UTF-8', $answer);// if run telnet in windows and/or Russia locale. 
							echo $answer; 
						}
					}
				}
			}
		}
		usleep(5);
	}
 ```
 Клиент.  
```PHP
<?php
	# php -f test_connect_socket_class.php 
	// демон для соединения с сервером
	include_once __DIR__.'/../autoload.php'; // autoload for test. PSR-4
	//include_once __DIR__.'/../../../autoload.php';// composer autoload
	use Alpa\Sockets\Socket;
	use Alpa\Sockets\Types;
	$params=[
		'socket_error_charsets'=>'WINDOWS-1251',
		'events_map'=>__DIR__.'\..\events_maps\events_map_default.php'
	];	
	$is_connect=false;
	$clients=[];
	//$params= new Types\ParamsSocketClient($params);	
	$socket=new Socket($params);
	$socket->create();
	//$socket->create();
	$init_connect=false;
	//$clients=new Types\SelectedPorts();
	while($socket->isWorks() && true){
		if($is_connect===false){
			$is_connect=$socket->connect('127.0.0.71','8000');
			if($is_connect===true){
				$clients[]=$socket->getResource();
			}
		} else {
			$read=$clients;
			$write=$clients;
			$selected=$socket->select($read,$write); //['read','write','mixed','all','except']
			if(!empty($selected)){
				if((in_array($socket->getResource(),$read))){
					$answer=$socket->read();
					$answer=iconv('IBM866', 'UTF-8', $answer);
					echo $answer;
				} 
				if((in_array($socket->getResource(),$write) && $init_connect==false)){
					$init_connect=true;
					$socket->write("Hello! My name Alx!\r\n");
				}
			}
		}
		usleep(10);
	}
```
Карта ошибок (описание) файла /errors_maps/errors_map_default.php
Карта ошибок помогает в 2х ситуациях.
Первае- это упрощает код. исключаяя избыточность в нем.
Вторая- это административная. Когда появляются не устойчивые соединения или непонятный результат, 
то применяется косвенное вмешательство в управление не изменяя основной код. А также возможность фомрировать свое поведение сокета при ошибках. 
 
```PHP
<?php
	use Alpa\Sockets\EventsMap;
	// keys=>[0] - 0 for success event
	$actions=[
			/*обработчики действий. Как правило пользователь их определяет  
				$event - событие(имя функции PHP Sockets) 
				$key - код ошибки . Если 0 то код успешного выполнения.
				$action - название действия 
				$socket - обьект сокета кем был вызван.
				$params- массив аргументов события  все передаются по ссылке изминяя их можно изменить в конечном результате 
				$answer - результат события. Передается по ссылке. Изминяяя его можно изменить результат. И исходя из этого будет решаться дальнейшие действия.
				@return bool - если true (по умолчанию) то  продолжит запланированные действия.  (вывод ошибок на экран, регистрация данных деактивация методов)
			*/
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
			// если значени строка, то привяжет действия к другому действию которое имеет обьект Closure и передаст в него параметры. 
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
	/*
		Сборки.
		С помощью их определяем комплекс действий для конкретных ключей.
		"*" - действие по умолчанию если не будет найдет ключ в сборках.
		Если ключи определены а действия нет - то будут генерироваться ошибки.
	*/
	$kits=[
		'shutdown_process'=>['keys'=>['*',10054],'actions'=>['shutdown_socket','close_socket']],
		'overlook'=>['keys'=>[0,10061],'actions'=>[ function (){ return false; }]], // если указать  return true то будет генерироваться на дисплей ошибка.
	];
	/*
		События - к каждому событияю привязываются сборки. 
		Если в событии сборки не определены, то обращается к событию "global" и ищет там ключ. 
		порядок поиска ключа, если не найден. 
		event+key => global+key => event+* => global+key 
		Собираются все действия для данного ключа в данном событии. 
		В событиях можно напрямую указывать карту ['event'=>[['keys'=>[100,101],'actions'=>[function(){/*код*/},'name_action']]]]
		но тогда управлять в калбеках действиями и ключами не представится возможности. 
		Класс EventsMap еще в разработке. еще не определены методы для управления действиями.
		
		При написании кода данным класом, всегда обращайте внимание на карту событий чтобы устовить конкретное поведение.
	*/
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
```

