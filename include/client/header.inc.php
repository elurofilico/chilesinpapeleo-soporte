<?php 
$title=($cfg && is_object($cfg))?$cfg->getTitle():'osTicket :: Centro de Soporte';
header("Content-Type: text/html; charset=UTF-8\r\n");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title><?php  echo Format::htmlchars($title)?></title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
    body {
        padding-top: 60px;
        padding-bottom: 40px;
    }
    </style>
</head>
<body>
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></a>
                <a class="brand" href="index.php"><?php  echo Format::htmlchars($title)?></a>
                <div class="nav-collapse collapse">
                    <ul class="nav">
                        <li class="active"><a href="index.php">Inicio</a></li>
                        <li><a href="open.php">Nuevo ticket</a></li>
                        <?php                      
                        if($thisclient && is_object($thisclient) && $thisclient->isValid()) {?>
                        <li><a href="tickets.php">Mis tickets</a></li>
                        <li><a href="logout.php">Salir</a></li>
                        <?php }else {?>
                        <li><a href="tickets.php">Consulta de tickets</a></li>
                        <?php }?>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>
    </div>
    <div class="container">
