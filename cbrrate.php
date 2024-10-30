<?php
/*
Plugin Name: CbrRate
Plugin URI: http://www.selikoff.ru/tag/cbrrate/
Description: Виджет курса валют ЦБ РФ на текущий день c динамикой.
Version: 1.1
Author: selikoff
Author URI: http://www.selikoff.ru/
License: GPL2
*/
function cbr_load2($date1){
	$doc = new DOMDocument();
	$doc->encoding = "windows-1251";
	$doc->load("http://www.cbr.ru/scripts/XML_daily.asp?date_req=".$date1);
	$vcurs   = $doc->getElementsByTagName('ValCurs');
	foreach($vcurs as $node){
		$date2 = $node->getAttribute('Date');
	}
	if ($date2!=$date1) return false;
	$_prods  = $doc->getElementsByTagName('Valute');
	foreach($_prods as $item){
	    foreach($item->childNodes as $node){
		$string =  $node->nodeName . ": " . $node->nodeValue;
		if ($node->nodeName == 'CharCode') $codes[] = $node->nodeValue;
		if ($node->nodeName == 'Value') $values[] = sprintf("%.2f",preg_replace("|,|",".",$node->nodeValue));
	    }
	}
	foreach($codes as $i=>$code){
		$currency[$code] = $values[$i];
	}
	return $currency;
}

function cbr_update2(){
	$limit=12;
	$codes = array('USD','EUR','GBP','BYR','CNY','JPY');
	$date1 = $date2 = null;
	$i=0;
	$currency = array();
	do {
		$curtime = mktime(0,0,0,date("m"),date("d")-$i+1,date("Y"));
		$rate = cbr_load2(date("d.m.Y",$curtime));
		$test = cbr_cache_get($codes[0].'_'.$curtime);
		if ($test && $test>0){

		} elseif($rate[$codes[0]]) {
				$currency[] = $rate;
				foreach($codes as $code) {
					cbr_cache_set($code.'_'.$curtime , $rate[$code] );
				}
				if (!$date1) $date1 = $curtime;
				elseif (!$date2)  $date2 = $curtime;
		}
		if (++$i > $limit) {
			$ztime = mktime(0,0,0,date("m"),date("d")-$i+1,date("Y"));
			foreach($codes as $code) {
				cbr_cache_delete($code.'_'.$ztime  );
			}
			break;
		}
	} while(count($currency)<2);

	if ($date1) cbr_cache_set( 'ratedate', $date1, 1 );
	if ($date2) cbr_cache_set( 'prevdate', $date2, 1 );
	return $currency;
}

function cbr_cache_delete($name){
	return delete_option( "cbrrate_".$name );
}
function cbr_cache_get($name){
	return get_option( "cbrrate_".$name );
}
function cbr_cache_set($name,$value,$upd=1){
	if ($upd) return update_option( "cbrrate_".$name, $value );
	else return add_option( "cbrrate_".$name, $value );
}

register_activation_hook(__FILE__, 'cbrrate_activation');
function cbrrate_activation() {
	wp_schedule_event( time(), 'hourly', 'cbrrate_hourly_event');
}

add_action('cbrrate_hourly_event', 'cbrrate_do_this_hourly');
function cbrrate_do_this_hourly() {
	cbr_update2();
}
register_deactivation_hook(__FILE__, 'cbrrate_deactivation');
function cbrrate_deactivation() {
	wp_clear_scheduled_hook('cbrrate_hourly_event');
}



add_action( 'widgets_init', function(){
     register_widget( 'Cbr_Widget' );
});


add_action('init', 'cbrrate_register_script');
function cbrrate_register_script() {
    wp_register_style( 'cbr_style', plugins_url('style.css', __FILE__), false, '1.0.0', 'all');
}
add_action('wp_enqueue_scripts', 'cbrrate_enqueue_style');
function cbrrate_enqueue_style(){
   wp_enqueue_style( 'cbr_style' );
}

class Cbr_Widget extends WP_Widget
{

	function __construct() {
		parent::__construct(
			'cbr_widget', // Base ID
			__( 'CbrRate', 'cbrrate' ), // Name
			array( 'description' => __( 'Central Bank Russia rate exchange', 'cbrrate' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		$limit=12;
		$i=$x=0;
		$currency = array();
		$codes = array('USD','EUR','GBP','BYR','CNY','JPY');
		do {
			$curtime = mktime(0,0,0,date("m"),date("d")-$i+1,date("Y"));
			$date = date("d.m.Y",$curtime);
			$test = cbr_cache_get($codes[0].'_'.$curtime);
			if ($test && $test>0){
				foreach($codes as $code) {
					$currency[$x][$code] = cbr_cache_get($code.'_'.$curtime);
				}
				$x++;
			}
			if (++$i > $limit) break;
		} while(count($currency)<2);

		if (empty($currency[0])) echo "";//"data empty ";
		else {
			if (empty($currency[1])) {
				foreach($codes as $code) {
					$currency[1][$code] = $currency[0][$code];
				}
			}
			$loadingdate = cbr_cache_get( 'ratedate' );
			$delta_usd = sprintf("%.2f", ($currency[0]['USD'] - $currency[1]['USD']));
			$delta_eur = sprintf("%.2f", ($currency[0]['EUR'] - $currency[1]['EUR']));
			echo '
		  <div id="currency">
			<div class="itemcbr">
				<div class="cbrname"><img width="25" height="30" border="0" alt="USD" src="' . WP_PLUGIN_URL . '/cbrrate/img/dollar.png"></div>
				<div class="cbrvalue">'.$currency[0]['USD'].'</div>
				<div class="cbrdif"><img width="9" height="9" src="' . WP_PLUGIN_URL . '/cbrrate/img/'.($delta_usd>0?'up':'dn').'.gif"><span style="font-size:12px;color:'.($delta_usd>0?'green':'red').'">'.$delta_usd.'</span></div>
			</div>
			<div class="itemcbr">
				<div class="cbrname"><img width="25" height="32" border="0" alt="EUR" src="' . WP_PLUGIN_URL . '/cbrrate/img/euro.png"></div>
				<div class="cbrvalue">'.$currency[0]['EUR'].'</div>
				<div class="cbrdif"><img width="9" height="9" src="' . WP_PLUGIN_URL . '/cbrrate/img/'.($delta_eur>0?'up':'dn').'.gif"><span style="font-size:12px;color:'.($delta_eur>0?'green':'red').'">'.$delta_eur.'</span></div>
			</div>
			'.( $loadingdate ? '
			<div class="cbrlegend">Курс ЦБ РФ на '.date("d.m.Y",$loadingdate).'</div>
			':'').'
		  </div>
			';
		}
	}
}


add_action('parse_request', 'cbrrate_custom_url_handler');

function cbrrate_custom_url_handler() {
   $codes = array('USD','EUR','GBP','BYR','CNY','JPY');
   if($_SERVER["REQUEST_URI"] == '/cbrtest') {

	// Test xml reading

	$lines = file('http://www.cbr.ru/scripts/XML_daily.asp');
	foreach ($lines as $line_num => $line) {
	    echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
	}
	exit();
   } elseif($_SERVER["REQUEST_URI"] == '/cbrread') {

	// Test read saved rate info

	$loadingdate = cbr_cache_get( 'ratedate' );
	echo date("d-m-Y H:i:s",$loadingdate);
	echo "<br>";
	$limit=12;
	$i=$x=0;
	$currency = array();
	$codes = array('USD','EUR','GBP','BYR','CNY','JPY');
	do {
		$curtime = mktime(0,0,0,date("m"),date("d")-$i+1,date("Y"));
		$test = cbr_cache_get($codes[0].'_'.$curtime);
		if ($test && $test>0){
			echo date("d-m-Y H:i:s",$curtime)."<br>";
			foreach($codes as $code) {
				$v = cbr_cache_get($code."_".$curtime);
				echo $code."_".$curtime." = ".$v. "<br> " ;
				$currency[$x][$code] = $v;
			}
			$x++;
		}
		if (++$i > $limit) break;
	} while(count($currency)<2);
	exit;

   } elseif($_SERVER["REQUEST_URI"] == '/cbrup') {

	//Test update rate info
	$limit=12;
	$currency = array(0 => array(),1 => array());
	$codes = array('USD','EUR','GBP','BYR','CNY','JPY');
	$date1 = $date2 = 0;
	$i=0;
	$currency = array();
	do {
		$curtime = mktime(0,0,0,date("m"),date("d")-$i+1,date("Y"));
		$rate = cbr_load2(date("d.m.Y",$curtime));
		$test = cbr_cache_get($codes[0].'_'.$curtime);
		echo "Loading ".date("d.m.Y",$curtime);
		print_r($rate);
		echo "<hr>";
		echo "TEST ".$codes[0].'_'.$curtime."<br>";
		if ($test && $test>0){

		} elseif($rate[$codes[0]]) {
				$currency[] = $rate;
				foreach($codes as $code) {
					echo $code.'_'.$curtime." ";
					cbr_cache_set($code.'_'.$curtime , $rate[$code] );
				}
				echo date("d-m-Y H:i:s",$curtime)."<br>";
				if (!$date1) $date1 = $curtime;
				elseif (!$date2)  $date2 = $curtime;
		}
		$i++;
		if ($i > $limit) {
			$ztime = mktime(0,0,0,date("m"),date("d")-$i+1,date("Y"));
			foreach($codes as $code) {
				cbr_cache_delete($code.'_'.$ztime  );
			}
			break;
		}
	} while(count($currency)<2);

	if ($date1) cbr_cache_set( 'ratedate', $date1, 1 );
	if ($date2) cbr_cache_set( 'prevdate', $date2, 1 );

	echo "<hr>";
	print_r($currency);
	echo "<hr>";
	echo " date1=".date("Y-m-d",cbr_cache_get( 'ratedate'));
	echo " date2=".date("Y-m-d",cbr_cache_get( 'prevdate'));
	exit();
   }

}
?>
