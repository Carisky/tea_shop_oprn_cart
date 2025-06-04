<?php

/**
 * Baselinker integration file
 * @author Sewer Skrzypiński <shops@baselinker.com>
 * @version 4
 * @package baselinker
 */

/** -----------------------------------------------------------------------------------------------------------
 * Settings to be filled in by the seller (all values enclosed in apostrophes)
 */
$options['baselinker_pass'] = 'bc96en457tdpveq05p9r5p2onqo46his';		// communication password, provided by Baselinker on the connection form

$options['db_host'] = 'localhost';		// database host (commonly localhost)
$options['db_user'] = 'wrapxpl_jz';		// database user
$options['db_pass'] = 'fOkbVEcw3sfE';		// database password
$options['db_name'] = 'wrapxpl_jz';		// database name
$options['db_prefix'] = 'szl_';			// database table prefix, e.g. oc_, leave empty to detect automatically

$options['images_folder'] = 'https://jedwabny-szlak.pl/image/';  // root URL for the images folder (end with a single slash)

$options['special_price'] = 1;			// use discounted prices if available (0 - no, 1 - yes)
$options['charset'] = 'UTF-8';			// database character encoding, typically UTF-8
$options['store_root'] = '';			// full path to the opencart installation directory, leave blank to detect automatically

$options['def_tax'] = 23;			// default tax rate, expressed as a percentage

$options['store_id'] = '';		// store ID for multi-store opencart setups
$options['currency'] = 'PLN';	// import product prices in the specified currency (3-letter ISO format)
$options['customer_group'] = 1; // customer group ID used in new orders and to calculate product prices
$options['lang'] = 'pl-pl'; // the language to be used for product translations (either code or name, exactly as it is displayed in opencart)
$options['want_invoice_cf'] = ''; // custom order field number indicating the need to generate an invoice, field_no:value, e.g. 1:2
$options['nip_cf'] = ''; // custom order field number where buyer's tax ID is stored, e.g. 2
$options['status_paid'] = ''; // payment methods codes and their respective "paid" statuses, e.g. payu: 2, bank_transfer: 3, etc. (fill out if payments not detected automatically!)
$options['active_stock_only'] = 0; // interpret inactive products as unavailable, regardless of the actual stock quantity (0 - no, 1 - yes)
$options['split_discounts'] = 0; // spread discounts evenly over all line items (0 - no, 1 - yes)
$options['vean_fld'] = ''; // name of the field in product_option_value table corresponding to the EAN of a combination
$options['sku_fld'] = 'sku'; // name of the field that holds product's SKU (typically sku or model)
$options['geo_zone'] = 'pol'; // store's geo zone name

/******* USER SETTINGS END HERE *******/




error_reporting(E_ERROR | E_WARNING);

date_default_timezone_set('Europe/Warsaw');
/** -----------------------------------------------------------------------------------------------------------
 * Connection control functions (Conn_*) and database operation functions (DB_*)
 *	- Same for all store platforms
 *	- Do not edit the code below
 */
 
 
 
/**
 * Definicja funkcji json_encode oraz json_decode dla PHP4 (istnieją domyślnie w PHP5.2), iconv() dla tablic, oraz array_walk_recursive()
 * Nie należy edytować. Credits goes to Steve http://usphp.com/manual/en/function.json-encode.php#82904
 */
if (!function_exists('json_encode'))
{
    function json_encode($a=false,$is_key=false)
    {if (is_null($a)) return 'null';if ($a === false) return 'false';if ($a === true) return 'true';
    if (is_scalar($a)){if(is_int($a)&&$is_key){return '"'.$a.'"';} if (is_float($a)){return floatval(str_replace(",", ".", strval($a)));}if (is_string($a)){
    static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';} else return $a;} $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)){if (key($a) !== $i){$isList = false;break;}}
    $result = array(); if ($isList){foreach ($a as $v) $result[] = json_encode($v); return '[' . join(',', $result) . ']';}
    else {foreach ($a as $k => $v) $result[] = json_encode($k,true).':'.json_encode($v); return '{' . join(',', $result) . '}';}}
}
if (!function_exists('json_decode'))
{
    function json_decode($json, $assoc = true)
    {$comment = false; $out = '$x='; for ($i=0; $i<strlen($json); $i++) { if (!$comment) {if (($json[$i] == '{') || ($json[$i] == '['))
    $out .= ' array('; else if (($json[$i] == '}') || ($json[$i] == ']'))   $out .= ')'; else if ($json[$i] == ':')    $out .= '=>';
    else $out .= $json[$i]; } else $out .= $json[$i]; if ($json[$i] == '"' && $json[($i-1)]!="\\")    $comment = !$comment;} eval($out . ';'); return $x;}
}
if (!function_exists('array_walk_recursive'))
{
    function array_walk_recursive(&$input, $funcname, $userdata = "")
    {if (!is_callable($funcname)){return false;}if (!is_array($input)){return false;}foreach ($input AS $key => $value){
    if (is_array($input[$key])){array_walk_recursive($input[$key], $funcname, $userdata);}else{$saved_value = $value;
    if (!empty($userdata)){$funcname($value, $key, $userdata);}else{$funcname($value, $key);}if ($value != $saved_value)
    {$input[$key] = $value;}}}return true;}
}

function array_iconv(&$val, $key, $userdata)
{$val = iconv($userdata[0], $userdata[1], $val);}
function recursive_iconv($in_charset, $out_charset, $arr)
{if (!is_array($arr)){return iconv($in_charset, $out_charset, $arr);}$ret = $arr;
array_walk_recursive($ret, "array_iconv", array($in_charset, $out_charset));return $ret;} 


/**
 * Funkcje wykonujące zapytania SQL
 */
function DB_Query($sql)
{
	global	$dbh;

	if (func_num_args() > 1){$i = 0; foreach(func_get_args() as $val){if ($i==0){$i++; continue;}$sql = str_replace("{".($i-1)."}", substr($dbh->quote($val), 1, -1), $sql); $i++;}}

	if (!($sth = $dbh->prepare($sql)))
	{
		$err = $dbh->errorInfo();
		Conn_error('db_query', 'SQL error: ' . $err[2]);
	}

	if (!($sth->execute()))
	{
		$err = $sth->errorInfo();
		Conn_error('db_query', 'SQL error: ' . $err[2]);
	}

	return $sth;
}

function DB_Result($sth, $num = 0) { if (DB_NumRows($sth) > $num){return $sth->fetchColumn($num);} return false; }

function DB_Identity() { global $dbh; return $dbh->lastInsertId(); }

function DB_NumRows($sth) { return $sth->rowCount(); }

function DB_Fetch($sth) { return $sth->fetch(PDO::FETCH_ASSOC); }


/**
 * Funkcja obsługująca żądania i wysyłająca odpowiedź.
 * Zalecane jest pozostawienie funkcji w tej postaci niezależnie od platformy
 * @global array $options : tablica z ustawieniami ogólnymi
 */
function Conn_Init()
{
	global $options;

	//sprawdzanie poprawności hasła wymiany danych
	if(!isset($_POST['bl_pass']))
	{Conn_Error("no_password","Odwołanie do pliku bez podania hasła. Jest to poprawny komunikat jeśli plik integracyjny został otworzony w przeglądarce internetowej.");}
	elseif($options['baselinker_pass'] == "" || $options['baselinker_pass'] !== $_POST['bl_pass'])
	{Conn_Error("incorrect_password");}
	
	//zmiana kodowania danych wejściowych
	if($options['charset'] != "UTF-8")
	{
		foreach($_POST as $key => $val)
		{$_POST[$key] = iconv('UTF-8', $options['charset'].'//IGNORE', $val);}
	}

	//łączenie z bazą danych sklepu
	Shop_ConnectDatabase($_POST);
	
	//rozbijanie tablic z danymi
	if(isset($_POST['orders_ids'])){$_POST['orders_ids'] = explode(",", $_POST['orders_ids']);}
	if(isset($_POST['products_id'])){$_POST['products_id'] = explode(",", $_POST['products_id']);}
	if(isset($_POST['fields'])){$_POST['fields'] = explode(",", $_POST['fields']);}
	if(isset($_POST['products'])){$_POST['products'] = json_decode($_POST['products'], true);}

	//sprawdzanie czy podana metoda jest zaimplementowana
	if(function_exists("Shop_".$_POST['action']))
	{
		$method = "Shop_".$_POST['action'];
		Conn_SendResponse($method($_POST));
	}
	else
	{Conn_Error("unsupported_action", "No action: ".$_POST['action']);}
}


/**
 * Funkcja generująca odpowiedź do systemu w formacie JSON
 * @global array $options tablica z ustawieniami ogólnymi
 */
function Conn_SendResponse($response)
{
	global $options;

	//zmiana kodowania danych wyjściowych
	if($options['charset'] != "UTF-8" && count($response) > 0)
	{
		foreach($response as $key => $val)
		{$response[$key] = recursive_iconv($options['charset'], 'UTF-8//IGNORE', $val);}
	}

	print json_encode($response);
	exit();
}


/**
 * Funkcja wypisująca kominukat błędu w formacie JSON i kończąca skrypt
 * Zalecane jest pozostawienie funkcji w tej postaci niezależnie od platformy
 * @param string $error_code kod błędu (standardowe wartości: db_connect, db_query, no_action)
 * @param string $error_text opis błędu
 */
function Conn_Error($error_code, $error_text = '')
{
	print json_encode(array('error' => true, 'error_code' => $error_code, 'error_text' => $error_text));
	exit();
}


 /**
 * Ewentualne wczytanie dodatkowych funkcji z pliku baselinker_pm.php (BaseLinker Product Managment) 
 * Zawarte w dodatkowym pliku funkcje rozszerzają możliwości integracji ze sklepem o funkcje pozwalające dodawać i zmieniać kategorie, produkty oraz warianty.
 * Obsługa tych funkcji jest wymagana przez niektóre moduły BaseLinkera (np. moduły integrujące system z programami typu ERP)
 * Plik baselinker_pm.php jest dostępny dla wybranych platform sklepów. Skontaktuj się z administratorem w cely uzyskania pliku.
 */ 
if(file_exists("baselinker_pm.php"))
{include("baselinker_pm.php");}


//inicjacja komunikacji
Conn_Init(); 




/** -----------------------------------------------------------------------------------------------------------
 * Funkcje obsługiwania żądań (przedrostek Shop_)
 *	- Zależne od platformy sklepu
 *	- Do edycji dla deweloperów
 */


 
 /**
 * Funkcja zwracająca wersję pliku wymiany danych
 * Przy tworzeniu pliku należy skonsultować numer wersji i nazwę platformy
 * z administracją systemu Baselinker
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica z danymi platformy z polami:
 * 		platform => nazwa platformy
 * 		version => numer wersji pliku
 */
function Shop_FileVersion($request)
{
	$response['platform'] = "OpenCart";
	$response['version'] = "4.2.27"; //wersja pliku integracyjnego, nie wersja sklepu!
	$response['standard'] = 4; //standard struktury pliku integracyjnego - obecny standard to 4.
	
	return $response;
}


/**
 * Funkcja zwracająca listę zaimplementowanych metod pliku
 * Zalecane jest pozostawienie funkcji w tej postaci niezależnie od platformy
 */
function Shop_SupportedMethods()
{
	$result = array();
	$methods = get_defined_functions();

	foreach($methods['user'] as $m)
	{
		if (stripos($m, 'shop_') === 0)
		{$result[] = substr($m,5);}
	}

	return $result;
}



 /**
 * Funkcja nawiązująca komunikację z bazą danych sklepu
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return boolean wartość logiczna określajaca sukces połączenia z bazą danych
 */
function Shop_ConnectDatabase($request)
{
	global $options; // globalna tablica z ustawieniami
	global $dbh;	// handler bazy danych
	
	$dbp = $options['db_prefix']; // Data Base Prefix - prefix tabel bazy
	
	// wydzielenie portu z nazwy hosta
	if (preg_match('/^\s*([\w\-\.]+):(\d+)\s*$/', $options['db_host'], $m))
	{
		$options['db_host'] = $m[1];
		$options['db_port'] = $m[2];
	}
	
	// wygenerowanie DSN
	$dsn = "mysql:dbname=${options['db_name']};host=${options['db_host']}";

	if (isset($options['db_port']))
	{
		$dsn .= ";port=${options['db_port']}";
	}

	// nawiązanie połączenia z bazą danych sklepu
	try {
		$dbh = new PDO($dsn, $options['db_user'], $options['db_pass']);
	} catch (Exception $ex) {
		Conn_Error('db_connection', $ex->getMessage());
	}

	if($options['charset'] == "UTF-8")
	{DB_Query("SET NAMES utf8");}

	//ustawienie mniej restrykcyjnego trybu MySQL (ważne przy insertach nie uwzględniających wszystkich pól tabeli)
	DB_Query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
	
	//automatyczne wyszukiwanie prefiksu bazy danych
	if($dbp == "")
	{	
		$unique_table = "product_to_category"; //wyszukiwanie tabeli z unikalną nazwą
		$search_table = DB_Query("SHOW TABLES LIKE '%${unique_table}'");
		
		if (DB_NumRows($search_table) == 1)
		{
			$options['db_prefix'] = str_replace($unique_table, '', DB_Result($search_table));
		}
		else //nie wykryto jednoznacznie prefiksulse
		{
			Conn_Error("database_prefix");
		}

		$dbp = $options['db_prefix'];
	}

	$options['lang_id'] = 1;

	//wybieranie języka polskiego z tabeli języków - zmianna $lang_id wykorzystywana później w zapytaniach
	$res = DB_Query("SELECT * FROM `${dbp}language` ORDER BY (`code` LIKE '{0}' OR `name` LIKE '{0}') DESC, language_id LIMIT 1", empty($options['lang']) ? 'pl' : $options['lang']);

	if ($lang = DB_Fetch($res))
	{
		$options['lang_id'] = $lang['language_id'];
		$options['lang_dir'] = isset($lang['directory']) ? $lang['directory'] : $lang['code'];
	}
	
	$options['geo_zone_id'] = false;
	$options['geo_zone'] = empty($options['geo_zone']) ? 'pol' : $options['geo_zone'];
	//sprawdzamy listę rejonów - jeśli więcej niz 1, wybieramy ID dla Polski
	if (DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}geo_zone'")))
	{
		$res = DB_Query("SELECT geo_zone_id
				 FROM `${dbp}geo_zone`
				 WHERE (`name` like '%{0}%' OR `description` LIKE '%{0}%')
				 AND geo_zone_id IN (SELECT DISTINCT geo_zone_id FROM `${dbp}tax_rate`)
				 ORDER BY (`name` LIKE '{0}' OR `description` LIKE '{0}') DESC, date_added DESC");

		if (DB_NumRows($res))
		{
			$options['geo_zone_id'] = DB_Result($res);
		}
	}

	//sprawdzanie istnienia dodatkowych pól
	$options['payment_tax_id'] = DB_NumRows(DB_Query("SHOW COLUMNS FROM `${dbp}order` LIKE 'payment_tax_id'"));

	//czy ceny zawierają podatek?
	$res = DB_Query("SELECT value FROM `${dbp}setting` WHERE `key` = 'config_tax'");
	$options['add_tax'] = DB_Result($res);

	//istnieje tabela tax_rule?
	$res = DB_Query("SHOW TABLES LIKE '${dbp}tax_rule'");
	$options['use_tax_rules'] = DB_NumRows($res);

	//pola sortujące
	$res = DB_Query("SHOW COLUMNS FROM `${dbp}product` LIKE 'sort_order'");
	$options['use_sort_order'] = DB_NumRows($res);

	//konwersja waluty
	if ($options['currency'])
	{
		$sql = "SELECT value FROM `${dbp}currency`
			WHERE code LIKE '{0}' AND status = 1";
		$options['currency_mult'] = DB_Result(DB_Query($sql, $options['currency']));
	}

	if (!$options['currency_mult'])
	{
		$options['currency_mult'] = 1;
	}

	//wybór sklepu
	if ($options['store_id'] === '')
	{
		$sql = "SELECT store_id FROM `${dbp}store`
			ORDER BY (store_id = '{0}') DESC LIMIT 1";

		if ($store_id = DB_Result(DB_Query($sql, $options['store_id'])))
		{
			$options['store_id'] = $store_id;
		}
		else
		{
			$options['store_id'] = 0;
		}
	}

	// v4
	$options['v4'] = DB_NumRows(DB_Query("SHOW FIELDS FROM `${dbp}order` LIKE 'payment_code'")) ? 0 : 1;
}





 /**
 * Funkcja zwraca listę kategorii sklepowych
 * Zwracana tabela powinna być posortowana alfabetycznie
 * W nazwie kategorii podrzędnej powinna być zawrta nazwa nadkategorii - np "Komputery/Karty graficzne" zamiast "Karty graficzne"
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica z listą kategori sklepowch w formacie:
 * 		id kategorii => nazwa kategorii
 */
function Shop_ProductsCategories($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	$categories = array();
	$parents = array();
	
	//pobieranie kategorii z bazy i zapisywanie do tabeli
	$sql = "SELECT c.category_id, c.parent_id, cd.name
		FROM `${dbp}category_to_store` cs
		LEFT JOIN `${dbp}category` c ON c.category_id = cs.category_id
		JOIN `${dbp}category_description` cd ON c.category_id = cd.category_id AND language_id = '{1}'
		WHERE c.status = 1 AND (cs.store_id = '{0}' or cs.store_id = 0)";
	$res = DB_Query($sql, $options['store_id'], $options['lang_id']);

	while ($category = DB_Fetch($res))
	{
		$categories[$category['category_id']] = $category['name'];
		$parents[$category['category_id']] = $category['parent_id'];
	}
	
	
	//budowanie drzewa kategorii na podstawie tablicy
	$category_tree = array();

	foreach($categories as $id => $name)
	{
		$cat_name = "";
		$this_id = $id;

		while ($parents[$this_id] != 0 and $this_id != $parents[$this_id])
		{
			$cat_name = $categories[$parents[$this_id]]."/".$cat_name;
			$this_id = $parents[$this_id];
		}

		$category_tree[$id] = $cat_name.$name;
	}

	//sortowanie alfabetycznie wg nazw
	asort($category_tree);
	
	return $category_tree;
}





 /**
 * Funkcja zwraca listę produktów z bazy sklepu
 * Zwracane liczby (np ceny) powinny mieć format typu: 123456798.12 (kropka oddziela część całkowitą, 2 miejsca po przecinku)
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu zawierająca pola:
 *		category_id => 			id kategori (wartość 'all' jeśli wszystkie przedmioty)
 *		filter_limit => 		limit zwróconych kategorii w formacie SQLowym ("ilość pomijanych, ilość pobieranych")
 *		filter_sort => 			wartość po której ma być sortowana lista produktów. Możliwe wartości:
 *								"id [ASC|DESC]", "name [ASC|DESC]", "quantity [ASC|DESC]", "price [ASC|DESC]"
 *		filter_id => 			ograniczenie wyników do konkretnego id produktu
 *		filter_ean => 			ograniczenie wyników do konkretnego ean
 *		filter_sku => 			ograniczenie wyników do konkretnego sku (numeru magazynowego)
 *		filter_name => 			filtr nazw przedmiotów (fragment szukanej nazwy lub puste pole)
 *		filter_price_from =>	dolne ograniczenie ceny (nie wyświetlane produkty z niższą ceną) 
 *		filter_price_to =>		górne ograniczenie ceny
 *		filter_quantity_from =>	dolne ograniczenie ilości produktów
 *		filter_quantity_to =>	górne ograniczenie ilości produktów
 *		filter_available =>		wyświetlanie tylko produktów oznaczonych jako dostępne (wartość 1) lub niedostępne (0) lub wszystkich (pusta wartość)
 * @return array $response tablica z listą produktów w formacie:
 * 		id produktu => 
						'name' => nazwa produktu
						'quantity' => dostępna ilość
						'price' => cena w PLN
 */
function Shop_ProductsList($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	//tabela stawek podatkowych
	$tax_rate = array();

	if ($options['use_tax_rules'])
	{
		$sql = "SELECT rate, tax_rule.tax_class_id
			FROM `${dbp}tax_rule` tax_rule
			JOIN `${dbp}tax_rate` tax_rate ON tax_rule.tax_rate_id = tax_rate.tax_rate_id AND tax_rate.type = 'P' "
			.($options['geo_zone_id'] ? (" AND geo_zone_id = " . $options['geo_zone_id']) : '')
			." ORDER BY tax_rule.tax_class_id, tax_rule.priority DESC";
	}
	else
	{
		$sql = "SELECT `${dbp}tax_class`.tax_class_id, rate
			FROM `${dbp}tax_class` LEFT JOIN `${dbp}tax_rate` ON `{$dbp}tax_class`.tax_class_id = `${dbp}tax_rate`.tax_class_id
			WHERE 1 " . ($options['geo_zone_id'] ? (" AND geo_zone_id = " . $options['geo_zone_id']) : '')
			. " ORDER BY priority DESC";
	}

	$res = DB_Query($sql);

	while ($tax = DB_Fetch($res))
	{
		$tax_rate[$tax['tax_class_id']] = $tax['rate'];
	}

	//wyrażenia zwracające ilość magazynową produktu i jego cenę
	$qty_spec = "if(isnull(pov.product_id) OR p.quantity <> 0, p.quantity, sum(pov.quantity))";
	$qty_spec = 'p.quantity';
	
	if ($options['active_stock_only'])
	{
		$qty_spec .= '*p.status';
	}

	$price_spec = "if(isnull(s.price) OR s.price = 0 OR '${options['special_price']}' <> '1' OR p.status <> 1 OR s.date_start > '" . date('Y-m-d') . "' OR (s.date_end < '" . date('Y-m-d') . "' AND s.date_end <> '0000-00-00'), p.price, s.price)*${options['currency_mult']}";

	//zmiana nazw kolumn na nazwy pól
	$request['filter_sort'] = str_replace(array('id', 'name', 'quantity', 'price'), array('p.product_id', 'pd.name', $qty_spec, $price_spec), isset($request['filter_sort']) ? $request['filter_sort'] : '');
	
	//pobieranie produktow z bazy danych
	$sql = "SELECT p.product_id id_product, $qty_spec quantity, tax_class_id,
		pd.name name, $price_spec price, p.status, p.${options['sku_fld']}, p.ean
		FROM `${dbp}product` p
		LEFT JOIN `${dbp}product_to_category` ptc ON p.product_id = ptc.product_id
		JOIN `${dbp}product_to_store` pts ON (pts.store_id = '{0}' or pts.store_id = 0) AND pts.product_id = p.product_id
		LEFT JOIN `${dbp}product_description` pd ON pd.product_id = p.product_id AND pd.language_id = ${options['lang_id']}
		/* LEFT JOIN `${dbp}product_option_value` pov ON p.product_id = pov.product_id */
		LEFT JOIN `${dbp}product_special` s ON s.product_id = p.product_id AND s.customer_group_id = '${options['customer_group']}'
		AND s.date_start <= '" . date('Y-m-d') . "' AND (s.date_end > '" . date('Y-m-d') . "' OR s.date_end = '0000-00-00')
		WHERE 1";

	if(isset($request['category_id']) and $request['category_id'] != 'all' and $request['category_id'] != '') {$sql .= " AND ptc.category_id = '${request['category_id']}'";} //wybór kategorii
	if(isset($request['filter_ean']) and $request['filter_ean'] != '') {$sql .= " AND p.ean = '${request['filter_ean']}'";} //filtrowanie ean
	if(isset($request['filter_sku']) and $request['filter_sku'] != '') {$sql .= " AND p.{$options['sku_fld']} = '${request['filter_sku']}'";} //filtrowanie sku
	if(isset($request['filter_id']) and $request['filter_id'] != '') {$sql .= " AND p.product_id = '${request['filter_id']}'";} //filtrowanie id
	if(isset($request['filter_name']) and $request['filter_name'] != '') {$sql .= " AND pd.name LIKE '%${request['filter_name']}%'";} //filtrowanie nazwy
	if(isset($request['filter_available']) and $request['filter_available'] != '') {$sql .= " AND p.status = '${request['filter_available']}'";} //produkty dostępne/niedostępne

	$sql .= " GROUP BY p.product_id";
	$having = array(); // dodatkowe kryteria filtrowania

	if(isset($request['filter_quantity_from']) and $request['filter_quantity_from'] != '') {$having[] = "quantity>= '${request['filter_quantity_from']}'";} //filtrowanie ilości
	if(isset($request['filter_quantity_to']) and $request['filter_quantity_to'] != '') {$having[] = "quantity <= '${request['filter_quantity_to']}'";} //filtrowanie ilości

	if (count($having))
	{
		$sql .= ' HAVING ' . implode(' AND ', $having);
	}

	if(isset($request['filter_sort']) and $request['filter_sort'] != '') {$sql .= " ORDER BY ${request['filter_sort']}";}
	if(isset($request['filter_limit']) and $request['filter_limit'] != '') {$sql .= " LIMIT ${request['filter_limit']}";}

	// stronicowanie wyników
	$count_sql = preg_replace('/^(SELECT\s).+?(\sFROM\s)/is', 'SELECT COUNT(*) FROM ($1p.product_id$2', $sql) . ') pids';

	if ($request['filter_limit'] or count($having))
	{
		$count = 1;
	}
	else
	{
		$count = DB_Result(DB_Query($count_sql, $options['store_id']));
	}

	$page = (isset($request['page']) and (int)$request['page']) ? (int)$request['page'] : 1;
	$pages = ceil($count/10000);

	if (empty($request['filter_limit']))
	{
		$sql .= ' LIMIT ' . (($page-1)*10000) . ', 10000';
	}

	$response = array();
	$result = DB_Query($sql, $options['store_id']);

	while($prod = DB_Fetch($result))
	{
		//wyliczanie ceny brutto
		if ($options['add_tax'])
		{
			$tax = number_format(($prod['tax_class_id'] and $tax_rate[$prod['tax_class_id']]) ? $tax_rate[$prod['tax_class_id']] : 0, 2);
			$prod['price'] = number_format($prod['price']*(1+$tax/100), 2, '.', '');
		}
		else
		{
			$prod['price'] = number_format($prod['price'], 2, '.', '');
		}
			
		//filtrowanie ceny
		if(isset($request['filter_price_from']) and $request['filter_price_from'] != '' and $prod['price'] < $request['filter_price_from']) {continue;} //dolne ograniczenie ceny
		if(isset($request['filter_price_to']) and $request['filter_price_to'] != '' and $prod['price'] > $request['filter_price_to']) {continue;} //górne ograniczenie ceny
			
		$response[$prod['id_product']] = array('ean' => $prod['ean'], 'sku' => $prod[$options['sku_fld']], 'name' => $prod['name'], 'quantity' => $prod['quantity'], 'price' => $prod['price']);
	}

	if ($pages > 1)
	{
		$response['pages'] = $pages;
	}
	
	return $response;
}





 /**
 * Funkcja zwraca szczegółowe dane wybranych produktów
 * Zwracane liczby (np ceny) powinny mieć format typu: 123456798.12 (kropka oddziela część całkowitą, 2 miejsca po przecinku)
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu zawierająca pola:
 *		products_id => 			tablica z numerami id produktów
 *		fields => 				tablica z nazwami pól do zwrócenia (jeśli pusta zwracany jest cały wynik)
 * @return array $response tablica z listą produktów w formacie:
 * 		id produktu => 
						'name' => nazwa produktu, 'ean' => Kod EAN, 'sku' => numer katalogowy, 'model' => nazwa modelu lub inny identyfikator np ISBN, 
						'description' => opis produktu (może zawierać tagi HTML), 'description_extra1' => drugi opis produktu (np opis krótki) 'weight' => waga produktu w kg,
						'quantity' => dostępna ilość, 'man_name' => nazwa producenta, 'man_image' => pełny adres obrazka loga producenta,
						'category_id' => numer ID głównej kategorii, 'category_name' => nazwa kategori do której należy przedmiot, 'tax' => wielkość podatku w formie liczby (np 23)
						'price' => cena brutto w PLN,
						'images' => tablica z pełnymi adresami dodatkowych obrazków (pierwsze zdjęcie główne, reszta w odpowiedniej kolejności),
						'features' => tablica z opisem cech produktu. Poszczególny element tablicy zawiera nazwę i wartość cechy, np array('Rozdzielczość','Full HD')
						'variants' => tablica z wariantami produktu do wyboru (np kolor, rozmiar). Format pola opisany jest w kodzie poniżej
 */
function Shop_ProductsData($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy


	//pobieranie danych produktów z bazy danych
	$sql = "SELECT DISTINCT p.product_id id, pd.name, pd.description description,
		tax_rate.rate tax, m.name man_name, m.image man_image, p.*,
		if(NOT isnull(s.price) AND {2}, s.price, p.price)*${options['currency_mult']} price
		FROM `${dbp}product` p 
		LEFT JOIN `${dbp}product_description` pd ON pd.product_id = p.product_id AND pd.language_id = {1}
		LEFT JOIN `${dbp}product_special` s ON s.product_id = p.product_id AND s.customer_group_id = '${options['customer_group']}' AND s.date_start <= '" . date('Y-m-d') . "' AND (s.date_end >= '" . date('Y-m-d') . "' OR s.date_end = '0000-00-00')";

	if ($options['use_tax_rules'])
	{
		$sql .= " LEFT JOIN `${dbp}tax_rate` tax_rate ON tax_rate.type = 'P'
		AND tax_rate_id = (SELECT tax_rate_id FROM `${dbp}tax_rule` WHERE tax_class_id = p.tax_class_id ORDER BY PRIORITY LIMIT 1)";
	}
	else
	{
		$sql .= " LEFT JOIN `${dbp}tax_rate` ON p.tax_class_id = tax_rate.tax_class_id";
	}

	$sql .= $options['geo_zone_id'] ? " AND tax_rate.geo_zone_id = ${options['geo_zone_id']}" : '';

	//obsługa dwóch metod zapisania producenta w bazie (różnice między wersjami OpenCart).
	//W nowszej wersji dodatkowa tabela product_to_manufacturer
	if (DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}product_to_manufacturer'")))
	{
		$sql .= " LEFT JOIN `${dbp}product_to_manufacturer` ptm ON p.product_id = ptm.product_id
			LEFT JOIN `${dbp}manufacturer` m ON m.manufacturer_id = ptm.manufacturer_id";
	}
	else
	{
		$sql .= " LEFT JOIN `${dbp}manufacturer` m ON m.manufacturer_id = p.manufacturer_id";
	}

	$sql .= " WHERE p.product_id IN ({0})";

	if ($options['use_sort_order'])
	{
		$sql .= ' ORDER BY p.sort_order, p.product_id';
	}
			
	$result = DB_Query($sql, implode(', ', $request['products_id']), $options['lang_id'], $options['special_price']);

	while ($product = DB_Fetch($result))
	{
		$this_prod_id = $product['id'];
	
		$prod = array(
			'name' => $product['name'],
			'description' => html_entity_decode($product['description']),
			'sku' => $product[$options['sku_fld']],
			'ean' => $product['ean'],
			'model' => $product['model'],
			'man_image' => $product['man_image'],
			'man_name' => $product['man_name'],
			'tax' => $product['tax'],
			'price' => $product['price'],
			'quantity' => $product['quantity'],
			'weight' => _weight_conv($product['weight'], $product['weight_class_id']),
		);

		$sql = "SELECT cd.name, ptc.category_id
			FROM `${dbp}product_to_category` ptc 
			JOIN `${dbp}category` c ON c.category_id = ptc.category_id AND c.status = 1
			LEFT JOIN `${dbp}category_description` cd ON cd.category_id = c.category_id AND cd.language_id = '{1}'
			WHERE ptc.product_id = '{0}'
			ORDER BY c.sort_order LIMIT 1";

		if ($cat = DB_Fetch(DB_Query($sql, $this_prod_id, $options['lang_id'])))
		{
			$prod['category_id'] = $cat['category_id'];
			$prod['category_name'] = $cat['name'];
		}

		if ($options['active_stock_only'])
		{
			$prod['quantity'] *= $product['status'];
		}

		// formatowanie URL loga producenta
		if ($prod['man_image'])
		{
			$prod['man_image'] = (preg_match('/^\w+:\/\//i', $prod['man_image']) ? '' : $options['images_folder']) . imgurlencode($prod['man_image']);
		}

		// obrazek główny jako pierwszy
		$prod['images'] = array((preg_match('/^\w+:\/\//i', $product['image']) ? '' : $options['images_folder']).imgurlencode($product['image']));

		// obrazki dodatkowe
		$sql = "SELECT image FROM `${dbp}product_image` WHERE product_id = '{0}'" . ($options['use_sort_order'] ? " ORDER BY sort_order" : '');
		$res = DB_Query($sql, $this_prod_id);

		while ($image = DB_Fetch($res))
		{
			if ($image['image'] == $product['image'])
			{
				continue;  // nie duplikuj obrazka głównego
			}

			$prod['images'][] = (preg_match('/^\w+:\/\//i', $image['image']) ? '' : $options['images_folder']).imgurlencode($image['image']);
		}

		// wyliczanie ceny brutto
		if ($options['add_tax'])
		{
			$net_price = $prod['price'];
			$prod['price'] = number_format($prod['price']*(100+$prod['tax'])/100, 2, '.', '');
		}
		else
		{
			$prod['price'] = number_format($prod['price'], 2, '.', '');
		}

		//pobieranie cech produktu (np. długość, kolor, rozmiar itp.)
		$prod['features'] = array();

		$sql = "SELECT name, text FROM `${dbp}product_attribute` pa
			JOIN `${dbp}attribute` a ON pa.attribute_id = a.attribute_id
			JOIN `${dbp}attribute_description` ad ON ad.attribute_id = pa.attribute_id AND ad.language_id = pa.language_id
			WHERE pa.product_id = {0} AND pa.language_id = {1}/* ORDER BY pa.sort_order*/";
		$res = DB_Query($sql, $this_prod_id, $options['lang_id']);

		while ($feature = DB_Fetch($res))
		{
			$prod['features'][] = array($feature['name'], $feature['text']);
		}

		//pobieranie wariantów produktów - można pominąć jeśli platforma nie obsługuje wariantów do wyboru (np. kolor, rozmiar itp)
		//format tablicy wariantów:
		//		id_wariantu => array('full_name' => pełna nazwa produktu z wariantem, 'name' => nazwa wariantu bez nazwy produktu, 'price' => cena, 'quantity' => stan magazynowy)
		$prod['variants'] = array();

		$sql = "SELECT concat(pov.price_prefix, pov.price)*${options['currency_mult']} price_diff, od.name, ovd.name value, pov.quantity,
			pov.product_option_value_id variant_id" . (empty($options['vean_fld']) ? '' : ", pov.${options['vean_fld']} ean") . "
			FROM `${dbp}product_option_value` pov
			JOIN `${dbp}option_description` od ON od.option_id = pov.option_id AND od.language_id = {1}
			JOIN `${dbp}option_value_description` ovd ON ovd.option_value_id = pov.option_value_id AND ovd.language_id = {1}
			WHERE pov.product_id = {0}";
		$res = DB_Query($sql, $this_prod_id, $options['lang_id']);

		while ($opt = DB_Fetch($res))
		{
			$prod['variants'][$opt['variant_id']] = array('full_name' => "${prod['name']} ${opt['value']}", 'name' => $opt['value'], 'price' => $options['add_tax'] ? number_format(($net_price+$opt['price_diff'])*(100+$prod['tax'])/100, 2, '.', '') : ($prod['price']+$opt['price_diff']), 'quantity' => $opt['quantity']);

			if ($options['active_stock_only'])
			{
				$prod['variants'][$opt['variant_id']]['quantity'] *= $product['status'];
			}

			if (!empty($opt['ean']))
			{
				$prod['variants'][$opt['variant_id']]['ean'] = $opt['ean'];
			}
		}
		
		//jeśli podatek nie sprecyzowany, przyjmij domyślną stawkę
		$prod['tax'] = (isset($prod['tax']) and is_numeric($prod['tax'])) ? $prod['tax'] : $options['def_tax'];

		//pobieranie wartości dodatkowych tagów z zewnętrznego skryptu
		//wszystkie pola tablicy $p mogą zostać użyte w szablonie aukcji: Nowe pole np. $p['test'] będzie dostępne w szablonie aukcji jako tag [test]
		//poniższy warunek może pozostać identyczny niezależnie od platformy sklepu
		if(file_exists("baselinker_extra.php"))
		{
			//pobranie dodatkowych informacji z zewnętrznego pliku pliku.
			//Plik tworzony jest indywidualnie dla każdego sprzedawcy jeśli zgłosi potrzebę pobierania dodatkowych danych ze sklepu.
			//Pozwala to uniknąć ingerowania w standardowy plik baselinker.php
			include("baselinker_extra.php");
		}
		
		
		//wyrzucanie niepotrzebnych wartości jeśli określono pola do pobrania
		//poniższy kod może pozostać identyczny niezależnie od platformy sklepu
		if(isset($request['fields']) and !(count($request['fields']) == 1 && $request['fields'][0] == "") && !count($request['fields']) == 0)
		{
			$temp_p = array();
			foreach($request['fields'] as $field)
			{$temp_p[$field] = $p[$field];}
			$p = $temp_p;
		}
		
		$response[$this_prod_id] = $prod;
	}
	
	return $response;
}





 /**
 * Funkcja zwraca stan magazynowy wszystkich produktów i ich wariantów
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica ze stanem magazynowym wszystkich produktów, w formacie:
 * 		id produktu => ID produktu jest kluczem tablicy, wartością jest tablica składająca się ze stanów wariantów
 *                             id wariantu => kluczem tablicy jest ID wariantu (0 w przypadku produktu głównego)
 *                             stan => wartościa jest stan magazynowy
 *          Przykład: array('432' => array('0' => 4, '543' => 2, '567' => 3)) - produkt ID 432, stan głównego produktu to 4, posiada dwa warianty (ID 543 i 563) o stanach 2 i 3.
 */
function Shop_ProductsQuantity($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	$page = empty($request['page']) ? 1 : (int)$request['page'];
	$per_page = 500;
	$response = array();

	// subset
	$sql = "SELECT DISTINCT product_id FROM `${dbp}product_to_store` WHERE store_id IN (0, '{0}')
		ORDER BY product_id LIMIT " . ($page-1)*$per_page . ", $per_page";
	$res = DB_Query($sql, $options['store_id']);

	if (!$row = DB_Fetch($res))
	{
		return $response;
	}
	else
	{
		$first = $last = $row['product_id'];
	}

	while ($row = DB_Fetch($res))
	{
		$last = $row['product_id'];
	}

	//pobieranie stanów magazynowych z bazy danych
	$sql = "SELECT p.product_id, p.quantity, pov.product_option_value_id variant_id, pov.quantity variant_quantity,
		p.status
		FROM `${dbp}product` p
		JOIN `${dbp}product_to_store` pts ON (pts.store_id = '{0}' or pts.store_id = 0) AND pts.product_id = p.product_id
		LEFT JOIN `${dbp}product_option_value` pov ON p.product_id = pov.product_id
		WHERE p.product_id >= $first AND p.product_id <= $last";

	$res = DB_Query($sql, $options['store_id']);

	while ($prod = DB_Fetch($res))
	{
		if ($options['active_stock_only'])
		{
			$prod['quantity'] *= $prod['status'];
			$prod['variant_quantity'] *= $prod['status'];
		}

		$response[$prod['product_id']][0] = $prod['quantity'];

		if ($prod['variant_id'])
		{
			$response[$prod['product_id']][$prod['variant_id']] = $prod['variant_quantity'];

			// jeśli stan głównego produktu jest 0, podliczamy łączną liczbę wariantów
			if ($prod['quantity'] === 0)
			{
				$response[$prod['product_id']][0] += $prod['variant_quantity'];
			}
		}
	}

	// pages
	$sql = "SELECT count(*) FROM `${dbp}product_to_store` WHERE store_id IN (0, '{0}')";
	$response['pages'] = ceil((int)DB_Result(DB_Query($sql, $options['store_id']))/$per_page);
	
	return $response;
}


 /**
 * Funkcja zwraca ceny wszystkich produktów i ich wariantów
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica z cenami wszystkich produktów, w formacie:
 * 	id produktu => 	ID produktu jest kluczem tablicy, wartością jest tablica składająca się z cen wariantów
 *	id wariantu => 	kluczem tablicy jest ID wariantu (0 w przypadku produktu głównego)
 * 	cena => 	wartościa jest cena brutto produktu lub wariantu
 *
 * 	Przykład: array('432' => array('0' => 230.00, '543' => 210.00, '567' => 230.00)) - produkt ID 432, cena głównego
 * 	produktu to 230, posiada dwa warianty (ID 543 i 563), pierwszy w cenie niższej o 20.00.
 */
function Shop_ProductsPrices($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	$per_page = 1000;
	$page = (isset($request['page']) and (int)$request['page']) ? (int)$request['page'] : 1;

	$response = array();

	//tabela stawek podatkowych
	$tax_rate = array();

	if ($options['use_tax_rules'])
	{
		$sql = "SELECT rate, tax_rule.tax_class_id
			FROM `${dbp}tax_rule` tax_rule
			JOIN `${dbp}tax_rate` tax_rate ON tax_rule.tax_rate_id = tax_rate.tax_rate_id AND tax_rate.type = 'P' "
			.($options['geo_zone_id'] ? (" AND geo_zone_id = " . $options['geo_zone_id']) : '')
			." ORDER BY tax_rule.tax_class_id, tax_rule.priority DESC";
	}
	else
	{
		$sql = "SELECT `${dbp}tax_class`.tax_class_id, rate
			FROM `${dbp}tax_class` LEFT JOIN `${dbp}tax_rate` ON `{$dbp}tax_class`.tax_class_id = `${dbp}tax_rate`.tax_class_id
			WHERE 1 " . ($options['geo_zone_id'] ? (" AND geo_zone_id = " . $options['geo_zone_id']) : '')
			. " ORDER BY priority DESC";
	}

	$res = DB_Query($sql);

	while ($tax = DB_Fetch($res))
	{
		$tax_rate[$tax['tax_class_id']] = $tax['rate'];
	}

	$price_spec = "if(isnull(s.price) OR s.price = 0 OR '${options['special_price']}' <> '1' OR p.status <> 1 OR s.date_start > '" . date('Y-m-d') . "' OR (s.date_end < '" . date('Y-m-d') . "' AND s.date_end <> '0000-00-00'), p.price, s.price)*${options['currency_mult']}";

	$sql = "SELECT p.product_id, $price_spec price, pov.product_option_value_id variant_id, p.tax_class_id, 
		concat(pov.price_prefix, pov.price)*${options['currency_mult']} price_diff
		FROM `${dbp}product` p
		LEFT JOIN `${dbp}product_option_value` pov ON p.product_id = pov.product_id
		LEFT JOIN `${dbp}product_special` s ON s.product_id = p.product_id AND s.customer_group_id = '${options['customer_group']}'
		";

	if ($options['special_price'])
	{
		$sql .= " AND s.date_start <= '" . date('Y-m-d') . "' AND (s.date_end > '" . date('Y-m-d') . "' OR s.date_end = '0000-00-00')";
	}

	$sql .= " WHERE p.product_id IN ({1})";
	$sql .= " ORDER BY p.product_id";

	// stronicowanie
	$count_sql = "SELECT count(*) FROM `${dbp}product_to_store` pts JOIN `${dbp}product` p ON pts.product_id = p.product_id WHERE pts.store_id = '{0}' or pts.store_id = 0";
	$rows = DB_Result(DB_Query($count_sql, $options['store_id']));
	$pages = ceil($rows/$per_page);
	// uwaga: product_to_store może zawierać dawno usunięte produkty - stąd joiny do tabeli product

	$sub_sql = "SELECT pts.product_id FROM `${dbp}product_to_store` pts JOIN `${dbp}product` p ON pts.product_id = p.product_id WHERE store_id = '{0}' or pts.store_id = 0 ORDER BY pts.product_id LIMIT {1}, {2}";
	$res = DB_Query($sub_sql, $options['store_id'], ($page-1)*$per_page, $per_page);
	$prod_ids = array();

	while ($p = DB_Fetch($res))
	{
		$prod_ids[] = $p['product_id'];
	}

	// pobieranie cen
	$res = DB_Query($sql, $options['store_id'], implode(', ', $prod_ids));

	while ($prod = DB_Fetch($res))
	{
		// wyliczanie ceny brutto dla produktu głównego
		if ($options['add_tax'])
		{
			$tax = number_format(($prod['tax_class_id'] and $tax_rate[$prod['tax_class_id']]) ? $tax_rate[$prod['tax_class_id']] : 0, 2);
			$prod['price'] = number_format($prod['price']*(1+$tax/100), 2, '.', '');
		}
		else
		{
			$prod['price'] = number_format($prod['price'], 2, '.', '');
		}

		$response[$prod['product_id']][0] = $prod['price'];

		// cena wariantu z ceny głównej i różnicy cen
		if ($prod['variant_id'])
		{
			$response[$prod['product_id']][$prod['variant_id']] = $options['add_tax'] ? number_format($prod['price']+$prod['price_diff']*(100+$tax)/100, 2, '.', '') : ($prod['price']+$prod['price_diff']);
		}
	}

	if ($pages > 1)
	{
		$response['pages'] = $pages;
	}
	
	return $response;
}


 /**
 * Funkcja ustawia stan magazynowy wybranych produktów i ich wariantów
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu: 
 *		products => tablica zawierająca informacje o zmianach stanu produktu. Każdy element tablicy jest również tablicą składającą się z pól:
 *					product_id => ID produktu
 *					variant_id => ID wariantu (0 jeśli produkt główny)
 *					operation => rodzaj zmiany, dopuszczalne wartości to: 'set' (ustawia konkretny stan), 'change' (dodaje do stanu magazynowego, ujemna liczba w polu quantity zmniejszy stan o daną ilość sztuk, dodatnia zwiększy)
 *					quantity => zmiana stanu magazynowego (ilośc do ustawienia/zmniejszenia/zwiększenia zależnie od pola operation)
 * @return array $response tablica zawierajaca pole z ilością zmienionych produktów:
 * 		counter => ilość zmienionych produktów
 */
function Shop_ProductsQuantityUpdate($request)
{	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	foreach ($request['products'] as $prod)
	{
		// zmiana stanu wariantu
		if ($prod['variant_id'] = (int)$prod['variant_id'])
		{
			$sql = "UPDATE `${dbp}product_option_value` SET quantity = {0}
				WHERE product_option_value_id = {1}";
		}
		else // zmiana stanu produktu głównego
		{
			$sql = "UPDATE `${dbp}product` SET quantity = {0} WHERE product_id = {1}";
		}
		
		DB_Query($sql, ($prod['operation'] == 'change') ? "quantity + ${prod['quantity']}" : $prod['quantity'],
				$prod['variant_id'] ? $prod['variant_id'] : $prod['product_id']);
	}
	
	return array('counter' => count($request['products']));
}


 /**
 * Funkcja tworzy zamówienie w sklepie na podstawie nadesłanych danych
 * Jeśli funkcja otrzyuje na wejściu ID zamówienia, aktualizuje dane zamówienie zamiast tworzyć nowe
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, zawiera informacje o zamówieniu w formacie:
 *		previous_order_id => ID zamówienia (jeśli pierwszy raz dodawane do sklepu, wartość jest pusta. Peśli było już wcześniej dodane, wartość zawiera poprzedni numer zamówienia)
 *		delivery_fullname, delivery_company, delivery_address, delivery_city, delivery_postcode, delivery_country => dane dotyczące adresu wysyłki
 *		invoice_fullname, invoice_company, invoice_address, invoice_city, invoice_postcode, invoice_country, invoice_nip => dane dotyczące adresu płatnika faktury
 *		phone => nr telefonu, email => adres email, 
 *		delivery_method => nazwa sposóbu wysyłki, delivery_method_id => numer ID sposobu wysyłki, delivery_price => cena wysyłki
 *		user_comments => komentarz kupującego, currency => waluta zamówienia, status_id => status nowego zamówienia
 *              change_products_quantity => flaga (bool) informująca, czy po stworzeniu zamówienia zmniejszony ma zostać stan zakupionych produktów 
 *		products => tablica z zakupionymi produktami w formacie:
 *				[] => 
 *						id => ID produktu
 *                                              variant_id => ID wariantu
 *						name => nazwa produktu (używana jeśli nie można pobrać jej z bazy na podstawie id)
 *						price => cena brutto w PLN
 *						currency => waluta
 *						quantity => zakupiona ilość
 *						attributes => tablica z atrybutami produktu w formacie:
 *									[] =>
 *											name =>	nazwa atrybutu (np. "kolor")
 *											value => wartość atybutu (np. "czerwony")
 *											price => różnica ceny dla tego produktu (np. "-10.00")
 *													 zmiana ceny jest już uwzględniona w cenie produktu
 * @return array $response tablica zawierająca numer nowego zamówienia:
 * 		'order_id' => numer utworzonego zamówienia
 */
function Shop_OrderAdd($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	//jeśli zamówienie jest ponownie dodawane do bazy sklepu, wcześniejsze dane są usuwane
	//przy ponownym dodawaniu zamówienia (aktualizowaniu), $request['previous_order_id'] zawiera poprzedni numer danego zamówienia w sklepie
	if (!empty($request['previous_order_id']))
	{
		$table_to_clear = array("order", "order_history", "order_product", "order_option", "order_total");
		foreach($table_to_clear as $tbl)
		{DB_Query("DELETE FROM `${dbp}${tbl}` WHERE `order_id` = '${request['previous_order_id']}' ");}
	}

	//nazwa sklepu
	$config_name = DB_Result(DB_Query("SELECT `value` FROM `${dbp}setting` WHERE `key` = 'config_name'"));
	
	//rozdzielenie imion od nazwisk
	$delivery_fullname_exp = explode(' ', $request['delivery_fullname']);
	$delivery_lastname = array_pop($delivery_fullname_exp);
	$delivery_firstname = implode(' ', $delivery_fullname_exp);

	$invoice_fullname_exp = explode(' ', $request['invoice_fullname']);
	$invoice_lastname = array_pop($invoice_fullname_exp);
	$invoice_firstname = implode(' ', $invoice_fullname_exp);

	//identyfikatory krajów
	$sql = "SELECT country_id FROM `${dbp}country` WHERE name LIKE '{0}' OR iso_code_2 LIKE '{0}' AND status = 1";
	$res = DB_Query($sql, $request['invoice_country_code'] ? $request['invoice_country_code'] : ($request['invoice_country'] ? $request['invoice_country'] : 'Polska'));
	$payment_country_id = (int)DB_Result($res);

	$res = DB_Query($sql, $request['delivery_country_code'] ? $request['delivery_country_code'] : ($request['delivery_country'] ? $request['delivery_country'] : 'Polska'));
	$shipping_country_id = (int)DB_Result($res);

	//identyfikator waluty
	$request['currency'] = $request['currency'] ? $request['currency'] : 'PLN';
	$sql = "SELECT currency_id FROM `${dbp}currency` WHERE code = '{0}' AND status = 1";
	$res = DB_Query($sql, $request['currency']);
	$currency_id = (int)DB_Result($res);

	//format adresów
	$addr_fmt = "{firstname} {lastname}\n{company}\n{address_1}\n{address_2}\n{city} {postcode}\n{zone}\n{country}";

	//custom fields available?
	$custom_fields = DB_NumRows(DB_Query("SHOW FIELDS FROM `${dbp}order` LIKE 'custom_field'"));
	$cfb = $custom_fields ? '' : '/*';
	$cfe = $custom_fields ? '' : '*/';
	$v3b = $options['v4'] ? '/*' : '';
	$v3e = $options['v4'] ? '*/' : '';

	$store_name = (string)DB_Result(DB_Query("SELECT url FROM `${dbp}store` WHERE store_id = '{0}'", $options["store_id"]));
	$store_name = empty($store_name) ? $config_name : $store_name;

	//dodanie zamowienia do tabeli orders
	$sql = "INSERT INTO `${dbp}order` 
		(order_id, customer_id, customer_group_id, firstname, lastname, ". ($options['payment_tax_id'] ? 'payment_tax_id,' : '') . "
		telephone, email, 
		shipping_firstname, shipping_lastname, shipping_company, shipping_address_1, shipping_address_2, shipping_city,
		shipping_postcode, shipping_country, shipping_address_format, 
		payment_firstname, payment_lastname, payment_company, payment_address_1, payment_address_2, payment_city, 
		payment_postcode, payment_country, payment_address_format,

		payment_method, $v3b payment_code, $v3e date_modified, date_added, order_status_id, currency_code, currency_value, $v3b shipping_code, $v3e

		$cfb custom_field, $cfe payment_country_id, shipping_country_id, $cfb payment_custom_field, shipping_custom_field, $cfe
		language_id, currency_id, shipping_method, comment, store_name, store_url)
		VALUES (
		'{0}', '0', '{37}', '{1}', '{2}', " . ($options['payment_tax_id'] ? "'{3}', " : '') . "
		'{4}', '{5}',
		'{6}', '{7}', '{8}', '{9}', '', '{10}',
		'{11}', '{12}', '$addr_fmt', 
		'{13}', '{14}', '{15}', '{16}', '', '{17}',
		'{18}', '{19}', '$addr_fmt', 

		'{35}', $v3b '{36}', $v3e '{20}', '{20}','{21}', '{22}', '1.000000', $v3b '{23}', $v3e

		$cfb '{24}', $cfe '{25}', '{26}', $cfb '{27}', '{28}', $cfe
		'{29}', '{30}', '{31}', '{32}', '{33}', '{34}')";

	DB_Query($sql, empty($request['previous_order_id']) ? null : $request['previous_order_id'], (empty($invoice_firstname) and empty($invoice_lastname) and !empty($request['invoice_company'])) ? $request['invoice_company'] : $invoice_firstname, $invoice_lastname, $request['invoice_nip'],
		 $request['phone'], $request['email'],
		 $delivery_firstname, $delivery_lastname, $request['delivery_company'], $request['delivery_address'], $request['delivery_city'],
		 $request['delivery_postcode'], $request['delivery_country'],
		 ($invoice_firstname or $invoice_lastname) ? $invoice_firstname : $request['invoice_company'], $invoice_lastname, $request['invoice_company'], $request['invoice_address'], $request['invoice_city'],
		 $request['invoice_postcode'], $request['invoice_country'],

		 date('Y-m-d H:i:s'), isset($request['status_id']) ? (int)$request['status_id'] : 1, $request['currency'] ? $request['currency'] : 'PLN', isset($request['delivery_method_id']) ? $request['delivery_method_id'] : 0,

		 json_encode(array('1' => '')), $payment_country_id, $shipping_country_id, json_encode(array()), json_encode(array()),
		 $options['lang_id'], $currency_id, $request['delivery_method'], $request['user_comments'], $store_name, $options['store_url'],
		 $request['payment_method_cod'] ? 'Płatność przy odbiorze' : 'Przelew', $request['payment_method_cod'] ? 'cod' : 'bank_transfer',
	 	 $options['customer_group']);
	$this_order_id = DB_Identity(); //pobieranie numeru nowego zamówienia

	//dodawanie wpisu do tabeli 'order_history'
	$sql = "INSERT INTO `${dbp}order_history` (order_id, order_status_id, date_added, notify, comment) VALUES ({0}, {1}, '{2}', 0, '{3}')";
	DB_Query($sql, $this_order_id, $request['status_id'] ? (int)$request['status_id'] : 1, date('Y-m-d H:i:s'), $request['user_comments']);

	//obsługa produktów w zamówieniu
	$sum_products_price = 0;
	$sum_products_tax = 0;
	$tax_rates = array();

	foreach($request['products'] as $prod)
	{
		$sql = "SELECT  p.*, p.product_id, pd.name product_name, `${dbp}tax_rate`.rate tax_rate,
			pov.weight variant_weight, pov.weight_prefix
			FROM `${dbp}product` p 
			LEFT JOIN `${dbp}product_option_value` pov ON pov.product_id = p.product_id AND pov.product_option_value_id = {0}
			JOIN `${dbp}product_description` pd ON pd.product_id = p.product_id
			LEFT JOIN `${dbp}tax_rule` ON p.tax_class_id = `${dbp}tax_rule`.tax_class_id
			LEFT JOIN `${dbp}tax_rate` ON `${dbp}tax_rule`.tax_rate_id = `${dbp}tax_rate`.tax_rate_id"
			.($options['geo_zone_id'] ? (" and `${dbp}tax_rate`.geo_zone_id = " . $options['geo_zone_id']) : '')
			." WHERE p.product_id = {1} AND pd.language_id = {2} AND `${dbp}tax_rate`.rate IS NOT NULL LIMIT 1";
		$res = DB_Query($sql, (int)$prod['variant_id'], $prod['id'], $options['lang_id']);
		$prod_data = DB_Fetch($res);
		
		//obliczanie cen
		if (isset($prod['tax']))
		{
			$prod_data['tax_rate'] = $prod['tax'];
		}
		else
		{
			$prod_data['tax_rate'] = ($prod_data['tax_rate']!="")?number_format($prod_data['tax_rate'], 2):"0.00";
		}

		if ($options['add_tax']) // wyliczanie ceny netto
		{
			$price = ($prod['price'] - $attr_price) / (1 + $prod_data['tax_rate']/100);
			$final_price = $prod['price'] / (1 + $prod_data['tax_rate']/100);
			$tax = number_format($prod['price'] - $final_price,2,".","");
			$tax_rates[$prod_data['tax_rate']] = $prod_data['tax_rate'];
		} else {
		
			$price = $prod['price'] - $attr_price;
			$final_price = $prod['price'];
			$netto = number_format($final_price*100/($prod_data['tax_rate']+100),2,".","");
			$tax = number_format($final_price-$netto, 2, '.', '');
			$tax_rates[$prod_data['tax_rate']] = $prod_data['tax_rate'];
		}
		
		//aktualizowanie zmiennych licząch sumy
		$sum_products_price += $prod['price'] * $prod['quantity'];
		$sum_products_tax += $tax * $prod['quantity'];
		
		//wybieranie nazwy z bazy lub nadesłanej
		if($prod_data['product_name'] == "")
		{$prod_data['product_name'] = $prod['name'];}
		
		//dodawanie produktu do zamowienia
		$sql = "INSERT INTO `${dbp}order_product` 
			(`order_id` , `product_id` , `model` , `name` , `price` , `total` , `tax` , `quantity` )
			VALUES ({0}, {1}, '{2}', '{3}', {4}, {5}, {6}, {7})";
		DB_Query($sql, $this_order_id, $prod['id'], $prod_data['model'], $prod_data['product_name'], $price, $final_price*$prod['quantity'], $tax, $prod['quantity']);
		$this_order_product_id = DB_Identity();
				
		//zpisanie wariantu produktu
		if ($prod['variant_id'])
		{
			$sql = "INSERT INTO `${dbp}order_option` (order_id, order_product_id, product_option_id, product_option_value_id, name, value, type)
				SELECT {0}, {1}, pov.product_option_id, pov.product_option_value_id, od.name, ovd.name, o.type
				FROM `${dbp}product_option_value` pov
				JOIN `${dbp}product_option` po ON po.product_option_id = pov.product_option_id
				JOIN `${dbp}option` o ON o.option_id = po.option_id
				JOIN `${dbp}option_description` od ON od.option_id = o.option_id AND od.language_id = '{3}'
				JOIN `${dbp}option_value_description` ovd ON ovd.option_value_id = pov.option_value_id AND ovd.language_id = '{3}'
				WHERE pov.product_option_value_id = {2}";
			DB_Query($sql, $this_order_id, $this_order_product_id, $prod['variant_id'], $options['lang_id']);
		}
	
		//zmniejszanie stanu magazynowego produktu (jeśli ustawiona flaga change_products_quantity)
		if ($request['change_products_quantity'] == 1)
		{
			Shop_ProductsQuantityUpdate(array('products' => array(array('product_id' => $prod['id'], 'variant_id' => $prod['variant_id'], 'operation' => 'change', 'quantity' => -1*$prod['quantity']))));
		}              
	}
	
	//formatowanie cen
	$sum_products_price = number_format($sum_products_price, 2, ".", "");
	$sum_products_tax = number_format($sum_products_tax, 2, ".", "");
	$total_price = number_format($sum_products_price + $request['delivery_price'], 2, ".", "");
	$shipping_tax_rate = isset($options['def_tax']) ? $options['def_tax'] : 23;

	$sql = "SELECT `rate`
		FROM `${dbp}tax_rule` tru
		LEFT JOIN `${dbp}tax_rate` tra ON tru.tax_rate_id = tra.tax_rate_id
		WHERE based = 'shipping' ORDER BY priority LIMIT 1";

	if ($tr = DB_Result(DB_Query($sql)))
	{
		$shipping_tax_rate = $tr;
	}

	$request['delivery_price'] = number_format($request['delivery_price']/($options['add_tax'] ? (1+$shipping_tax_rate/100) : 1), 2, ".", "");
	
	//nazwa podatku
	if(count($tax_rates) == 1)
	{$tax_name = "Podatek (".array_pop($tax_rates)."%):";}
	else
	{$tax_name = "Podatek:";}

	//sprawdzanie czy tabela order_total posiada pole `text`
	$has_text = DB_NumRows(DB_Query("SHOW COLUMNS FROM `${dbp}order_total` LIKE 'text'"));

	//dodawanie wartości do tabeli orders_total
	$sql = "INSERT INTO `${dbp}order_total` (`order_id`, `title`{7}, `text`{8}, `value`, `code`, `sort_order`) VALUES 
		('{0}', 'Podsuma:'{7}, '{1} PLN'{8}, '{1}', 'sub_total', '1'),
		('{0}', '{2}'{7}, '{3} PLN'{8}, '{3}', 'tax', '2'),
		('{0}', '{4} :'{7}, '{5} PLN'{8}, '{5}', 'shipping', '3'),
		('{0}', 'Suma:'{7}, '<B>{6} PLN</B>'{8}, '{6}', 'total', '900');";
	DB_Query($sql, $this_order_id, $sum_products_price, $tax_name, $sum_products_tax, $request['delivery_method'], $request['delivery_price'],
		$total_price, $has_text ? '' : '/*', $has_text ? '' : '*/');
	DB_Query("UPDATE `${dbp}order` SET total = {0} WHERE order_id = {1}", $total_price, $this_order_id);
                
	$response = array('order_id' => $this_order_id);
	return $response;
}





 /**
 * Funkcja pobiera zamówienia złożone w sklepie internetowym
 * Zwracane liczby (np ceny) powinny mieć format typu: 123456798.12 (kropka oddziela część całkowitą, 2 miejsca po przecinku)
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, zawiera informacje o zamówieniu w formacie:
 *		time_from => czas od którego mają zastać pobrane zamówienia - format UNIX TIME
 *		id_from => ID od którego mają zastać pobrane zamówienia
 *		only_paid => flaga określająca czy pobierane mają być tylko zamówienia opłacone (0/1)
 * @return array $response tablica zawierająca dane zamówień:
 * 		id zamówienia => array:
 *						delivery_fullname, delivery_company, delivery_address, delivery_city, delivery_postcode, delivery_country => dane dotyczące adresu wysyłki
 *						invoice_fullname, invoice_company, invoice_address, invoice_city, invoice_postcode, invoice_country, invoice_nip => dane dotyczące adresu płatnika faktury
 *						phone => nr telefonu, email => adres email, 
 *						date_add => data złożenia zamówienia,
 *						payment_method => nazwa metody płatności,
 *						user_comments => komentarz klienta do zamówienia,
 *						status_id => numer ID statusu zamówienia,
 *						delivery_method_id => numer ID metody wysyłki, delivery_method => nazwa metody wysyłki,
 *						delivery_price => cena wysyłki
 *						products => array:
 *									[] =>
 *										id => id produktu, variant_id => id wariantu produktu (0 jeśli produkt główny), 
										name => nazwa produktu
 *										quantity => zakupiona ilość, price => cena sztuki brutto (uwzględniająca atrybuty) 
 *										weight => waga produktu w kg, tax => wysokość podatku jako liczba z zakresu 0-100
 *										attributes => array: - tablica z wybieralnymi atrybutami produktów (jeśli istnieją)
 *														[] =>
 *															name => nazwa atrybutu (np 'kolor'), 
 *															value => wartość atrubutu (np 'czerwony'), 
 *															price => różnica w cenie w stosunku do ceny standardowe									
 */
function Shop_OrdersGet($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	$request['order_id'] = isset($request['order_id']) ? (int)$request['order_id'] : 0;
	//sprawdzanie istnienia dodatkowych tabeli
	$has_paczkomaty_machines = DB_NumRows(DB_Query("SHOW TABLES LIKE 'paczkomaty_machines'"));
	$has_paczkawruchu_machines = DB_NumRows(DB_Query("SHOW TABLES LIKE 'paczkawruchu_machines'"));
	$has_parcel2order = DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}inpost_parcel2order'"));
	$has_op_discount = DB_NumRows(DB_Query("SHOW FIELDS FROM `${dbp}order_product` LIKE 'discount'"));
	$has_paynow = DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}paynow_payments'"));

	//status zamówień opłaconych ustawiany przez moduł OpenPayU
	$sql = "SELECT `value` FROM `${dbp}setting` WHERE `key` like '%payu_complete_status'";
	$payu_complete = DB_Result(DB_Query($sql));

	//status zamówień opłaconych ustawiany przez moduł DotPay
	$sql = "SELECT `value` FROM `${dbp}setting` WHERE `key` = 'dotpay_new_status_completed'";
	$dotpay_complete = DB_Result(DB_Query($sql));

	//status zamówień opłaconych ustawiany przez moduł Tpay
	$sql = "SELECT `value` FROM `${dbp}setting` WHERE `key` = 'payment_tpay_order_status_completed'";
	$tpay_complete = DB_Result(DB_Query($sql));

	//status zamówień opłaconych ustawiany przez moduł PayPal
	$sql = "SELECT `value` FROM `${dbp}setting` WHERE `key` = 'pp_standard_completed_status_id' OR `key` = 'payment_pp_standard_completed_status_id'";
	$paypal_complete = DB_Result(DB_Query($sql));

	//status zamówień opłaconych ustawiany przez moduł CashBill
	$sql = "SELECT GROUP_CONCAT(`value` SEPARATOR ',') FROM `${dbp}setting` WHERE `key` LIKE 'payment_cashbill_%order_status_id'";
	$cashbill_complete = DB_Result(DB_Query($sql));

	//status zamówień opłaconych ustawiany przez moduł Przelewy24
	$sql = "SELECT `value` FROM `${dbp}setting` WHERE `key` = 'przelewy24_order_status_id' OR `key` = 'payment_przelewy24_order_status_id'";
	$p24_complete = DB_Result(DB_Query($sql));

	//status zamówień opłaconych ustawiany przez moduł Comgate
	$sql = "SELECT `value` FROM `${dbp}setting` WHERE `key` = 'payment_comgate_status'";
	$comgate_complete = DB_Result(DB_Query($sql));

	//status zamówień opłaconych ustawiany przez moduł BluePayment
	$sql = "SELECT `value` FROM `${dbp}setting` WHERE `key` = 'payment_bluepayment_status_success'";
	$bluepayment_complete = DB_Result(DB_Query($sql));

	//status zamówień opłaconych ustawiany przez moduł Apple Pay, Google Pay, GP webpay
	$sql = "SELECT `key`, `value` FROM `${dbp}setting` WHERE `key` LIKE 'payment_gpwebpaygpebinder%_status'";

	if ($res = DB_Query($sql))
	{
		$gpwebpaygpebinder_complete = array();

		while ($row = DB_Fetch($res))
		{
			$gpwebpaygpebinder_complete[preg_replace('/^payment_(\w+?)_status$/', '$1', $row['key'])] = $row['value'];
		}
	}

	$use_upload_names = DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}upload'"));
	$response = array();
	$per_page = 50;
	$page = empty($request['page']) ? 1 : (int)$request['page'];

	//zapytanie pobierające zamówienia od określonego czasu
	$sql = "SELECT o.*, UNIX_TIMESTAMP(o.date_added) as time_purchased,
		" . ($has_parcel2order ? 'ip2o.parcel ip2o_paczkomat,' : '') . "
		" . ($has_paynow ? 'pnp.id_payment paynow_transaction_id,' : '') . "
		pc.iso_code_2 as payment_country_code, sc.iso_code_2 as shipping_country_code,
		pz.code payment_zone_code, sz.code shipping_zone_code
		FROM `${dbp}order` o
		LEFT JOIN `${dbp}country` pc ON pc.country_id = o.payment_country_id
		LEFT JOIN `${dbp}zone` pz ON pz.zone_id = o.payment_zone_id
		LEFT JOIN `${dbp}country` sc ON sc.country_id = o.shipping_country_id
		LEFT JOIN `${dbp}zone` sz ON sz.zone_id = o.shipping_zone_id
		"
		. ($has_parcel2order ? "LEFT JOIN `${dbp}inpost_parcel2order` ip2o ON ip2o.order_id = o.order_id": '')
		. ($has_paynow ? "LEFT JOIN `${dbp}paynow_payments` pnp ON pnp.id_order = o.order_id AND pnp.status = 'CONFIRMED'": '') .
		"
		WHERE " . (empty($request['order_id']) ? "order_status_id != 0 AND o.date_added >= '{0}' AND o.order_id > {1}"
		. (($options['store_id'] === '') ? '' : " AND o.store_id = '{2}'")
		. " AND o.order_id IN (SELECT order_id FROM `${dbp}order_history`)" : "o.order_id = {3}");
	$sql .= ' ORDER BY o.order_id LIMIT ' . (($page-1)*$per_page) . ', ' . ($per_page+1);

	$res = DB_Query($sql, date('Y-m-d H:i:s', $request['time_from']), isset($request['id_from']) ? (int)$request['id_from'] : 0, (int)$options['store_id'], $request['order_id']);

	while($order = DB_Fetch($res))
	{
		if (count($response) >= $per_page)
		{
			$response['pages'] = $page+1;
			break;
		}

		$o = array();
		$o['delivery_fullname'] = trim($order['shipping_firstname'] . ' ' . $order['shipping_lastname']);
		$o['delivery_company'] = preg_replace_callback('/&\w+;/', function($m) { return html_entity_decode(strtolower($m[0])); }, $order['shipping_company']);
		$o['delivery_address'] = $order['shipping_address_1'] . ($order['shipping_address_2'] ? ("\n" . $order['shipping_address_2']) : '');
		$o['delivery_city'] = $order['shipping_city'];
		$o['delivery_postcode'] = $order['shipping_postcode'];
		$o['delivery_country'] = $order['shipping_country'];
		$o['delivery_country_code'] = $order['shipping_country_code'];
		$o['invoice_fullname'] = $order['payment_firstname'] . ' ' . $order['payment_lastname'];
		$o['invoice_company'] =  preg_replace_callback('/&\w+;/', function($m) { return html_entity_decode(strtolower($m[0])); }, $order['payment_company']);
		$o['invoice_nip'] = isset($order['payment_tax_id']) ? $order['payment_tax_id'] : '';
		$o['invoice_address'] = $order['payment_address_1'] . ($order['payment_address_2'] ? ("\n" . $order['payment_address_2']) : '');
		$o['invoice_city'] = $order['payment_city'];
		$o['invoice_postcode'] = $order['payment_postcode'];
		$o['invoice_country'] = $order['payment_country'];
		$o['invoice_country_code'] = $order['payment_country_code'];

		if ($o['invoice_country_code'] != 'PL' and !empty($order['payment_zone_code']))
		{
			$o['invoice_state_code'] = $order['payment_zone_code'];
		}

		if ($o['delivery_country_code'] != 'PL' and !empty($order['shipping_zone_code']))
		{
			$o['delivery_state_code'] = $order['shipping_zone_code'];
		}

		$o['phone'] = $order['telephone'];
		$o['email'] = $order['email'];
		$o['date_add'] = $order['time_purchased'];
		$o['payment_method'] = $order['payment_method'];
		$o['delivery_method_id'] = preg_replace('/\..+$/', '', $order['shipping_code']);
		$o['payment_method'] = $order['payment_method'];
		$o['payment_code'] = $order['payment_code'];
		$o['status_id'] = $order['order_status_id'];
		$o['currency'] = isset($order['currency_code']) ? $order['currency_code'] : 'PLN';

		// nip z pola custom
		if (!empty($options['nip_cf']))
		{
			foreach (array('custom_field', 'payment_custom_field', 'shipping_custom_field') as $cf)
			{
				if (!empty($order[$cf]) and $cf = @json_decode($order[$cf], true))
				{
					if (isset($cf[$options['nip_cf']]))
					{
						$o['invoice_nip'] = $cf[$options['nip_cf']];
						break;
					}
				}
			}
		}

		// multiplikator waluty
		$currency_mult = $order['currency_value'] ? $order['currency_value'] : 1;

		// czy klient chce fakturę
		if (!empty($order['invoice_status']) or preg_match('/\d+/', $o['invoice_nip']))
		{
			$o['want_invoice'] = 1;
		}

		if (!empty($options['want_invoice_cf']) and preg_match('/(\d+):(\d+)/', $options['want_invoice_cf'], $m))
		{
			foreach (array('custom_field', 'payment_custom_field', 'shipping_custom_field') as $cf)
			{
				if (!empty($order[$cf]) and $cf = @json_decode($order[$cf], true))
				{
					if (isset($cf[$m[1]]) and ((is_array($cf[$m[1]]) and reset($cf[$m[1]]) == $m[2]) or $cf[$m[1]] == $m[2]))
					{
						$o['want_invoice'] = 1;
					}
				}
			}
		}

		// opcjonalne dane do faktury
		if (!empty($order['invoice_address']))
		{
			$o['invoice_fullname'] = $order['invoice_name'];
			$o['invoice_address'] = $order['invoice_address'];
			$o['invoice_postcode'] = $order['invoice_postcode'];
			$o['invoice_city'] = $order['invoice_city'];
			$o['invoice_company'] = ''; // brak odpowiednika w tabeli zamówień
		}

		$o['paid'] = 0;

		$mapped_payments = '';

		if (preg_match_all('/(\w+)\s*:\s*\d+/', $options['status_paid'], $m))
		{
			$mapped_payments = '|' . implode('|', $m[1]);
		}

		if (!empty($order['paynow_transaction_id']))
		{
			$o['paid'] = 1;
			$o['transaction_id'] = $order['paynow_transaction_id'];
		}
		//zapłacone/nie zapłacone, na podstawie statusu zamówienia
		elseif (preg_match('/payu|dotpay|tpay|pp_standard|cashbill|eway|przelewy24|comgate|bluepayment|stripe|gpwebpaygpebinder'.$mapped_payments.'/i', $order['payment_code']))
		{
			$sql = "SELECT count(*) FROM `${dbp}order_history` oh
				JOIN `${dbp}order_status` os ON oh.order_status_id = os.order_status_id
				WHERE order_id = '{0}' AND ";

			if (preg_match('/payu/i', $order['payment_code']) and $payu_complete)
			{
				$sql .= "oh.order_status_id = '$payu_complete'";
			}
			elseif (preg_match('/dotpay/i', $order['payment_code']) and $dotpay_complete)
			{
				$sql .= "oh.order_status_id = '$dotpay_complete'";
			}
			elseif (preg_match('/tpay/i', $order['payment_code']) and $tpay_complete)
			{
				$sql .= "oh.order_status_id = '$tpay_complete'";
			}
			elseif (preg_match('/pp_/i', $order['payment_code']) and $paypal_complete)
			{
				$sql .= "oh.order_status_id = '$paypal_complete'";
			}
			elseif (preg_match('/cashbill/i', $order['payment_code']) and $cashbill_complete)
			{
				$sql .= "oh.order_status_id IN ($cashbill_complete)";
			}
			elseif (preg_match('/przelewy24/i', $order['payment_code']) and $p24_complete)
			{
				$sql .= "oh.order_status_id = $p24_complete";
			}
			elseif (preg_match('/comgate/i', $order['payment_code']) and $comgate_complete)
			{
				$sql .= "oh.order_status_id = $comgate_complete";
			}
			elseif (preg_match('/bluepayment/i', $order['payment_code']) and $bluepayment_complete)
			{
				$sql .= "oh.order_status_id = $bluepayment_complete";
			}
			elseif (preg_match('/gpwebpaygpebinder/i', $order['payment_code']) and isset($gpwebpaygpebinder_complete[$order['payment_code']]))
			{
				$sql .= "oh.order_status_id = '" . $gpwebpaygpebinder_complete[$order['payment_code']] . "'";
			}
			elseif ($order['payment_code'] === 'stripe')
			{
				$sql .= 'oh.comment = "Stripe payment success"';
			}
			else
			{
				if (preg_match('/^\w+$/', $order['payment_code']) and preg_match('/\b' . $order['payment_code'] . '\s*:\s*(\d+)/', $options['status_paid'], $m))
				{
					$sql .= 'oh.order_status_id = ' . $m[1];
				}
				else
				{
					$sql .= "(name LIKE 'complete' OR name LIKE 'zakończone')";
				}
			}

			$o['paid'] = DB_Result(DB_Query($sql, $order['order_id'])) ? 1 : 0;
		}
		elseif ($order['payment_code'] == 'pp_express')
		{
			if (!isset($options['pott']))
			{
				$options['pott'] = DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}paypal_order_transaction'"));
			}

			if ($options['pott'])
			{
				if ($pmt_status = DB_Result(DB_Query("SELECT payment_status FROM `${dbp}paypal_order` o
								      LEFT JOIN `${dbp}paypal_order_transaction` t ON o.paypal_order_id = t.paypal_order_id
								      WHERE o.order_id = '{0}'
								      AND transaction_id <> '' AND transaction_entity = 'payment'
								      ORDER BY paypal_order_transaction_id DESC", $order['order_id'])))
				{
					$o['paid'] = ($pmt_status == 'Completed') ? 1 : 0;
				}
			}
		}
		elseif ($order['payment_code'] == 'mollie_creditcard')
		{
			if (!isset($options['mop']))
			{
				$options['mop'] = DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}mollie_payments'"));
			}

			if ($options['mop'])
			{
				if (DB_Result(DB_Query("SELECT count(*) FROM `${dbp}mollie_payments` 
						      WHERE order_id = '{0}' AND bank_status = 'paid'
						      AND amount > 0 AND amount > {1}-0.01", $order['order_id'], $order['total'])))
				{
					$o['paid'] = 1;
				}
			}
		}

		if (!$o['paid'] and !empty($order['paid']) and $order['paid'] == '1')
		{
			$o['paid'] = 1;
		}

		//komentarz do zamówienia
		$o['user_comments'] = preg_replace('/^text_collection/', '', $order['comment']);

		//delivery name and price
		$o['delivery_price'] = 0;
		$sql = "SELECT title, value FROM `${dbp}order_total` WHERE order_id = {0} AND code = 'shipping' ORDER BY order_total_id DESC";
		$result = DB_Query($sql, $order['order_id']);
		
		while  ($delivery = DB_Fetch($result))
		{
			$o['delivery_method'] = $delivery['title'];
			$o['delivery_price'] += number_format($delivery['value'], 2, ".", "");
		}

		//paczkomat lub inny punkt odbioru
		if (!empty($order['shipping_custom_field']))
		{
			if ($data = @json_decode($order['shipping_custom_field'], true))
			{
				if (!empty($data['pickup_point']))
				{
					$data = preg_split('/\s*,\s*/', $data['pickup_point']);
					$o['delivery_point_name'] = array_shift($data);
					if (count($data) == 2)
					{
						$o['delivery_point_address'] = array_shift($data);
						$o['delivery_point_city'] = array_shift($data);
					}
				}
				elseif (preg_match('/inpost/i', $order['shipping_code']) and isset($data['address']['1']))
				{
					$o['delivery_point_id'] = $data['address']['1'];
					$o['delivery_point_name'] = $data['address']['1'];
				}
			}
		}
		elseif (!empty($order['inpost']) and !$order['delivery_point_name'])
		{
			$o['delivery_point_name'] = $order['inpost'];
		}

		if (empty($o['delivery_point_name']) and preg_match('/inpostlockerpl([\w\-]{6,})$/', $order['shipping_code'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
		}

		if (empty($o['delivery_point_name']))
		{
			// plugin PP
			if (!empty($order['point_shipping_name']))
			{
				$o['delivery_point_name'] = $order['point_shipping_name'];
				$o['delivery_point_address'] = $order['point_shipping_address'];
				$o['delivery_point_city'] = $order['point_shipping_city'];
				$o['delivery_point_postcode'] = $order['point_shipping_postcode'];
			}
		}

		// DPD pickup/Alza Box
		if (empty($o['delivery_point_name']) and preg_match('/^(.*?(dpd|alza).+?)\s+\((\w+), (.+?), (\d+) (.+?)( [A-Z]{2})?\)/iu', $o['delivery_method'], $m))
		{
			$o['delivery_method'] = $m[1];
			$o['delivery_point_name'] = $m[3];
			$o['delivery_point_id'] = $m[3];
			$o['delivery_point_address'] = $m[4];
			$o['delivery_point_postcode'] = $m[5];
			$o['delivery_point_city'] = $m[6];
		}

		// Balikovna
		if (empty($o['delivery_point_name']) and preg_match('/^(.*?(balík|balík).+?)\s+\(([^\(]+?), (\d{5}), (.+?)\)/iu', $o['delivery_method'], $m))
		{
			$o['delivery_method'] = $m[1];
			$o['delivery_point_address'] = $m[3];
			$o['delivery_point_postcode'] = $m[4];
			$o['delivery_point_city'] = $m[5];

			if (!empty($order['branch_id']))
			{
				$o['delivery_point_id']= $order['branch_id'];
			}
		}
		// paczkomat
		if (empty($o['delivery_point_name']) and empty($o['delivery_point_id']) and preg_match('/^inpost.inpost_([\w\-]+)/', $order['shipping_code'], $m))
		{
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_name'] = $m[1];

 			if (preg_match('/\(' . $o['delivery_point_id'] . '\) - (.+?), ([^,]+)$/', $order['shipping_method'], $m))
			{
				$o['delivery_point_address'] = $m[1];
				$o['delivery_point_city'] = $m[2];
			}
		}

		if (empty($o['delivery_point_name']) and empty($o['delivery_point_id']) and $order['shipping_code'] == 'inpost.inpost' and preg_match('/^\(([\w-]+)\)\s+(.+)$/', $order['shipping_address_1'], $m))
		{
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_city'] = $order['shipping_city'];
			$o['delivery_point_postcode'] = $order['shipping_postcode'];
		}

		if (empty($o['delivery_point_name']) and empty($o['delivery_point_id']) and preg_match('/^paczkomaty\.paczkomaty\d/', $order['shipping_code']) and preg_match('/\(([\w\-]+)\), ([^,]+), (\d\d-\d{3}) (.+)/', $order['shipping_address_format'], $m))
		{
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_city'] = $m[4];
			$o['delivery_point_postcode'] = $m[3];
		}

		if (empty($o['delivery_point_name']) and empty($o['delivery_point_id']) and !empty($order['ip2o_paczkomat']) and preg_match('/^([A-Z0-9\-]+)\s+\((.+?) (\d\d-\d{3}) (.+?)\)/', $order['ip2o_paczkomat'], $m))
		{
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_name'] = 'Paczkomat ' . $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_city'] = $m[4];
			$o['delivery_point_postcode'] = $m[3];
		}

		// kiosk, etc.
		if (empty($o['delivery_point_name']) and empty($o['delivery_point_id']) and $order['shipping_code'] == 'paczkawruchu.paczkawruchu' and preg_match('/^([\w\-]+?)\s*<br\/>\s*(.+?)\s*<br\/>\s*(.+?)\s*<br\/>/', $order['shipping_address_format'], $m))
		{
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_city'] = $m[3];
		}

		if (empty($o['delivery_point_name']) and empty($o['delivery_point_id']) and isset($order['parcelLocker']) and preg_match('/[A-Z]\d/', $order['parcelLocker']))
		{
			$o['delivery_point_id'] = $o['delivery_point_name'] = $order['parcelLocker'];
		}

		// DPD Pickup
		if (empty($o['delivery_point_name']) and empty($o['delivery_point_id']) and preg_match('/dpd pickup.+?\[\s*(\w+)\s*\]$/i', $o['delivery_method'], $m))
		{
			$o['delivery_point_id'] = $o['delivery_point_name'] = $m[1];
		}

		// dane punktu odbioru z komentarza
		if (empty($o['delivery_point_name']) and $has_paczkawruchu_machines and preg_match('/Wybrany punkt "paczki w ruchu"\s*:\s*([\w-]+),/si', $o['user_comments'], $m))
		{
			if ($pwr = DB_Fetch(DB_Query("SELECT street, c.name city, post_code FROM `paczkawruchu_machines` m LEFT JOIN `paczkawruchu_city` c ON m.city_id = c.id WHERE m.name = '{0}'", $m[1])))
			{
				$o['delivery_point_name'] = $m[1];
				$o['delivery_point_city'] = mb_convert_case($pwr['city'], MB_CASE_TITLE, 'UTF-8');
				$o['delivery_point_address'] = $pwr['street'];
				$o['delivery_point_postcode'] = $pwr['post_code'];
			}
		}

		if (empty($o['delivery_point_name']) and $has_paczkomaty_machines and preg_match('/Wybrany paczkomat\s*:\s*([\w-]+),/si', $o['user_comments'], $m))
		{
			if ($paczkomat = DB_Fetch(DB_Query("SELECT street, building_number, post_code, c.name city FROM `paczkomaty_machines` m LEFT JOIN `paczkomaty_city` c ON m.city_id = c.id WHERE m.name = '{0}'", $m[1])))
			{
				$o['delivery_point_name'] = $m[1];
				$o['delivery_point_city'] = mb_convert_case($paczkomat['city'], MB_CASE_TITLE, 'UTF-8');
				$o['delivery_point_address'] = trim($paczkomat['street'] . ' ' . $paczkomat['building_number']);
				$o['delivery_point_postcode'] = $paczkomat['post_code'];
			}
		}

		if (empty($o['delivery_point_name']) and preg_match('/Wybrany paczkomat\s*:\s*\[Paczkomat - ([\w-]+) (.+?) (\d\d-\d{3}) (.+?)\]/', $o['user_comments'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_postcode'] = $m[3];
			$o['delivery_point_city'] = $m[4];
		}

		if (empty($o['delivery_point_name']) and preg_match('/Paczkomat (-|InPost:) ([A-Z]{3}[\w-]+) (.+?) (\d\d-\d{3}) (.+)$/', $order['shipping_method'].$order['comment'], $m))
		{
			$o['delivery_point_name'] = $m[2];
			$o['delivery_point_id'] = $m[2];
			$o['delivery_point_address'] = $m[3];
			$o['delivery_point_postcode'] = $m[4];
			$o['delivery_point_city'] = $m[5];
		}

		if (empty($o['delivery_point_name']) and preg_match('/paczkom/i', $o['delivery_method']) and preg_match('/^([A-Z][\w\-]+), (\d\d\-\d{3}) [^,]+, [\w\s\-]+$/u', $order['comment'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_postcode'] = $m[2];
			$o['delivery_point_address'] = $m[3];
			$o['delivery_point_city'] = $m[4];
		}

		if (empty($o['delivery_point_name']) and preg_match('/paczkom/i', $o['delivery_method']) and preg_match('/\[ ([A-Z][\w\-]+), (.+?), (\d\d-\d{3}) ([\w\s]+) \]/u', $o['delivery_method'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_postcode'] = $m[3];
			$o['delivery_point_city'] = $m[4];
		}

		if (empty($o['delivery_point_name']) and preg_match('/(\w.+?), (\d\d-\d{3}) ([\w\-]+) ;Paczkomat ([\w\-]+)/su', $order['comment'], $m))
		{
			$o['delivery_point_address'] = $m[1];
			$o['delivery_point_postcode'] = $m[2];
			$o['delivery_point_city'] = $m[3];
			$o['delivery_point_name'] = $m[4];
			$o['delivery_point_id'] = $m[4];
			$o['user_comments'] = preg_replace('/(\w.+?), (\d\d-\d{3}) ([\w\-]+) ;Paczkomat ([\w\-]+)\W*/su', '', $o['user_comments']);
		}

		if (empty($o['delivery_point_name']) and preg_match('/Punkt odbioru: (\w.+?), (\d\d-\d{3}) (.+?), ([\w\-\s]+)$/mu', $order['comment'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_postcode'] = $m[2];
			$o['delivery_point_address'] = $m[3];
			$o['delivery_point_city'] = $m[4];
			$o['user_comments'] = preg_replace('/Punkt odbioru: (\w.+?), (\d\d-\d{3}) (.+?), ([\w\-\s]+)$/su', '', $o['user_comments']);
		}

		if (empty($o['delivery_point_name']) and preg_match('/Paczkomat:? ([\w\-]+)/', $order['comment'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
		}

		// paczka w ruchu
		if (empty($o['delivery_point_name']) and preg_match('/\(([A-Z]{2}-[0-9\-]+)\)$/i', $o['delivery_method'], $m))
		{
			$o['delivery_point_name'] = $m[1];
		}

		// dane punktu z pola extra_info
		if (empty($o['delivery_point_name']) and empty($o['delivery_point_id']) and isset($order['extra_info']) and preg_match('/Wybrano paczkomat: ([A-Z\-0-9]{6,})/i', $order['extra_info'], $m))
		{
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_name'] = $m[1];
		}

		if (empty($o['delivery_point_name']) and isset($order['extra_info']) and ($order['extra_info'] == 'Wybrano paczkomat: ' or preg_match('/paczkomat/i', $o['user_comments'].$o['delivery_method']))  and preg_match('/([\w\-]+), (\d\d-\d{3}) ([^,]+), ([\w\s]+)/um', $o['user_comments'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_address'] = $m[3];
			$o['delivery_point_postcode'] = $m[2];
			$o['delivery_point_city'] = $m[4];
		}

		if (empty($o['delivery_point_name']) and preg_match('/paczkom/i', $o['delivery_method']) and preg_match('/Paczkomat - ([\w\-]+) (.+?) (\d\d-\d{3}) (.+)$/', $o['delivery_method'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_postcode'] = $m[3];
			$o['delivery_point_city'] = $m[4];
			$o['delivery_method'] = 'Paczkomat';
		}

		if (empty($o['delivery_point_name']) and isset($order['shipping_address_format']) and preg_match('/paczkom/i', $o['delivery_method']) and preg_match('/Paczkomat - ([\w\-]+) (.+?) (\d\d-\d{3}) (.+)$/', $order['shipping_address_format'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_postcode'] = $m[3];
			$o['delivery_point_city'] = $m[4];
			$o['delivery_method'] = 'Paczkomat';
		}

		if (!isset($o['delivery_point_name']) and !isset($o['delivery_point_id']) and $o['delivery_country_code'] == 'CZ')
		{
			$dm = $o['delivery_method'];
			$dm_chunks = preg_split('/\).+?\(/', ')' . $dm);
			$dp = array_pop($dm_chunks);

			if (preg_match('/^(.+?), (.+?)\)?\s*$/', $dp, $m) and count($dm_chunks))
			{
				$o['delivery_point_name'] = $m[1];
				$o['delivery_point_address'] = $m[2];
				$o['delivery_method'] = preg_replace('/\s*[\(\)]+\s*$/', '', str_replace($dp, '', $dm));

				if (!empty($order['branch_id']))
				{
					$o['delivery_point_id'] = $order['branch_id'];
				}
			}
		}

		// Sameday RO
		if (empty($o['delivery_point_name']) and preg_match('/easybox/i', $order['shipping_method']) and preg_match('/sameday/i', $order['shipping_method']))
		{
			if ($last_point_pos = strrpos($order['shipping_code'], '.'))
			{
				$sameday_locker_id = substr($order['shipping_code'], $last_point_pos + 1);
				$sql = "SELECT * FROM `${dbp}sameday_locker` WHERE locker_id = '{0}'";
				$result = DB_Query($sql, $sameday_locker_id);

				if ($sameday_locker_info = DB_Fetch($result))
				{
					$o['delivery_point_name'] = $sameday_locker_info['name'] ?? '';
					$o['delivery_point_id'] = $sameday_locker_info['locker_id'] ?? '';
					$o['delivery_point_address'] = $sameday_locker_info['address'] ?? '';
					$o['delivery_point_postcode'] = $sameday_locker_info['postal_code'] ?? '';
					$o['delivery_point_city'] = $sameday_locker_info['city'] ?? '';
					$o['delivery_state_code'] = $sameday_locker_info['county'] ?? '';
					$o['delivery_method'] = 'Livrare in Easybox Sameday' ?? '';
				}
			}
		}

		//płatność za pobraniem
		if ($order['payment_code'] == 'cod' or preg_match('/pobran|przy odb/i', $o['payment_method']))
		{
			$o['payment_method_cod'] = 1;
			$o['paid'] = 0;
		}

		//dodatek za pobranie lub inne koszty płatności
		$sql = "SELECT value FROM `${dbp}order_total`
			WHERE order_id = {0} AND (code = 'paycharge' OR code = 'pickup_fee' OR code = 'codfee')
			LIMIT 1";
		$result = DB_Query($sql, $order['order_id']);

		if (DB_NumRows($result))
		{
			$payment_fee = DB_Result($result);
			$o['delivery_price'] = number_format($o['delivery_price']+$payment_fee, 2, ".", "");
		}

		//produkty zamówienia
		$o['products'] = array();

		$total_products = 0; // łączna kwota zakupu
		$total_net_products = 0; // suma kwot netto pobranych ze sklepu
		$other_fees = 0;
		$products_tax = 0; // łączny podatek od produktów
		$tax_major = array(-1); // stawki podatkowe wg liczby pozycji w zamówieniu

		$sql = "SELECT p.*, op.order_product_id, op.tax, op.quantity, op.name, op.product_id id, op.price"
			. ($has_op_discount ? ', op.discount' : '')
			. (empty($options['vean_fld']) ? '' : ", pov.{$options['vean_fld']} vean") . ",
			GROUP_CONCAT(oo.product_option_value_id SEPARATOR '-') variant_id,
			GROUP_CONCAT(" . ($use_upload_names ? "if(isnull(u.name), oo.value, u.name)" : "oo.value") ." SEPARATOR ' ') variant_name
			FROM `${dbp}order_product` op
			LEFT JOIN `${dbp}product` p ON p.product_id = op.product_id
			LEFT JOIN `${dbp}order_option` oo ON oo.order_product_id = op.order_product_id
			" . (empty($options['vean_fld']) ? '' : "LEFT JOIN `${dbp}product_option_value` pov ON pov.product_option_value_id = oo.product_option_value_id") . "
			" . ($use_upload_names ? "LEFT JOIN `${dbp}upload` u ON u.code = oo.value AND oo.type = 'file'" : "") . "
			WHERE op.order_id = {0}
			GROUP BY op.order_product_id";
		$result = DB_Query($sql, $order['order_id']);

		while ($product = DB_Fetch($result))
		{
			$op = array(
				'id' => $product['product_id'],
				'variant_id' => (int)$product['variant_id'],
				'name' => trim($product['name'] . ' ' . $product['variant_name']),
				'price' => number_format($product['price']*$currency_mult, 2, '.', ''),
				'oc_price_raw' => $product['price'],
				'sku' => $product[$options['sku_fld']],
				'ean' => empty($product['vean']) ? (empty($product['ean']) ? '' : $product['ean']) : $product['vean'],
				'quantity' => $product['quantity'],
				'weight' => number_format(_weight_conv($product['weight'], $product['weight_class_id']), 3, '.', ''),
			);

			$op_discount = $has_op_discount ? $product['discount']/$product['quantity']*$currency_mult : 0;

			$add_tax = $options['add_tax'] && DB_Result(DB_Query("SELECT SUM(`value`) FROM `${dbp}order_total` WHERE order_id = {0} and code = 'tax'", $order['order_id']));

			if ($add_tax) // przeliczanie cen netto na brutto
			{
				if ($op['price'] and $op['price'] >= 0.01)
				{
					$tax_rate = number_format(round($product['tax']*$currency_mult/$op['price']*100), 2, ".", "");
					$op['price'] = number_format($op['price']+$op_discount*(1+$product['tax']/$product['price'])+$product['tax']*$currency_mult, 2, ".", "");
				}
			}
			else
			{
				$tax_rate = ($op['price'] >= 0.01) ? number_format(round($product['tax']*$currency_mult/$op['price']*100), 2, ".", "") : 0;
				$op['price'] += $op_discount;
			}

			$products_tax += $product['tax']*$currency_mult*$op['quantity'];
			$op['tax'] = $tax_rate;

			$total_net_products += $product['price']*$currency_mult*$op['quantity'];

			//atrybuty przedmiotów
			$op['attributes'] = array();
			$sql = "SELECT * FROM `${dbp}order_option` WHERE order_id = {0} AND order_product_id = {1}";
			$res_attr = DB_Query($sql, $order['order_id'], $product['order_product_id']);

			while ($attribute = DB_Fetch($res_attr))
			{
				$a['name'] = $attribute['name'];
				$a['value'] = $attribute['value'];
				$a['price'] = 0;
				$op['attributes'][] = $a;
			}

			// formatowanie ceny
			$op['price'] = number_format($op['price'], ($o['currency'] == 'CZK') ? 0 : 2, ".", "");
			$total_products += $op['price']*$op['quantity'];

			// odnotowanie stawki dla tej pozycji zamówienia
			$tax_major[$op['tax']] = isset($tax_major[$op['tax']]) ? $tax_major[$op['tax']] : 1;

			$o['products'][] = $op;
		}

		// wyłonienie najczęściej występującej stawki podatkowej
		arsort($tax_major, SORT_NUMERIC);
		$tax_major = reset(array_keys($tax_major));

		// kupony
		$sql = "SELECT * FROM `${dbp}order_total` WHERE order_id = {0} AND code in ('coupon', 'tieredgiftstotal', 'reward') AND `value` < 0";
		$result = DB_Query($sql, $order['order_id']);
		$discounts_tax = 0;

		while ($discount = DB_Fetch($result))
		{
			$o['discounts'][] = $discount;
			/*
			$o['products'][] = array('id' => 0, 'name' => $discount['title'], 'quantity' => 1, 'tax' => 0,
						 'price' => number_format($discount['value']*$currency_mult*(1+$op['tax']/100), 2, '.', ''));
			$products_tax += $op['tax']/100*$discount['value']*$currency_mult;
			 */

			if ($options['split_discounts'])
			{
				split_discount($o, -$discount['value']*$currency_mult, $discount['title']);

				$total_net_products = 0;
				$total_products = 0;

				foreach ($o['products'] as $op)
				{
					$total_products += $op['price']*$op['quantity'];
					$total_net_products += $op['price']*$op['quantity']/(1+$op['tax']/100);
				}
			}
			else
			{
				$o['products'][] = array('id' => 0, 'name' => $discount['title'], 'quantity' => 1, 'tax' => $tax_major,
				'price' => number_format(in_array($discount['code'], array('reward', 'coupon')) ? $discount['value'] : ($total_products*$discount['value']*$currency_mult/$total_net_products), ($o['currency'] == 'CZK') ? 0 : 2, '.', ''));
				$total_products += end($o['products'])['price']; // do wyliczania rozbieżności poniżej
				$discounts_tax += end($o['products'])['price'] - $discount['value'];
			}

//			$products_tax += ($discount['value']*$currency_mult/$total_net_products)*$products_tax;
		}

		// koszty płatności
		$sql = "SELECT * FROM `${dbp}order_total` WHERE order_id = {0} AND code LIKE '%payment%'";
		$result = DB_Query($sql, $order['order_id']);

		while ($fee = DB_Fetch($result))
		{
			$o['delivery_price'] += $fee['value'];
		}

		// additional fees
		$sql = "SELECT * FROM `${dbp}order_total` WHERE order_id = {0} AND code LIKE '%xfeepro%'";
		$result = DB_Query($sql, $order['order_id']);

		while ($fee = DB_Fetch($result))
		{
			$o['products'][] = array('id' => 0, 'name' => $fee['title'], 'quantity' => 1, 'price' => $fee['value']);
			$other_fees += $fee['value'];
		}

		// konwersja waluty dla kosztów wysyłki
		$o['delivery_price'] *= $currency_mult;

		// CZK rounding
		if ($o['currency'] == 'CZK' and $o['delivery_price'] >= 0.01)
		{
			$o['delivery_price_orig'] = $o['delivery_price'];
			$o['delivery_price'] = round($o['delivery_price']);
		}

		// podatek od wysyłki
		if ($add_tax)
		{
			$sql = "SELECT sum(value) FROM `${dbp}order_total` WHERE order_id = {0} and code = 'tax'";
			$result = DB_Query($sql, $order['order_id']);

			if (DB_NumRows($result))
			{
				$total_tax = DB_Result($result);
				if ($o['delivery_price']!=0) //AG 19-05-2025 
				$o['delivery_price'] = number_format($o['delivery_price'] + $total_tax*$currency_mult - $products_tax - $discounts_tax, ($o['currency'] == 'CZK') ? 0 : 2, '.', '');
			}

			$sql = "SELECT value FROM `${dbp}order_total` WHERE order_id = {0} and code = 'total'";
			$result = DB_Query($sql, $order['order_id']);

			// korekta sumy zamówienia (dodanie różnicy do któregoś z produktów)
			if (DB_NumRows($result))
			{
				$total = DB_Result($result);

				$o['oc_total'] = $total*$currency_mult;
				$o['bl_total'] = $total_products + $o['delivery_price'] + $other_fees;

				if ($total*$currency_mult != $total_products + $o['delivery_price'] + $other_fees)
				{
					$diff = number_format($total*$currency_mult - $total_products - $o['delivery_price'] - $other_fees, 2, '.', '');

					if (abs($diff) < 0.06) // różnica kilku groszy max!
					{
						foreach ($o['products'] as $i => $op)
						{
							$new_price = number_format($op['price'] + $diff/$op['quantity'], 2, '.', '');

							if ($new_price*$op['quantity'] - $op['price']*$op['quantity'] - $diff < 0.001)
							{
								$o['products'][$i]['price'] = $new_price;
								break;
							}
						}
					}
				}
			}
		}

		// brak danych wysyłki - import z danych rozliczeniowych
		if (empty($o['delivery_address']) and empty($o['delivery_fullname']) and empty($o['delivery_company']))
		{
			foreach (array('fullname', 'company', 'address', 'city') as $fld)
			{
				$o["delivery_$fld"] = $o["invoice_$fld"];
			}
		}

		// formatowanie ceny wysyłki
		$o['delivery_price'] = number_format($o['delivery_price'], 2, '.', '');

		// odbiór w paczkomacie
		if (!isset($o['delivery_point_name']) and preg_match('/Odbiór paczki: ([A-Z0-9\-]+), [^,]+, (.+?), (\d\d-\d{3}) (.+?)$/', $o['user_comments'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_postcode'] = $m[3];
			$o['delivery_point_city'] = $m[4];
		}

		// odbiór w paczkomacie #2
		if (!isset($o['delivery_point_name']) and preg_match('/Paczkomat: NR paczkomatu \(([A-Z0-9\-]+)\), (.+?), (\d\d-\d{3}) (.+?)$/', $o['user_comments'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_address'] = $m[2];
			$o['delivery_point_postcode'] = $m[3];
			$o['delivery_point_city'] = $m[4];
		}
	
		// odbiór w paczkomacie #3
		if (!isset($o['delivery_point_name']) and preg_match('/point: ([A-Z0-9\-]+), (\d\d-\d{3}) ([^,]+), (.+?)$/s', $o['user_comments'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[1];
			$o['delivery_point_address'] = $m[3];
			$o['delivery_point_postcode'] = $m[2];
			$o['delivery_point_city'] = $m[4];
			$o['user_comments'] = preg_replace('/^.*?point: [A-Z0-9\-]+, \d\d-\d{3}.+$/m', '', $o['user_comments']);
		}

		// odbiór w UP
		if (!isset($o['delivery_point_name']) and preg_match('/point: ([^\|]+?) \| (\d{6}) \| ([^,]+?), (\d\d-\d{3}), (.+?)$/m', $o['user_comments'], $m))
		{
			$o['delivery_point_name'] = $m[1];
			$o['delivery_point_id'] = $m[2];
			$o['delivery_point_address'] = $m[3];
			$o['delivery_point_postcode'] = $m[4];
			$o['delivery_point_city'] = $m[5];
			$o['user_comments'] = preg_replace('/Collection point: .+?\)$/m', '', $o['user_comments']);
		}

		$response[$order['order_id']] = $o;
	}

	if (!isset($response['pages']))
	{
		$response['pages'] = $page;
	}
	
	return $response;
}


 /**
 * Funkcja aktualizuje zamówienia wcześniej dodane do bazy
 * W przypadku zapisywania numeru nadawaczego, parametr orders_ids będzie przyjmował zawsze tablicę z jedną pozycją
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, zawiera informacje o aktualizacji zamówienia w formacie:
 *		orders_ids => ID zamówień
 *		update_type => typ zmiany - 'status', `delivery_number`, lub 'paid' (aktualizacja statusu zamówienia, dodanie numeru nadawczego i dodanie/usunięcie wpłaty)
 *		update_value => aktualizowana wartość - ID statusu, numer nadawczy lub informacja o opłaceniu zamówienia (bool true/false)
 * @return array $response tablica zawierająca potwierdzenie zmiany:
 * 		'counter' => ilość zamówień w których dokonano zmiany (nawet jeśli zamówienie pozostało takie jak wcześniej)
 */
function Shop_OrderUpdate($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy

	$counter = 0;

	//dla wszystkich nadesłanych numerów zamówień
	foreach($request['orders_ids'] as $order_id)
	{
		if (intval($order_id) != $order_id)
		{continue;}

		//zmiana statusu
		if($request['update_type'] == 'status')
		{
		        DB_Query("UPDATE `${dbp}order` SET order_status_id = {0} WHERE order_id = {1}", $request['update_value'], $order_id);
		        DB_Query("INSERT INTO `${dbp}order_history` (order_id, order_status_id, notify, comment, date_added)
				 VALUES ({0}, {1}, 0, '', '{2}');", $order_id, $request['update_value'], date('Y-m-d H:i:s'));
		}
		//zapisanie numeru nadawczego
		elseif($request['update_type'] == 'delivery_number')
		{
		        DB_Query("UPDATE `${dbp}order` SET tracking = '{0}' WHERE order_id = {1}", $request['update_value'], $order_id);
		}
		//zmiana wpłaty
		elseif($request['update_type'] == 'paid')
		{

		}
		
		$counter++;
	}
	
	return array('counter' => $counter);
}


 /**
 * Funkcja zwraca listę dostępnych statusów zamówień
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica zawierająca dostępne statusy zamówień:
 * 		'status_id' => nazwa statusu
 */
function Shop_StatusesList($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	
	$response = array();
	$sql = "SELECT * FROM `${dbp}order_status` WHERE language_id = {0}";
	$res = DB_Query($sql, $options['lang_id']);

	while($status = DB_Fetch($res))
	{
		$response[$status['order_status_id']] = $status['name'];
	}
		
	return $response;
}


 /**
 * Funkcja zwraca listę dostępnych sposobów wysyłki
 * @global array $options : tablica z ustawieniami ogólnymi z początku pliku
 * @param array $request tablica z żadaniem od systemu, w przypadku tej funkcji nie używana
 * @return array $response tablica zawierająca dostępne sposoby_wysyłki:
 * 		'delivery_id' => nazwa wysyłki
 */
function Shop_DeliveryMethodsList($request)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	
	$response = array();
	$sql = "SELECT code FROM `${dbp}extension` WHERE type = 'shipping'";
	$res = DB_Query($sql);

	while ($method = DB_Fetch($res))
	{
		$name = $method['code'];
		$ext_file = _store_root() . "/catalog/language/${options['lang_dir']}/shipping/${method['code']}.php";

		if (file_exists($ext_file))
		{
			include($ext_file);

			if (isset($_['text_title']))
			{
				$name = $_['text_title'];
			}
		}

		$response[$method['code']] = $name;
	}
		
	return $response;
}

/* autodetekcja katalogu sklepu */
function _store_root()
{
	global $options;
	$look_for = 'system/startup.php'; // charakterystyczna ścieżka bezpośrednio w katalogu głównym sklepu

	if ($options['store_root'] == '')
	{
		// przeszukujemy 3 poziomy w górę i w dół względem lokalizacji pliku integracyjnego
		for ($up = 0; $up < 4; $up++)
		{
			for ($down = 0; $down < 3; $down++)
			{
				$path = getcwd() . '/' . str_repeat('../', $up) . str_repeat('*/', $down) . $look_for;

				if (glob($path)) 
				{
					$path = (glob($path));
					break 2;
				}
			}
		}

		if (is_array($path)) // znaleziono poszukiwany plik
		{
			$options['store_root'] = dirname($path[0]);
		}
	}

	return $options['store_root'];
}

function _weight_conv($value = 0, $class_id = 0)
{
	global $options; //globalna tablica z ustawieniami
	$dbp = $options['db_prefix']; //Data Base Prefix - prefix tabel bazy
	static $wt_mult;
	static $wt_unit;
	static $wt_class;

	if (!isset($wt_mult))
	{
		$wt_mult = $wt_unit = array();

		// sklep obsługuje różne jednostki wagi
		if (DB_NumRows(DB_Query("SHOW TABLES LIKE '${dbp}weight_class_description'")))
		{
			$sql = "SELECT wcd.unit, wc.value, wc.weight_class_id
				FROM `${dbp}weight_class` wc
				JOIN `${dbp}weight_class_description` wcd
				ON wc.weight_class_id = wcd.weight_class_id";
			$res = DB_Query($sql);

			while ($conv = DB_Fetch($res))
			{
				$wt_mult[$conv['unit']] = $conv['value'];
				$wt_mult[$conv['weight_class_id']] = $conv['value'];
				$wt_class[$conv['unit']] = $conv['weight_class_id'];
			}
		}
	}

	if (!is_numeric($value)) // specjalny przypadek - zwróć klasę danej jednostki wagi
	{
		return $wt_class[$value] ? $wt_class[$value] : 1;
	}

	if ($class_id and isset($wt_class)) // zwróć wagę przeliczoną na kg
	{
		$base = $value / (isset($wt_mult[$class_id]) ? $wt_mult[$class_id] : 1);
		return $base * (isset($wt_mult[$wt_class['kg']]) ? $wt_mult[$wt_class['kg']] : 1);
	}

	return $value;
}

function imgurlencode($path = '')
{
	$path = explode('/', $path);

	foreach ($path as $i => $chunk)
	{
		$path[$i] = rawurlencode($chunk);
	}

	return implode('/', $path);
}

function split_discount(&$o, $amt = 0, $title = '')
{
	// informacja o użytym kuponie
	//$o['extra_field_1'] = $title;

	// zliczanie sumy produktów
	$total_products = 0;

	foreach ($o['products'] as $op)
	{
		$total_products += $op['price']*$op['quantity'];
	}

	// współczynnik skalowania cen
	$price_scale_factor = (1-$amt/$total_products);
	$new_total_products = 0;

	// obniżenie cen w zamówieniu
	foreach ($o['products'] as $i => $op)
	{
		$op['orig_price'] = $op['price'];
		$op['price'] = number_format($price_scale_factor*$op['price'], 2, '.', '');
		$o['products'][$i] = $op;
		$new_total_products += $op['price']*$op['quantity'];
	}

	// na skutek błędów zaokrąglania pozostała reszta, którą trzeba doliczyć do któregoś produktu
	if ((abs($total_diff = $total_products - $new_total_products - $amt) > 0.005))
	{
		$diff_split = false; // czy reszta została już rozdzielona
		$min_qty_prod_idx = 0; // indeks produktu z najmniejszą ilością zakupionych sztuk

		foreach ($o['products'] as $i => $op)
		{
			// .. najlepiej do takiego, który zakupiony został w małej liczbie sztuk
			// lub liczbie sztuk pozwalającej na równe rozdzielenie reszty
			if ($op['quantity'] == 1 or !(round($total_diff*100)%$op['quantity']))
			{
				$o['products'][$i]['price'] += $total_diff/$op['quantity'];
				$diff_split = true;
				break;
			}

			if ($op['quantity'] < $o['products'][$min_qty_prod_idx]['quantity'])
			{
				$min_qty_prod_idx = $i;
			}
		}

		// jeśli powyższe się nie uda, doklejamy różnicę do produktu z najmnieszą liczbą sztuk
		if (!$diff_split)
		{
			$o['products'][$min_qty_prod_idx]['price'] += $total_diff/$o['products'][$min_qty_prod_idx]['quantity'];
		}
	}
}
?>
