<?php

function doSniff()
{
	global $output, $contact;
	$w = getPool();

	$onSyncResult = getFromCache('whatsniffer'.$contact.'onSyncResult');
	if ($onSyncResult == null) {
		$w->eventManager()->bind('onGetSyncResult', 'onSyncResult');
		$w->sendSync(array($contact));
	} else {
		$result['isUser'] = $onSyncResult;
	}

	$w->eventManager()->bind('onGetRequestLastSeen', 'onGetRequestLastSeen');
	$w->sendGetRequestLastSeen($contact);

	$w->eventManager()->bind('onPresenceAvailable', 'onPresenceAvailable');
	$w->sendPresenceSubscription($contact);

	$onGetStatus = getFromCache('whatsniffer'.$contact.'onGetStatus');
	if ($onGetStatus == null) {
		$w->eventManager()->bind('onGetStatus', 'onGetStatus');
		$w->sendGetStatuses(array($contact));
	} else {
		$result['status'] = $onGetStatus;
	}

	$onGetProfilePicture = getFromCache('whatsniffer'.$contact.'onGetProfilePicture');
	if ($onGetProfilePicture == null) {
		$w->eventManager()->bind('onGetProfilePicture', 'onGetProfilePicture');
		$w->sendGetProfilePicture($contact, false);
	} else {
		$result['picture'] = $onGetProfilePicture;
	}
}

function onSyncResult($result)
{
	global $output, $contact;
	$output['isUser'] = null;
    foreach ($result->existing as $number) {
        $output['isUser'] = true;
        setOnCache('whatsniffer'.$contact.'isUser', true);
        return;
    }
    foreach ($result->nonExisting as $number) {
        $output['isUser'] = false;
        setOnCache('whatsniffer'.$contact.'isUser', false, 1);
        return;
    }
}

function onPresenceAvailable($mynumber, $from) 
{
	global $output, $contact;
	$output['isOnline'] = true;
}

function onGetStatus($mynumber, $from, $requested, $id, $time, $data)
{
	global $output, $contact;
	$output['status']['text'] = $data;
	$output['status']['time'] = $time;
	setOnCache('whatsniffer'.$contact.'onGetStatus', $output['status'], 86400);
}

function onGetRequestLastSeen($mynumber, $from, $id, $seconds)
{
	global $output, $contact;
	$output['lastConnection'] = $seconds;
}

function onGetProfilePicture($mynumber, $from, $type, $data) 
{
	global $output, $contact;
	$output['picture'] = base64_encode($data);
	setOnCache('whatsniffer'.$contact.'onGetProfilePicture', $output['picture'], 86400);
}

function shouldContinue() 
{
	global $output;

	if (isset($output['isUser']) && !$output['isUser']) {
		return false;
	}

	if (isset($output['isUser']) && $output['isUser'] && isset($output['status']) && isset($output['picture']) && (isset($output['lastConnection']) || isset($output['isOnline']))) {
		return false;
	}

	return true;
}

function defineOnlineStatus()
{
	global $output;
	
	if (isset($output['isUser']) && !$output['isUser']) {
		$output['online'] = -1;
		return;
	}

	if (isset($output['isOnline']) && $output['isOnline']) {
		$output['online'] = 1;
		unset($output['isOnline']);
		unset($output['lastConnection']);
		return;
	}

	if (!isset($output['isOnline']) && isset($output['lastConnection'])) {
		$output['online'] = 2;
		unset($output['isOnline']);
		return;
	}

	if (!isset($output['isOnline']) && !isset($output['lastConnection'])) {
		$output['online'] = 3;
		unset($output['isOnline']);
		unset($output['lastConnection']);
		return;
	}
}

function getFromCache($key) 
{
	if(!class_exists('Memcached')) {
		return null;
	}
	$m = new Memcached();
	$m->addServer('localhost', 11211);
	return $m->get($key);
}

function setOnCache($key, $value, $secs = 0) 
{
	if(!class_exists('Memcached')) {
		return null;
	}
	$m = new Memcached();
	$m->addServer('localhost', 11211);
	$m->set($key, $value, $secs);
}

?>