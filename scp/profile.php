<?php
/*********************************************************************
    profile.php

    Staff's profile handle

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/

require_once('staff.inc.php');
$msg='';
if($_POST && $_POST['id']!=$thisuser->getId()) { //Check dummy ID used on the form.
 $errors['err']='Error Interno. Acci&oacute;n Denegada';
}

if(!$errors && $_POST) { //Handle post
    switch(strtolower($_REQUEST['t'])):
    case 'pref':
        if(!is_numeric($_POST['auto_refresh_rate']))
            $errors['err']='Valor de actualizaci&oacute;n autom&aacute;tica no v&aacute;lido .';

        if(!$errors) {

            $sql='UPDATE '.STAFF_TABLE.' SET updated=NOW() '.
                ',daylight_saving='.db_input(isset($_POST['daylight_saving'])?1:0).
                ',max_page_size='.db_input($_POST['max_page_size']).
                ',auto_refresh_rate='.db_input($_POST['auto_refresh_rate']).
                ',timezone_offset='.db_input($_POST['timezone_offset']).
                ' WHERE staff_id='.db_input($thisuser->getId());

            if(db_query($sql) && db_affected_rows()){
                $thisuser->reload();
                $_SESSION['TZ_OFFSET']=$thisuser->getTZoffset();
                $_SESSION['daylight']=$thisuser->observeDaylight();
                $msg='Preferencia actualizada con &eacute;xito.';
            }else{
                $errors['err']='Error al actualizar preferencia.';
            }
        }
        break;
    case 'passwd':
        if(!$_POST['password'])
            $errors['password']='Se requiere la contrase&ntilde;a actual';        
        if(!$_POST['npassword'])
            $errors['npassword']='Se requiere la contrase&ntilde;a nueva';
        elseif(strlen($_POST['npassword'])<6)
             $errors['npassword']='La contrase&ntilde;a tiene que tener al menos 6 caracteres';
        if(!$_POST['vpassword'])
            $errors['vpassword']='Confirma la contrase&ntilde;a nueva';
        if(!$errors) {
            if(!$thisuser->check_passwd($_POST['password'])){
                $errors['password']='Se requiere una contrase&ntilde;a v&aacute;lida';
            }elseif(strcmp($_POST['npassword'],$_POST['vpassword'])){
                $errors['npassword']=$errors['vpassword']='La contrase&ntilde;a nueva no coincide.';
            }elseif(!strcasecmp($_POST['password'],$_POST['npassword'])){
                $errors['npassword']='La contrase&ntilde;a nueva es igual a la contrase&ntilde;a vieja';
            }
        }
        if(!$errors) {       
            $sql='UPDATE '.STAFF_TABLE.' SET updated=NOW() '.
                ',change_passwd=0, passwd='.db_input(MD5($_POST['npassword'])).
                ' WHERE staff_id='.db_input($thisuser->getId()); 
            if(db_query($sql) && db_affected_rows()){
                $msg='La contrase&ntilde;a se a cambiado con &eacute;xito';
            }else{
                $errors['err']='No se a podido completar el cambio de la contrase&ntilde;a. Error interno';
            }
        }
        break;
    case 'info':
        //Update profile info
        if(!$_POST['firstname']) {
            $errors['firstname']='Nombre requerido';
        }
        if(!$_POST['lastname']) {
            $errors['lastname']='Apellidos requerido';
        }
        if(!$_POST['email'] || !Validator::is_email($_POST['email'])) {
            $errors['email']='Se requiere cuenta de correo v&aacute;lida';
        }
        if($_POST['phone'] && !Validator::is_phone($_POST['phone'])) {
            $errors['phone']='Introduce un numero v&aacute;lido';
        }
        if($_POST['mobile'] && !Validator::is_phone($_POST['mobile'])) {
            $errors['mobile']='Introduce un numero v&aacute;lido';
        }

        if($_POST['phone_ext'] && !is_numeric($_POST['phone_ext'])) {
            $errors['phone_ext']='Ext. no v&aacute;lida';
        }

        if(!$errors) {

            $sql='UPDATE '.STAFF_TABLE.' SET updated=NOW() '.
                ',firstname='.db_input(Format::striptags($_POST['firstname'])).
                ',lastname='.db_input(Format::striptags($_POST['lastname'])).
                ',email='.db_input($_POST['email']).
                ',phone="'.db_input($_POST['phone'],false).'"'.
                ',phone_ext='.db_input($_POST['phone_ext']).
                ',mobile="'.db_input($_POST['mobile'],false).'"'.
                ',signature='.db_input(Format::striptags($_POST['signature'])).
                ' WHERE staff_id='.db_input($thisuser->getId());
            if(db_query($sql) && db_affected_rows()){
                $msg='Perfil actualizado con &eacute;xito';
            }else{
                $errors['err']='Se a producido un error, el perfil NO se a actualizado';
            }
        }else{
            $errors['err']='Se an producido errores. Intentalo de nuevo';
        }
        break;
    default:
        $errors['err']='Acci&oacute;n incorrecta';
    endswitch;
    //Reload user info if no errors.
    if(!$errors) {
        $thisuser->reload();
        $_SESSION['TZ_OFFSET']=$thisuser->getTZoffset();
        $_SESSION['daylight']=$thisuser->observeDaylight();
    }
}

//Tab and Nav options.
$nav->setTabActive('profile');
$nav->addSubMenu(array('desc'=>'Mi Perfil','href'=>'profile.php','iconclass'=>'user'));
$nav->addSubMenu(array('desc'=>'Ajustes','href'=>'profile.php?t=pref','iconclass'=>'userPref'));
$nav->addSubMenu(array('desc'=>'Cambiar Contrase&ntilde;a','href'=>'profile.php?t=passwd','iconclass'=>'userPasswd'));
//Warnings if any.
if($thisuser->onVacation()){
        $warn.='Bienvenido de nuevo! Tu estado es \'de vacaciones\' Avisa a tu administrador de que ya estas de vuelta.';
}

$rep=($errors && $_POST)?Format::input($_POST):Format::htmlchars($thisuser->getData());

// page logic
$inc='myprofile.inc.php';
switch(strtolower($_REQUEST['t'])) {
    case 'pref':
        $inc='mypref.inc.php';
        break;
    case 'passwd':
        $inc='changepasswd.inc.php';
        break;
    case 'info':
    default:
        $inc='myprofile.inc.php';
}
//Forced password Change.
if($thisuser->forcePasswdChange()){
    $errors['err']='cambia la contrase&ntilde;a para continuar.';
    $inc='changepasswd.inc.php';
}

//Render the page.
require_once(STAFFINC_DIR.'header.inc.php');
?>
<div>
    <?php if($errors['err']) {?>
        <p align="center" id="errormessage"><?php echo $errors['err']?></p>
    <?php }elseif($msg) {?>
        <p align="center" id="infomessage"><?php echo $msg?></p>
    <?php }elseif($warn) {?>
        <p align="center" id="warnmessage"><?php echo $warn?></p>
    <?php }?>
</div>
<div>
   <?php  require(STAFFINC_DIR.$inc);  ?>
</div>
<?php 
require_once(STAFFINC_DIR.'footer.inc.php');
?>
