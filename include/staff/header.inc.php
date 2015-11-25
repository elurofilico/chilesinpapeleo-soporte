<?php  if(!defined('OSTSCPINC') || !is_object($thisuser) || !$thisuser->isStaff() || !is_object($nav)) die('Acceso Denegado'); ?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<?php 
if(defined('AUTO_REFRESH') && is_numeric(AUTO_REFRESH_RATE) && AUTO_REFRESH_RATE>0){ //Refresh rate
echo '<meta http-equiv="refresh" content="'.AUTO_REFRESH_RATE.'" />';
}
?>
<title>osTicket :: Panel de Control</title>
<link rel="stylesheet" href="css/main.css" media="screen">
<link rel="stylesheet" href="css/style.css" media="screen">
<link rel="stylesheet" href="css/tabs.css" type="text/css">
<link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/scp.js"></script>
<script type="text/javascript" src="js/tabber.js"></script>
<script type="text/javascript" src="js/calendar.js"></script>
<script type="text/javascript" src="js/bsn.AutoSuggest_2.1.3.js" charset="utf-8"></script>
<?php 
if($cfg && $cfg->getLockTime()) { //autoLocking enabled.?>
<script type="text/javascript" src="js/autolock.js" charset="utf-8"></script>
<?php }?>
</head>
<body>
<?php 
if($sysnotice){?>
<div id="system_notice"><?php  echo $sysnotice; ?></div>
<?php  
}?>
<div id="container">
    <div id="header">
        <a id="logo" href="index.php" title="osTicket"><img src="images/ostlogo.jpg" width="188" height="72" alt="osTicket"></a>
        <p id="info">Bienvenido, <strong><?php echo $thisuser->getUsername()?></strong> 
           <?php 
            if($thisuser->isAdmin() && !defined('ADMINPAGE')) { ?>
            | <a href="admin.php">Panel de Configuraci&oacute;n</a> 
            <?php }else{?>
            | <a href="index.php">Panel de Administraci&oacute;n</a>
            <?php }?>
            | <a href="profile.php?t=pref">Mis Preferencias</a> | <a href="logout.php">Salir</a></p>
    </div>
    <div id="nav">
        <ul id="main_nav" <?php echo !defined('ADMINPAGE')?'class="dist"':''?>>
            <?php 
            if(($tabs=$nav->getTabs()) && is_array($tabs)){
             foreach($tabs as $tab) { ?>
                <li><a <?php echo $tab['active']?'class="active"':''?> href="<?php echo $tab['href']?>" title="<?php echo $tab['title']?>"><?php echo $tab['desc']?></a></li>
            <?php }
            }else{ //?? ?>
                <li><a href="profile.php" title="Mi Perfil">Mi Perfil</a></li>
            <?php }?>
        </ul>
        <ul id="sub_nav">
            <?php 
            if(($subnav=$nav->getSubMenu()) && is_array($subnav)){
              foreach($subnav as $item) { ?>
                <li><a class="<?php echo $item['iconclass']?>" href="<?php echo $item['href']?>" title="<?php echo $item['title']?>"><?php echo $item['desc']?></a></li>
              <?php }
            }?>
        </ul>
    </div>
    <div class="clear"></div>
    <div id="content" width="100%">

