<?php
/**
 * DCRM Commercial Package API
 *
 * Copyright (c) 2015 Hintay <hintay@me.com>
 */


if(isset($_GET['action'])){
	switch($_GET['action']){
		// 支付宝跳转
		case 'alipay_go':
?>
<form id="alipay" accept-charset="GBK" method="POST" action="https://shenghuo.alipay.com/send/payment/fill.htm">
	<?php if(isset($_GET['optEmail'])): ?><input type="hidden" value="<?php echo($_GET['optEmail']); ?>" name="optEmail"><?php endif; ?>
	<?php if(isset($_GET['payAmount'])): ?><input type="hidden" value="<?php echo($_GET['payAmount']); ?>" name="payAmount"><?php endif; ?>
	<?php if(isset($_GET['title'])): ?><input id="title" type="hidden" value="<?php echo($_GET['title']); ?>" name="title"><?php endif; ?>
	<?php if(isset($_GET['memo'])): ?><input name="memo" type="hidden" value="<?php echo($_GET['memo']); ?>" /><?php endif; ?>
</form>
<script type="text/javascript">
var postForm = document.getElementById('alipay');
postForm.method = "post" ;   
postForm.action = 'https://shenghuo.alipay.com/send/payment/fill.htm' ;  
postForm.submit();
</script>
<?php
			exit();
			break;
	}
}

if(!isset($package_info)){
	if(!isset($_GET['Package']))
		exit();

	require_once('system/common.inc.php');
	if(!isset($detect)){
		class_loader('Mobile_Detect');
		$detect = new Mobile_Detect;
	}
	$package_info = DB::fetch_first("SELECT `Package`, `Name`, `Tag`, `Level`, `Price`, `Purchase_Link` FROM `".DCRM_CON_PREFIX."Packages` WHERE `Package` = '".$_GET['Package']."' AND `Stat` = '1'");
	if(!$package_info)
		exit();
?>
<!DOCTYPE html SYSTEM "about:legacy-compat">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Activation · Cydia</title>
<link rel="stylesheet" type="text/css" href="./css/menes.min.css">
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="format-detection" content="telephone=no">
<script type="text/javascript" src="./js/fastclick.js"></script>
<script type="text/javascript" src="./js/cytyle.js"></script>
<script type="text/javascript" src="./js/menes.js"></script>
</head>
<body class="pinstripe">
<?php
}
$nowip = _ip2long(getIp());
$commercial_status = check_commercial_tag($package_info['Tag']);

if(isset($_GET['udid']))
	$udid_status = DB::fetch_first("SELECT `Packages`, `Level`, `IP` FROM `".DCRM_CON_PREFIX."UDID` WHERE `UDID` = '".$_GET['udid']."' LIMIT 1");
else
	$udid_status = DB::fetch_first("SELECT `Packages`, `Level`, `IP` FROM `".DCRM_CON_PREFIX."UDID` WHERE `IP` = '".$nowip."' LIMIT 1");
$purchased = false;
if(!empty($udid_status)){
	if(!empty($udid_status['Packages'])) {
		$udid_packages = TrimArray(explode(',', $udid_status['Packages']));
		if(in_array($package_info['Package'], $udid_packages, true))
			$purchased = true;
	} else {
		$udid_level = (int)$udid_status['Level'];
		$package_level = (int)$package_info['Level'];
		if($udid_level > $package_level)
			$purchased = true;
	}
}

if($purchased) {
	//$not_installed_button = 'null, null, null';
	$purchase_status = 'purchased';
	$fieldset_color = '#eefff0';
	$fieldset_icons = 'https://cache.saurik.com/crystal/256x256/actions/agt_action_success.png';
	if(empty($package_info['Price']))
		$purchase_status = 'candownload';
} else {
	$fieldset_color = '#ffc040';
	if(!empty($package_info['Price'])):
		//$not_installed_button = '"'.__('Purchase').'", "Highlighted", function(){buy();}';
		$purchase_status = 'notpurchase';
		$fieldset_icons = 'https://cache.saurik.com/crystal/64x64/apps/kwallet.png';
	else:
		//$not_installed_button = '"'.__('Recheck').'", "Highlighted", function(){reload_();}';
		$purchase_status = 'protection';
		$fieldset_icons = 'https://cache.saurik.com/crystal/64x64/apps/alert.png';
	endif;
}
?>
<panel>
	<fieldset style="background-color:<?php echo($fieldset_color); ?>">
		<a target="_popup" <?php if('notpurchase' == $purchase_status) echo('href="'.$package_info['Purchase_Link'].'"'); ?>>
			<img class="icon" src="<?php echo($fieldset_icons); ?>">
			<div>
				<div>
<?php 
switch($purchase_status){
	case 'notpurchase':
?>
					<label>
						<p><?php _e('Purchase Product'); ?></p>
					</label>
					<label class="price">
						<p><?php echo($package_info['Price']); ?></p>
					</label>
<?php
		break;
	case 'protection':
?>
					<label>
						<p><?php _e('Package Protected'); ?></p>
					</label>
<?php
		break;
	case 'purchased':
?>
					<label>
						<p><?php _e('Package Officially Purchased'); ?></p>
					</label>
<?php
		break;
	case 'candownload':
?>
					<label>
						<p><?php _e('Allowed To Download'); ?></p>
					</label>
<?php
		break;
}
?>
				</div>
			</div>
		</a>
	</fieldset>
</panel>