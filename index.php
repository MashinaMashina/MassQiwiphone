<?php

error_reporting(0);

define('DIR', __DIR__);

require DIR.'/functions.php';
require DIR.'/request.php';

$token = file_read(DIR.'/token.txt');
$is_test = file_read(DIR.'/is_test.txt') ? true : false;

$lines = explode("\n", file_read(DIR.'/numbers.txt'));

$success_file = save_next_name('success');
$error_file = save_next_name('error');

foreach($lines as $line)
{
	$data = explode('-', $line);
	
	$data[0] = trim($data[0]);
	$data[1] = trim($data[1]);
	
	if(strlen($data[0]) > 10) $data[0] = substr($data[0], -10);
	
	echo "Pay 7{$data[0]} - {$data[1]} RUB";
	
	$phone = array(
		'phone' => '7'.$data[0]
	);
	
	$request = new request('https://qiwi.com/mobile/detect.action');
	$request->post($phone);
	$request->send();
	
	$json = json_decode($request->response, true);
	
	$operator = $json['message'];
	
	if( $is_test) $operator++;
	
	$payment = array(
		'id' => ''. preg_replace('#[^0-9]#', '', microtime()), // needs string
		'sum' => array(
			'amount' => $data[1],
			'currency' => '643' // needs string
		),
		'paymentMethod' => array(
			'type' => 'Account',
			'accountId' => '643', // needs string
		),
		'fields' => array(
			'account' => ''.$data[0] // needs string
		)
	);
	
	$url = 'https://edge.qiwi.com/sinap/api/v2/terms/'.$operator.'/payments';
	
	$request = new request($url);
	$request->payload($payment);
	$request->set_headers('Authorization', 'Bearer '.$token);
	$request->send();
	
	$out_json = json_decode($request->response, true);
	
	if( $out_json['transaction']['state']['code'] === 'Accepted')
	{
		echo " - success \r\n";
		file_append($success_file, trim($line)."\r\n");
	}
	else
	{
		echo " - error ({$out_json['code']}: {$out_json['message']}) \r\n";
		file_append($error_file, trim($line)."\r\n");
	}
	
	sleep(0.1);
}