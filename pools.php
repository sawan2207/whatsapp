<?php

function getPool()
{
	$username = "12564454712"; 
	$nickname = "Andrea"; 
	$password = "UEFcAlo7vYyQCz4N00jAW7CDAug="; 
	$debug = false;

	$w = new WhatsProt($username, $nickname, $debug);
	$w->connect(); 
	$w->loginWithPassword($password);
	return $w;
}

?>