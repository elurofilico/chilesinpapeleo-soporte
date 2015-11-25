<?php
/*********************************************************************
    admin.php

    Handles all admin related pages....everything admin!

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/
require('staff.inc.php');
//Make sure the user is admin type LOCKDOWN BABY!
if(!$thisuser or !$thisuser->isadmin()){
    header('Location: index.php');
    require('index.php'); // just in case!
    exit;
}


//Some security related warnings - bitch until fixed!!! :)
if(defined('THIS_VERSION') && strcasecmp($cfg->getVersion(),THIS_VERSION)) {
    $sysnotice=sprintf('El Script es la versi&oacute;n %s, mientras que la base de datos es la versi&oacute;n %s.',THIS_VERSION,$cfg->getVersion());
    if(file_exists('../setup/'))
        $sysnotice.=' Posiblemente causada por una actualizaci&oacute;n incompleta <a href="../setup/upgrade.php">Actualizar</a>.';
    $errors['err']=$sysnotice; 
}elseif(!$cfg->isHelpDeskOffline()) {

    if(file_exists('../setup/')){
        $sysnotice='Te recomendamos eliminar el archivo <strong>setup/install</strong> por razones de seguridad.';
    }else{

	    if(CONFIG_FILE && file_exists(CONFIG_FILE) && is_writable(CONFIG_FILE)) {
            //Confirm for real that the file is writable by group or world.
	    clearstatcache(); //clear the cache!
	    $perms = @fileperms(CONFIG_FILE);
	    if(($perms & 0x0002) || ($perms & 0x0010)) { 
		    #$sysnotice=sprintf('Deberias cambiarles los permisos de escritura al archivo (%s) cambiarlos a <i>chmod 644 %s</i>',
		    #basename(CONFIG_FILE),basename(CONFIG_FILE));
		    }
        }

    }
    if(!$sysnotice && ini_get('register_globals'))
        $sysnotice='Recomendamos desactivar registerGlobals si es posible.';
}

//Access checked out OK...lets do the do 
define('OSTADMININC',TRUE); //checked by admin include files
define('ADMINPAGE',TRUE);   //Used by the header to swap menus.

//Files we might need.
//TODO: Do on-demand require...save some mem.
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');
require_once(INCLUDE_DIR.'class.email.php');
require_once(INCLUDE_DIR.'class.mailfetch.php');

//Handle a POST.
if($_POST && $_REQUEST['t'] && !$errors):
    //print_r($_POST);
    //WELCOME TO THE HOUSE OF PAIN.
    $errors=array(); //do it anyways.

    switch(strtolower($_REQUEST['t'])):
        case 'pref':
            //Do the dirty work behind the scenes.
            if($cfg->updatePref($_POST,$errors)){
                $msg='Preferencias Actualizadas con &eacute;xito';
                $cfg->reload();
            }else{
                $errors['err']=$errors['err']?$errors['err']:'Error interno';
            }
            break;
        case 'attach':
            if($_POST['allow_attachments'] or $_POST['upload_dir']) {

                if($_POST['upload_dir']) //get the real path.
                    $_POST['upload_dir'] = realpath($_POST['upload_dir']);

                if(!$_POST['upload_dir'] or !is_writable($_POST['upload_dir'])) {
                    $errors['upload_dir']='El directorio debe ser v&aacute;lido y tener permisos de escritura';
                    if($_POST['allow_attachments'])
                        $errors['allow_attachments']='Directorio de adjuntos no v&aacute;lido';
                }elseif(!ini_get('file_uploads')) {
                    $errors['allow_attachments']='La directiva \'file_uploads\' est&aacute; desactivada en el php.ini';
                }
                
                if(!is_numeric($_POST['max_file_size']))
                    $errors['max_file_size']='El tama&ntilde;o m&aacute;ximo de archivo es requerido';

                if(!$_POST['allowed_filetypes'])
                    $errors['allowed_filetypes']='Tipos de archivos permitidos es requerido';
            }
            if(!$errors) {
               $sql= 'UPDATE '.CONFIG_TABLE.' SET allow_attachments='.db_input(isset($_POST['allow_attachments'])?1:0).
                    ',upload_dir='.db_input($_POST['upload_dir']). 
                    ',max_file_size='.db_input($_POST['max_file_size']).
                    ',allowed_filetypes='.db_input(strtolower(preg_replace("/\n\r|\r\n|\n|\r/", '',trim($_POST['allowed_filetypes'])))).
                    ',email_attachments='.db_input(isset($_POST['email_attachments'])?1:0).
                    ',allow_email_attachments='.db_input(isset($_POST['allow_email_attachments'])?1:0).
                    ',allow_online_attachments='.db_input(isset($_POST['allow_online_attachments'])?1:0).
                    ',allow_online_attachments_onlogin='.db_input(isset($_POST['allow_online_attachments_onlogin'])?1:0).
                    ' WHERE id='.$cfg->getId();
               //echo $sql;
               if(db_query($sql)) {
                   $cfg->reload();
                   $msg='Ajustes de archivos adjuntos actualizados';
               }else{
                    $errors['err']='Error de acualizaci&oacute;n';
               }
            }else {
                $errors['err']='Se produjo un error. Ver los mensajes de error abajo.';
                    
            }
            break;
        case 'api':
            include_once(INCLUDE_DIR.'class.api.php');
            switch(strtolower($_POST['do'])) {
                case 'add':
                    if(Api::add(trim($_POST['ip']),$errors))
                        $msg='Llave creada con &eacute;xito para'.Format::htmlchars($_POST['ip']);
                    elseif(!$errors['err'])
                        $errors['err']='Error al agregar la IP. Intentalo de nuevo.';
                    break;
                case 'update_phrase':
                    if(Api::setPassphrase(trim($_POST['phrase']),$errors))
                        $msg='Frase secreta actualizada con &eacute;xito.';
                    elseif(!$errors['err'])
                        $errors['err']='Error al actualizar frase secreta, intentalo de nuevo.';
                    break;
                case 'mass_process':
                    if(!$_POST['ids'] || !is_array($_POST['ids'])) {
                        $errors['err']='Debe seleccionar al menos una entrada para proceder';
                    }else{
                        $count=count($_POST['ids']);
                        $ids=implode(',',$_POST['ids']);
                        if($_POST['enable'] || $_POST['disable']) {
                            $resp=db_query('UPDATE '.API_KEY_TABLE.' SET isactive='.db_input($_POST['enable']?1:0).' WHERE id IN ('.$ids.')');
                                
                            if($resp && ($i=db_affected_rows())){
                                $msg="$i de $count llaves seleccionadas actualizadas";
                            }else {
                                $errors['err']='no se a podido eliminar la llave selecionada.';
                             }
                        }elseif($_POST['delete']){
                            $resp=db_query('DELETE FROM '.API_KEY_TABLE.'  WHERE id IN ('.$ids.')');
                            if($resp && ($i=db_affected_rows())){
                                $msg="$i de $count llaves seleccionadas eliminadas";
                            }else{
                                $errors['err']='No se a podido eliminar la llave seleccionada, intentalo de nuevo';
                            }
                        }else {
                            $errors['err']='Comando incorrecto';
                        }
                    }
                    break;
                default:
                    $errors['err']='Acci&oacute;n incorrecta '.$_POST['do'];
            }
            break;
        case 'banlist': //BanList.
            require_once(INCLUDE_DIR.'class.banlist.php');
            switch(strtolower($_POST['a'])) {
                case 'add':
                    if(!$_POST['email'] || !Validator::is_email($_POST['email']))
                        $errors['err']='Proporcione un Email valido.';
                    elseif(BanList::isbanned($_POST['email']))
                        $errors['err']='Este Email ya esta bloqueado';
                    else{
                        if(BanList::add($_POST['email'],$thisuser->getName()))
                            $msg='Email agregado a la lista negra';
                        else
                            $errors['err']='No se a podido agregar el Email a la lista negra.';
                    }
                    break;
                case 'remove':
                    if(!$_POST['ids'] || !is_array($_POST['ids'])) {
                        $errors['err']='Debe seleccionar al menos un Email';
                    }else{
                        //TODO: move mass remove to Banlist class when needed elsewhere...at the moment this is the only place.
                        $sql='DELETE FROM '.BANLIST_TABLE.' WHERE id IN ('.implode(',',$_POST['ids']).')';
                        if(db_query($sql) && ($num=db_affected_rows()))
                            $msg="$num de $count Emails selecionados quitados de la lista negra.";
                        else
                            $errors['err']='No se an podido quitar los Emails selecionados de la lista negra, intentalo de nuevo.';
                    }
                    break;
                default:
                    $errors['err']='Comando incorrecto';
            }
            break;
        case 'email':
            require_once(INCLUDE_DIR.'class.email.php');
            $do=strtolower($_POST['do']);
            switch($do){
                case 'update':
                    $email = new Email($_POST['email_id']);
                    if($email && $email->getId()) {
                        if($email->update($_POST,$errors))
                            $msg='La cuenta de correo electr&oacute;nico se a actualizado con &eacute;xito';
                        elseif(!$errors['err'])
                            $errors['err']='Error al actualizar la cuenta de correo electr&oacute;nico. Intentalo de nuevo';
                    }else{
                        $errors['err']='Error interno';
                    }
                    break;
                case 'create':
                    if(Email::create($_POST,$errors))
                        $msg='La cuenta de correo electr&oacute;nico se a agregado con &eacute;xito';
                    elseif(!$errors['err'])
                         $errors['err']='No se a podido agregar la cuenta de correo electr&oacute;nico. Error interno';
                    break;
                case 'mass_process':
                    if(!$_POST['ids'] || !is_array($_POST['ids'])) {
                        $errors['err']='Tienes que elegir al menos una cuenta de correo electr&oacute;nico para proceder';
                    }else{
                        $count=count($_POST['ids']);
                        $ids=implode(',',$_POST['ids']);
                        $sql='SELECT count(dept_id) FROM '.DEPT_TABLE.' WHERE email_id IN ('.$ids.') OR autoresp_email_id IN ('.$ids.')';
                        list($depts)=db_fetch_row(db_query($sql));
                        if($depts>0){
                            $errors['err']='Una o m&aacute;s de las cuentas de correo electr&oacute;nico seleccionadas est&aacute; siendo utilizada por un departamento. Deshacer primero la asociaci&oacute;n';    
                        }elseif($_POST['delete']){
                            $i=0;
                            foreach($_POST['ids'] as $k=>$v) {
                                if(Email::deleteEmail($v)) $i++;
                            }
                            if($i>0){
                                $msg="$i de $count cuentas de correo electr&oacute;nico selecionadas eliminadas";
                            }else{
                                $errors['err']='No se an podido eliminar las cuentas de correo electr&oacute;nico selecionadas.';
                            }
                        }else{
                            $errors['err']='Comando incorrecto';
                        }
                    }
                    break;
                default:
                    $errors['err']='Acci&oacute;n de &aacute;rea de ayuda incorrecta';
            }
            break;
        case 'templates':
           include_once(INCLUDE_DIR.'class.msgtpl.php'); 
            $do=strtolower($_POST['do']);
            switch($do){
                case 'add':
                case 'create':
                    if(($tid=Template::create($_POST,$errors))){
                        $msg='Plantilla creada con &eacute;xito';
                    }elseif(!$errors['err']){
                        $errors['err']='Error al crear la plantilla. Intentalo de nuevo';
                    }
                    break;
                case 'update':
                    $template=null;
                    if($_POST['id'] && is_numeric($_POST['id'])) {
                        $template= new Template($_POST['id']);
                        if(!$template || !$template->getId()) {
                            $template=null;
                            $errors['err']='Plantilla desconocida'.$id;
                  
                        }elseif($template->update($_POST,$errors)){
                            $msg='Plantilla actualizada con &eacute;xito';
                        }elseif(!$errors['err']){
                            $errors['err']='Error al actualizar la plantilla - Intentalo de nuevo';
                        }
                    }
                    break;
                case 'mass_process':
                    if(!$_POST['ids'] || !is_array($_POST['ids'])) {
                        $errors['err']='Tienes que elegir al menos una plantilla';
                    }elseif(in_array($cfg->getDefaultTemplateId(),$_POST['ids'])){
                        $errors['err']='No puedes eliminar la plantilla por defecto';
                    }else{
                        $count=count($_POST['ids']);
                        $ids=implode(',',$_POST['ids']);
                        $sql='SELECT count(dept_id) FROM '.DEPT_TABLE.' WHERE tpl_id IN ('.$ids.')';
                        list($tpl)=db_fetch_row(db_query($sql));
                        if($tpl>0){
                            $errors['err']='Una o m&aacute;s de las plantillas seleccionadas est&aacute; siendo utilizada por un departamento. Deshacer primero la asociaci&oacute;n.';
                        }elseif($_POST['delete']){
                            $sql='DELETE FROM '.EMAIL_TEMPLATE_TABLE.' WHERE tpl_id IN ('.$ids.') AND tpl_id!='.db_input($cfg->getDefaultTemplateId());
                            if(($result=db_query($sql)) && ($i=db_affected_rows()))
                                $msg="$i de $count las plantillas selecionadas eliminadas";
                            else
                                $errors['err']='No se an podido eliminar las plantillas selecionadas. Intentalo de nuevo';
                        }else{
                            $errors['err']='Comando incorrecto';
                        }
                    }
                    break;
                default:
                    $errors['err']='Acci&oacute;n incorrecta';
                    //print_r($_POST);
            }
            break;
    case 'topics':
        require_once(INCLUDE_DIR.'class.topic.php');
        $do=strtolower($_POST['do']);
        switch($do){
            case 'update':
                $topic = new Topic($_POST['topic_id']);
                if($topic && $topic->getId()) {
                    if($topic->update($_POST,$errors))
                        $msg='&Aacute;rea de ayuda actualizada con &eacute;xito';
                    elseif(!$errors['err'])
                        $errors['err']='Error al actualizar el &aacute;rea de ayuda';
                }else{
                    $errors['err']='Error interno';
                }
                break;
            case 'create':
                if(Topic::create($_POST,$errors))
                    $msg='&Aacute;rea de ayuda creada con &eacute;xito';
                elseif(!$errors['err'])
                    $errors['err']='No se a podido crear el &aacute;rea de ayuda. Error interno';
                break;
            case 'mass_process':
                if(!$_POST['tids'] || !is_array($_POST['tids'])) {
                    $errors['err']='Tienes que elegir al menos un &aacute;rea de ayuda.';
                }else{
                    $count=count($_POST['tids']);
                    $ids=implode(',',$_POST['tids']);
                    if($_POST['enable']){
                        $sql='UPDATE '.TOPIC_TABLE.' SET isactive=1, updated=NOW() WHERE topic_id IN ('.$ids.') AND isactive=0 ';
                        if(db_query($sql) && ($num=db_affected_rows()))
                            $msg="$num de $count servicios habilitados";
                        else
                            $errors['err']='No se a podido completar la acci&oacute;n.';
                    }elseif($_POST['disable']){
                        $sql='UPDATE '.TOPIC_TABLE.' SET isactive=0, updated=NOW() WHERE topic_id IN ('.$ids.') AND isactive=1 ';
                        if(db_query($sql) && ($num=db_affected_rows()))
                            $msg="$num de $count &aacute;reas de ayuda seleccionadas deshabilitadas";
                        else
                            $errors['err']='No se han podido deshabiltar las &aacute;reas de ayuda seleccionadas';
                    }elseif($_POST['delete']){
                        $sql='DELETE FROM '.TOPIC_TABLE.' WHERE topic_id IN ('.$ids.')';        
                        if(db_query($sql) && ($num=db_affected_rows()))
                            $msg="$num de $count &aacute;reas de ayuda seleccionadas eliminadas";
                        else
                            $errors['err']='No se han podido eliminar las &aacute;reas de ayuda seleccionadas';
                    }
                }
                break;
            default:
                $errors['err']='Acci&oacute;n de &aacute;rea de ayuda incorrecta';
        }
        break;
    case 'groups':
        include_once(INCLUDE_DIR.'class.group.php');
        $do=strtolower($_POST['do']);
        switch($do){
            case 'update':
                if(Group::update($_POST['group_id'],$_POST,$errors)){
                    $msg='Grupo'.Format::htmlchars($_POST['group_name']).' actualizado con &eacute;xito';
                }elseif(!$errors['err']) {
                    $errors['err']='A ocurrido un error. Intentalo de nuevo';
                }
                break;
            case 'create':
                if(($gID=Group::create($_POST,$errors))){
                    $msg='Grupo '.Format::htmlchars($_POST['group_name']).' creado con &eacute;xito';
                }elseif(!$errors['err']) {
                    $errors['err']='A ocurrido un error. Intentalo de nuevo.';
                }
                break;
            default:
                //ok..at this point..look WMA.
                if($_POST['grps'] && is_array($_POST['grps'])) {
                    $ids=implode(',',$_POST['grps']);
                    $selected=count($_POST['grps']);
                    if(isset($_POST['activate_grps'])) {
                        $sql='UPDATE '.GROUP_TABLE.' SET group_enabled=1,updated=NOW() WHERE group_enabled=0 AND group_id IN('.$ids.')';
                        db_query($sql);
                        $msg=db_affected_rows()." de  $selected grupos selecionados habiltados";
                    }elseif(in_array($thisuser->getDeptId(),$_POST['grps'])) {
                          $errors['err']="&iquest;Tratando de 'Desactivar'o 'Borrar' tu grupo? No tiene ning&uacute;n sentido";
                    }elseif(isset($_POST['disable_grps'])) {
                        $sql='UPDATE '.GROUP_TABLE.' SET group_enabled=0, updated=NOW() WHERE group_enabled=1 AND group_id IN('.$ids.')';
                        db_query($sql);
                        $msg=db_affected_rows()." de  $selected grupos selecionados deshabilitados"; 
                    }elseif(isset($_POST['delete_grps'])) {
                        $res=db_query('SELECT staff_id FROM '.STAFF_TABLE.' WHERE group_id IN('.$ids.')');
                        if(!$res || db_num_rows($res)) { //fail if any of the selected groups has users.
                            $errors['err']='Uno o m&aacute;s de los grupos seleccionadas tiene usuarios. Solamente se pueden eliminar los grupos que est&aacute;n vac&iacute;os.';
                        }else{
                            db_query('DELETE FROM '.GROUP_TABLE.' WHERE group_id IN('.$ids.')');    
                            $msg=db_affected_rows()." de  $selected grupos selecionados eliminados.";
                        }
                    }else{
                         $errors['err']='Comando incorrecto';
                    }
                    
                }else{
                    $errors['err']='No hay grupos seleccionados.';
                }
        }
    break;
    case 'staff':
        include_once(INCLUDE_DIR.'class.staff.php');
        $do=strtolower($_POST['do']);
        switch($do){
            case 'update':
                $staff = new Staff($_POST['staff_id']);
                if($staff && $staff->getId()) {
                    if($staff->update($_POST,$errors))
                        $msg='Perfil de miembro actualizado con &eacute;xito';
                    elseif(!$errors['err'])
                        $errors['err']='Error al actualizar miembro';
                }else{
                    $errors['err']='Error interno';
                }
                break;
            case 'create':
                if(($uID=Staff::create($_POST,$errors)))
                    $msg=Format::htmlchars($_POST['firstname'].' '.$_POST['lastname']).'Agregado con &eacute;xito ';
                elseif(!$errors['err'])
                    $errors['err']='No se a podido agregar al miembro. Intentalo de nuevo';
                break;
            case 'mass_process':
                //ok..at this point..look WMA.
                if($_POST['uids'] && is_array($_POST['uids'])) {
                    $ids=implode(',',$_POST['uids']);
                    $selected=count($_POST['uids']);
                    if(isset($_POST['enable'])) {
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=1,updated=NOW() WHERE isactive=0 AND staff_id IN('.$ids.')';
                        db_query($sql);
                        $msg=db_affected_rows()." de  $selected miembros seleccionados habilitados";
                    
                    }elseif(in_array($thisuser->getId(),$_POST['uids'])) {
                        //sucker...watch what you are doing...why don't you just DROP the DB?
                        $errors['err']='Venga ya... No te puedes eliminar ni bloquear tu mismo';  
                    }elseif(isset($_POST['disable'])) {
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=0, updated=NOW() '.
                            ' WHERE isactive=1 AND staff_id IN('.$ids.') AND staff_id!='.$thisuser->getId();
                        db_query($sql);
                        $msg=db_affected_rows()." de  $selected miembros seleccionado bloqueado.";
                        //Release tickets assigned to the user?? NO? could be a temp thing 
                        // May be auto-release if not logged in for X days? 
                    }elseif(isset($_POST['delete'])) {
                        db_query('DELETE FROM '.STAFF_TABLE.' WHERE staff_id IN('.$ids.') AND staff_id!='.$thisuser->getId());
                        $msg=db_affected_rows()." de  $selected miembros seleccionado eliminados";
                        //Demote the user 
                        db_query('UPDATE '.DEPT_TABLE.' SET manager_id=0 WHERE manager_id IN('.$ids.') ');
                        db_query('UPDATE '.TICKET_TABLE.' SET staff_id=0 WHERE staff_id IN('.$ids.') ');
                    }else{
                        $errors['err']='Comando incorrecto';
                    }
                }else{
                    $errors['err']='No se an seleccionado miembros.';
                }
            break;
            default:
                $errors['err']='Comando incorrecto';
        }
    break;
    case 'dept':
        include_once(INCLUDE_DIR.'class.dept.php');
        $do=strtolower($_POST['do']);
        switch($do){
            case 'update':
                $dept = new Dept($_POST['dept_id']);
                if($dept && $dept->getId()) {
                    if($dept->update($_POST,$errors))
                        $msg='Departamento actualizado con &eacute;xito';
                    elseif(!$errors['err'])
                        $errors['err']='Error al actualizar departamento';
                }else{
                    $errors['err']='Error interno';
                }
                break;
            case 'create':
                if(($deptID=Dept::create($_POST,$errors)))
                    $msg=Format::htmlchars($_POST['dept_name']).' agregado con &eacute;xito';
                elseif(!$errors['err'])
                    $errors['err']='No se a podido agregar departamento. Error interno';
                break;
            case 'mass_process':
                if(!$_POST['ids'] || !is_array($_POST['ids'])) {
                    $errors['err']='Tienes que elegir al menos un departamento';
                }elseif(!$_POST['public'] && in_array($cfg->getDefaultDeptId(),$_POST['ids'])) {
                    $errors['err']='No se puede deshabilitar o borrar un departamento predeterminado. Quite el Departamento de ser predeterminado y vuelva a intentarlo.';
                }else{
                    $count=count($_POST['ids']);
                    $ids=implode(',',$_POST['ids']);
                    if($_POST['public']){
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=1 WHERE dept_id IN ('.$ids.')';  
                        if(db_query($sql) && ($num=db_affected_rows()))
                            $warn="$num de $count departamentos seleccionados echo publicos.";
                        else
                            $errors['err']='No se an podido hacer publico los departamentos.';
                    }elseif($_POST['private']){
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=0 WHERE dept_id IN ('.$ids.') AND dept_id!='.db_input($cfg->getDefaultDeptId());
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            $warn="$num de $count departamentos seleccionados seran privados.";
                        }else
                            $errors['err']='No se pueden hacer privados los departamento seleccionados, puede que ya sean privados';
                            
                    }elseif($_POST['delete']){
                        //Deny all deletes if one of the selections has members in it.
                        $sql='SELECT count(staff_id) FROM '.STAFF_TABLE.' WHERE dept_id IN ('.$ids.')';
                        list($members)=db_fetch_row(db_query($sql));
                        $sql='SELECT count(topic_id) FROM '.TOPIC_TABLE.' WHERE dept_id IN ('.$ids.')';
                        list($topics)=db_fetch_row(db_query($sql));
                        if($members){
                            $errors['err']='No se puede eliminar un departamento con miembros, elimine los miembros primero.';
                        }elseif($topic){
                             $errors['err']='No se puede eliminar un departamento que tenga asociado un &aacute;rea de ayuda, elimine la asociaci&oacute;n primero.';
                        }else{
                            //We have to deal with individual selection because of associated tickets and users.
                            $i=0;
                            foreach($_POST['ids'] as $k=>$v) {
                                if($v==$cfg->getDefaultDeptId()) continue; //Don't delete default dept. Triple checking!!!!!
                                if(Dept::delete($v)) $i++;
                            }
                            if($i>0){
                                $warn="$i de $count departamentos seleccionados eliminados.";
                            }else{
                                $errors['err']='No se pueden eliminar los departamentos seleccionados.';
                            }
                        }
                    }
                }
            break;            
            default:
                $errors['err']='Acci&oacute;n de departamento incorrecta';
        }
    break;
    default:
        $errors['err']='Comando incorrecto';
    endswitch;
endif;

//================ADMIN MAIN PAGE LOGIC==========================
//Process requested tab.
$thistab=strtolower($_REQUEST['t']?$_REQUEST['t']:'dashboard');
$inc=$page=''; //No outside crap please!
$submenu=array();
switch($thistab){
    //Preferences & settings
    case 'settings':
    case 'pref':
    case 'attach':
    case 'api':
        $nav->setTabActive('settings');
        $nav->addSubMenu(array('desc'=>'Preferencias','href'=>'admin.php?t=pref','iconclass'=>'preferences'));
        $nav->addSubMenu(array('desc'=>'Adjuntos','href'=>'admin.php?t=attach','iconclass'=>'attachment'));
        $nav->addSubMenu(array('desc'=>'API','href'=>'admin.php?t=api','iconclass'=>'api'));
        switch($thistab):
        case 'settings':            
        case 'pref':        
            $page='preference.inc.php';
            break;
        case 'attach':
            $page='attachment.inc.php';
            break;
        case 'api':
            $page='api.inc.php';
        endswitch;
        break;   
    case 'dashboard':
    case 'syslog':
        $nav->setTabActive('dashboard');
        $nav->addSubMenu(array('desc'=>'Registros del Sistema','href'=>'admin.php?t=syslog','iconclass'=>'syslogs'));
        $page='syslogs.inc.php';
        break;
    case 'email':
    case 'templates':
    case 'banlist':
        $nav->setTabActive('emails');
        $nav->addSubMenu(array('desc'=>'Direcciones de Correo','href'=>'admin.php?t=email','iconclass'=>'emailSettings'));
        $nav->addSubMenu(array('desc'=>'A&ntilde;adir Correo','href'=>'admin.php?t=email&a=new','iconclass'=>'newEmail'));
        $nav->addSubMenu(array('desc'=>'Plantillas','href'=>'admin.php?t=templates','title'=>'Plantillas de autorespuestas','iconclass'=>'emailTemplates')); 
        $nav->addSubMenu(array('desc'=>'Lista Negra','href'=>'admin.php?t=banlist','title'=>'Cuentas de correo restringidas','iconclass'=>'banList')); 
        switch(strtolower($_REQUEST['t'])){
            case 'templates':
                $page='templates.inc.php';
                $template=null;
                if(($id=$_REQUEST['id']?$_REQUEST['id']:$_POST['email_id']) && is_numeric($id)) {
                    include_once(INCLUDE_DIR.'class.msgtpl.php');
                    $template= new Template($id);
                    if(!$template || !$template->getId()) {
                        $template=null;
                        $errors['err']='No se pudo obtener informaci&oacute;n sobre la plantilla. ID# '.$id;
                    }else {
                        $page='template.inc.php';
                    }
                }
                break;
            case 'banlist':
                $page='banlist.inc.php';
                break;
            case 'email':
            default:
                include_once(INCLUDE_DIR.'class.email.php');
                $email=null;
                if(($id=$_REQUEST['id']?$_REQUEST['id']:$_POST['email_id']) && is_numeric($id)) {
                    $email= new Email($id,false);
                    if(!$email->load()) {
                        $email=null;
                        $errors['err']='No se pudo obtener informaci&oacute;n sobre el correo ID#'.$id;
                    }
                }
                $page=($email or ($_REQUEST['a']=='new' && !$emailID))?'email.inc.php':'emails.inc.php';
        }
        break;
    case 'topics':
        require_once(INCLUDE_DIR.'class.topic.php');
        $topic=null;
        $nav->setTabActive('topics');
        $nav->addSubMenu(array('desc'=>'&Aacute;rea de Ayuda','href'=>'admin.php?t=topics','iconclass'=>'helpTopics'));
        $nav->addSubMenu(array('desc'=>'Agregar Nueva &Aacute;rea de Ayuda','href'=>'admin.php?t=topics&a=new','iconclass'=>'newHelpTopic'));
        if(($id=$_REQUEST['id']?$_REQUEST['id']:$_POST['topic_id']) && is_numeric($id)) {
            $topic= new Topic($id);
            if(!$topic->load() && $topic->getId()==$id) {
                $topic=null;
                $errors['err']='No se pudo obtener informaci&oacute;n sobre el &Aacute;rea de Ayuda ID#'.$id;
            }
        }
        $page=($topic or ($_REQUEST['a']=='new' && !$topicID))?'topic.inc.php':'helptopics.inc.php';
        break;
    //Staff (users, groups and teams)
    case 'grp':
    case 'groups':
    case 'staff':
        $group=null;
        //Tab and Nav options.
        $nav->setTabActive('staff');
        $nav->addSubMenu(array('desc'=>'Miembros del Staff','href'=>'admin.php?t=staff','iconclass'=>'users'));
        $nav->addSubMenu(array('desc'=>'Nuevo Miembro','href'=>'admin.php?t=staff&a=new','iconclass'=>'newuser'));
        $nav->addSubMenu(array('desc'=>'Grupos','href'=>'admin.php?t=groups','iconclass'=>'groups'));
        $nav->addSubMenu(array('desc'=>'Nuevo Grupo','href'=>'admin.php?t=groups&a=new','iconclass'=>'newgroup'));
        $page='';
        switch($thistab){
            case 'grp':
            case 'groups':
                if(($id=$_REQUEST['id']?$_REQUEST['id']:$_POST['group_id']) && is_numeric($id)) {
                    $res=db_query('SELECT * FROM '.GROUP_TABLE.' WHERE group_id='.db_input($id));
                    if(!$res or !db_num_rows($res) or !($group=db_fetch_array($res)))
                        $errors['err']='No se pudo obtener informaci&oacute;n sobre el Grupo ID#'.$id;
                }
                $page=($group or ($_REQUEST['a']=='new' && !$gID))?'group.inc.php':'groups.inc.php';
                break;
            case 'staff':
                $page='staffmembers.inc.php';
                if(($id=$_REQUEST['id']?$_REQUEST['id']:$_POST['staff_id']) && is_numeric($id)) {
                    $staff = new Staff($id);
                    if(!$staff || !is_object($staff) || $staff->getId()!=$id) {
                        $staff=null;
                        $errors['err']='No se pudo obtener informaci&oacute;n sobre el Miembro ID#'.$id;
                    }
                }
                $page=($staff or ($_REQUEST['a']=='new' && !$uID))?'staff.inc.php':'staffmembers.inc.php';
                break;
            default:
                $page='staffmembers.inc.php';
        }
        break;
    //Departments
    case 'dept': //lazy
    case 'depts':
        $dept=null;
        if(($id=$_REQUEST['id']?$_REQUEST['id']:$_POST['dept_id']) && is_numeric($id)) {
            $dept= new Dept($id);
            if(!$dept || !$dept->getId()) {
                $dept=null;
                $errors['err']='No se pudo obtener informaci&oacute;n sobre el Departamento ID#'.$id;
            }
        }
        $page=($dept or ($_REQUEST['a']=='new' && !$deptID))?'dept.inc.php':'depts.inc.php';
        $nav->setTabActive('depts');
        $nav->addSubMenu(array('desc'=>'Departamentos','href'=>'admin.php?t=depts','iconclass'=>'departments'));
        $nav->addSubMenu(array('desc'=>'Nuevo Departamento','href'=>'admin.php?t=depts&a=new','iconclass'=>'newDepartment'));
        break;
    // (default)
    default:
        $page='pref.inc.php';
}
//========================= END ADMIN PAGE LOGIC ==============================//

$inc=($page)?STAFFINC_DIR.$page:'';
//Now lets render the page...
require(STAFFINC_DIR.'header.inc.php');
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
<table width="100%" border="0" cellspacing="0" cellpadding="1">
    <tr><td>
        <div style="margin:0 5px 5px 5px;">
        <?php 
            if($inc && file_exists($inc)){
                require($inc);
            }else{
                ?>
                <p align="center">
                    <font class="error">Problemas al cargar la p&aacute;gina de administraci&oacute;n (<?php echo Format::htmlchars($thistab)?>)</font>
                    <br>Posiblemte tenga el acceso denegado, si crees que esto es un error, pida asistencia t&eacute;cnica.
                </p>
            <?php }?>
        </div>
    </td></tr>
</table>
<?php 
include_once(STAFFINC_DIR.'footer.inc.php');
?>
