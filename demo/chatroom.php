<?php


$server = new \swoole_server("0.0.0.0",8081,SWOOLE_PROCESS,SWOOLE_SOCK_TCP);

$server->clients = [];

$server->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
    $serv->clients[$fd]['fd'] = $fd;
    $serv->send($fd,"Welcome to chatroom!\nPlease set you name, format:--set-name:zhangsan\n");
});

$server->on('receive', function ($serv, $fd, $from_id, $data){
    $user_name = $serv->clients[$fd]['user_name'];
    if (strpos($data, '-set-name:') !== false) {
    	$user_name = substr(trim($data), 11);


    	foreach($serv->clients as $client)
        {
         	$_user_name = $client['user_name'];
         	if ($_user_name == $user_name) {
         	   	$serv->send($fd,"You name has beed taken,Please reset you name, format:--set-name:zhangsan\n");
         	}   
        }

    	$serv->clients[$fd]['user_name'] = $user_name;
    	
    	foreach($serv->connections as $tempFD)
        {
        	if ($fd == $tempFD) {
        		$serv->send($fd,"Welcome ".$user_name."\n");
        	} else {
        		$serv->send($tempFD,$user_name." has enter the chat room.\n");
        	}
            
        }
    } else {
    	if (!$serv->clients[$fd]['user_name']) {
	    	$serv->send($fd,"Please set you name, format:--set-name:zhangsan\n");
	    } else {
	    	foreach($serv->connections as $tempFD)
	        {
	        	if ($fd == $tempFD) {
	        		$serv->send($tempFD, 'you say:'. $data);
	        		continue;
	        	}
	            $serv->send($tempFD,$user_name .':'. $data);
	        }
	    }
    }
    
});

$server->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
    $serv->send($fd,"Good bye! See you next time.\n");
});
$server -> start();
