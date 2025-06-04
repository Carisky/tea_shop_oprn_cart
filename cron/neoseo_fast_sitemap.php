<?php

// Version
define('VERSION', '3.0.2.0');
// Configuration
require_once(dirname(__FILE__) . '/../config.php');
require_once(DIR_SYSTEM . 'startup.php');
// Registry
$registry = new Registry();

// Config
$config = new Config();
$registry->set('config', $config);

// Request
$registry->set('request', new Request());

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

$config->set('config_url', HTTP_SERVER);
$config->set('config_ssl', HTTPS_SERVER);

// Store
if (isset($argv[2])) {
	$store_id = $argv[2];
} else {
	$store_id = 0;
}


// Settings
$query = $db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' OR store_id = '" . (int)$config->get('config_store_id') . "' ORDER BY store_id ASC");

foreach ($query->rows as $result) {
	if (!$result['serialized']) {
		$config->set($result['key'], $result['value']);
	} else {
		$config->set($result['key'], json_decode($result['value'], true));
	}
}

if (!$config->get('neoseo_fast_sitemap_status')) {
	exit();
}

// Url
$url = new Url($config->get('config_url'), $config->get('config_secure') ? $config->get('config_ssl') : $config->get('config_url'));
$registry->set('url', $url);

// Log
$log = new Log($config->get('config_error_filename'));
$registry->set('log', $log);

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

function log1($message) {
	global $config;

	file_put_contents(DIR_LOGS . "neoseo_fast_sitemap.log" , date("Y-m-d H:i:s - ") . $message . "\r\n", FILE_APPEND);
}

// Error Handler
function error_handler($errno, $errstr, $errfile, $errline)
{

	if (0 === error_reporting())
		return TRUE;
	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;
		default:
			$error = 'Unknown';
			break;
	}
	log1('PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	return TRUE;
}

// Error Handler
set_error_handler('error_handler');

// Request
$request = new Request();
$registry->set('request', $request);

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$registry->set('response', $response);

// Cache
$cache = new Cache('file');
$registry->set('cache', $cache);

$languages = array();

$query = $db->query("SELECT * FROM `" . DB_PREFIX . "language`");

foreach ($query->rows as $result) {
	$languages[$result['code']] = $result;
}

$config->set('config_language_id', $languages[$config->get('config_language')]['language_id']);

// Language
$language = new Language($languages[$config->get('config_admin_language')]['directory']);
$language->load($languages[$config->get('config_admin_language')]['directory']);
$registry->set('language', $language);

// Document
$registry->set('document', new Document());

// Currency
$registry->set('currency', new Cart\Currency($registry));

// Weight
$registry->set('weight', new Cart\Weight($registry));

// Length
$registry->set('length', new Cart\Length($registry));

// User
$registry->set('user', new Cart\User($registry));


// Event
$event = new Event($registry);
$registry->set('event', $event);

// Event Register
if ($config->has('action_event')) {
	foreach ($config->get('action_event') as $key => $value) {
		$event->register($key, new Action($value));
	}
}


$method = 'generateLinks';

if (isset($argv[1])) {
	$method = $argv[1];
}

$loader->model('feed/neoseo_fast_sitemap_filter_seogen');
$registry->get('model_feed_neoseo_fast_sitemap_filter_seogen')->$method();

