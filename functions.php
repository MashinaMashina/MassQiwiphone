<?php

function file_read($filename)
{
	$handle = fopen($filename, 'r');
	$size = filesize($filename);
	
	if($size === 0)
	{
		return '';
	}
	
	$content = fread($handle, $size);
	fclose($handle);
	
	return $content;
}

function file_write($filename, $content = '')
{
	$handle = fopen($filename, 'w');
	$result = fwrite($handle, $content);
	fclose($handle);
	
	return $result;
}

function file_append($filename, $content = '')
{
	$handle = fopen($filename, 'a');
	$result = fwrite($handle, $content);
	fclose($handle);
	
	return $result;
}

function save_next_name($name = '')
{
	$first_filename = DIR."/data/{$name}.txt";
	
	if( !file_exists($first_filename) or trim(file_read($first_filename)) === '') return $first_filename;
	
	
	$i = 1;
	do{
		$second_filename = DIR."/data/{$name} ({$i}).txt";
		$i++;
	}while( file_exists($second_filename));
	
	copy($first_filename, $second_filename);
	file_write($first_filename, '');
	
	return $first_filename;
}