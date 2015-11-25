<?php
/*************************************************************************
    tickets.php
    
    Handles all tickets related actions.
 
    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');
require_once(INCLUDE_DIR.'class.banlist.php');


$page='';
$ticket=null; //clean start.
//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if(!$errors && ($id=$_REQUEST['id']?$_REQUEST['id']:$_POST['ticket_id']) && is_numeric($id)) {
    $deptID=0;
    $ticket= new Ticket($id);
    if(!$ticket or !$ticket->getDeptId())
        $errors['err']='ID de Ticket Desconocido'.$id; //Sucker...invalid id
    elseif(!$thisuser->isAdmin()  && (!$thisuser->canAccessDept($ticket->getDeptId()) && $thisuser->getId()!=$ticket->getStaffId()))
        $errors['err']='Acceso Denegado. Si crees que esto es un Error, ponte en contacto con el Administrador';

    if(!$errors && $ticket->getId()==$id)
        $page='viewticket.inc.php'; //Default - view

    if(!$errors && $_REQUEST['a']=='edit') { //If it's an edit  check permission.
        if($thisuser->canEditTickets() || ($thisuser->isManager() && $ticket->getDeptId()==$thisuser->getDeptId()))
            $page='editticket.inc.php';
        else
            $errors['err']='Acceso Denegado. No tienes permiso para ver este Ticket. Si crees que esto es un Error, ponte en contacto con el Administrador';
    }

}elseif($_REQUEST['a']=='open') {
    //TODO: Check perm here..
    $page='newticket.inc.php';
}
//At this stage we know the access status. we can process the post.
if($_POST && !$errors):

    if($ticket && $ticket->getId()) {
        //More tea please.
        $errors=array();
        $lock=$ticket->getLock(); //Ticket lock if any
        $statusKeys=array('open'=>'Abierto','Reopen'=>'Abierto','Close'=>'Cerrado');
        switch(strtolower($_POST['a'])):
        case 'reply':
            $fields=array();
            $fields['msg_id']       = array('type'=>'int',  'required'=>1, 'error'=>'Falta ID de Mensaje');
            $fields['response']     = array('type'=>'text', 'required'=>1, 'error'=>'Respuesta requerida');
            $params = new Validator($fields);
            if(!$params->validate($_POST)){
                $errors=array_merge($errors,$params->errors());
            }
            //Use locks to avoid double replies
            if($lock && $lock->getStaffId()!=$thisuser->getId())
                $errors['err']='Acci&oacute;n Denegada. El Ticket a sido bloqueado por otro';

            //Check attachments restrictions.
            if($_FILES['attachment'] && $_FILES['attachment']['size']) {
                if(!$_FILES['attachment']['name'] || !$_FILES['attachment']['tmp_name'])
                    $errors['attachment']='Adjunto no V&aacute;lido';
                elseif(!$cfg->canUploadFiles()) //TODO: saved vs emailed attachments...admin config??
                    $errors['attachment']='Directorio de adjuntos invalido, contacta al Administrador.';
                elseif(!$cfg->canUploadFileType($_FILES['attachment']['name']))
                    $errors['attachment']='Extensi&oacute;n de archivo invalida';
            }

            //Make sure the email is not banned
            if(!$errors && BanList::isbanned($ticket->getEmail()))
                $errors['err']='La cuenta de correo esta en la lista negra. Quitar de la lista negra antes de responder.';

            //If no error...do the do.
            if(!$errors && ($respId=$ticket->postResponse($_POST['msg_id'],$_POST['response'],$_POST['signature'],$_FILES['attachment']))){
                $msg='Respuesta enviada con &eacute;xito';
                //Set status if any.
                $wasOpen=$ticket->isOpen();
                if(isset($_POST['ticket_status']) && $_POST['ticket_status']) {
                   if($ticket->setStatus($_POST['ticket_status']) && $ticket->reload()) {
                       $note=sprintf('%s %s Al enviar ticket de ',$thisuser->getName(),$ticket->isOpen()?'reabrir':'cerrar');
                       $ticket->logActivity('El estado del Ticket a cambiado a '.($ticket->isOpen()?'Abierto':'Cerrado'),$note);
                   }
                }
                //Finally upload attachment if any
                if($_FILES['attachment'] && $_FILES['attachment']['size']){
                    $ticket->uploadAttachment($_FILES['attachment'],$respId,'R');
                }
                $ticket->reload();
                //Mark the ticket answered if OPEN.
                if($ticket->isopen()){
                    $ticket->markAnswered();
                }elseif($wasOpen) { //Closed on response???
                    $page=$ticket=null; //Going back to main listing.
                }
            }elseif(!$errors['err']){
                $errors['err']='No se a podido enviar la respuesta.';
            }
            break;
        case 'transfer':
            $fields=array();
            $fields['dept_id']      = array('type'=>'int',  'required'=>1, 'error'=>'Selecciona Departamento');
            $fields['message']      = array('type'=>'text',  'required'=>1, 'error'=>'Nota/Mensaje requerido');
            $params = new Validator($fields);
            if(!$params->validate($_POST)){
                $errors=array_merge($errors,$params->errors());
            }

            if(!$errors && ($_POST['dept_id']==$ticket->getDeptId()))
                $errors['dept_id']='El Ticket ya se encuentra en este departamento.';
       
            if(!$errors && !$thisuser->canTransferTickets())
                $errors['err']='Acci&oacute;n Denegada. No tienes permiso para transferir este Ticket.';
            
            if(!$errors && $ticket->transfer($_POST['dept_id'])){
                 $olddept=$ticket->getDeptName();
                 $ticket->reload(); //dept manager changed!
                //Send out alerts?? - for now yes....part of internal note!
                $title='Departamento cambiado de '.$olddept.' a '.$ticket->getDeptName();
                $ticket->postNote($title,$_POST['message']);
                $msg='Ticket transferido con &eacute;xito al departameto '.$ticket->getDeptName().'';
                if(!$thisuser->canAccessDept($_POST['dept_id']) && $ticket->getStaffId()!=$thisuser->getId()) { //Check access.
                    //Staff doesn't have access to the new department.
                    $page='tickets.inc.php';
                    $ticket=null;
                }
            }elseif(!$errors['err']){
                $errors['err']='No se a podido completar la transferencia.';
            }
            break;
        case 'assign':
            $fields=array();
            $fields['staffId']          = array('type'=>'int',  'required'=>1, 'error'=>'Selecciona destinario');
            $fields['assign_message']   = array('type'=>'text',  'required'=>1, 'error'=>'Mensaje requerido');
            $params = new Validator($fields);
            if(!$params->validate($_POST)){
                $errors=array_merge($errors,$params->errors());
            }
            if(!$errors && $ticket->isAssigned()){
                if($_POST['staffId']==$ticket->getStaffId())
                    $errors['staffId']='Ticket ya esta asignado a este destinario.';
            }
            //if already assigned.
            if(!$errors && $ticket->isAssigned()) { //Re assigning.
                //Already assigned to the user?
                if($_POST['staffId']==$ticket->getStaffId())
                    $errors['staffId']='Ticket ya esta asignado a este destinario.';
                //Admin, Dept manager (any) or current assigneee ONLY can reassign
                if(!$thisuser->isadmin()  && !$thisuser->isManager() && $thisuser->getId()!=$ticket->getStaffId())
                    $errors['err']='Ticket ya esta asignado. No tienes permiso para reasignar un Ticket asignado.';
            }
            if(!$errors && $ticket->assignStaff($_POST['staffId'],$_POST['assign_message'])){
                $staff=$ticket->getStaff();
		$msg='Ticket asignado a '.($staff?$staff->getName():'staff').'';
                //Remove all the logs and go back to index page.
                TicketLock::removeStaffLocks($thisuser->getId(),$ticket->getId());
                $page='tickets.inc.php';
                $ticket=null;
            }elseif(!$errors['err']) {
                $errors['err']='No se a podido asignar el Ticket';
            }
            break; 
        case 'postnote':
            $fields=array();
            $fields['title']    = array('type'=>'string',   'required'=>1, 'error'=>'Titulo requerido');
            $fields['note']     = array('type'=>'string',   'required'=>1, 'error'=>'Nota requerida');
            $params = new Validator($fields);
            if(!$params->validate($_POST))
                $errors=array_merge($errors,$params->errors());

            if(!$errors && $ticket->postNote($_POST['title'],$_POST['note'])){
                $msg='Nota interna enviada';
                if(isset($_POST['ticket_status']) && $_POST['ticket_status']){
                    if($ticket->setStatus($_POST['ticket_status']) && $ticket->reload()){
                        $msg.=' y el estado a cambiado a '.($ticket->isClosed()?'cerrado':'abierto');
                        if($ticket->isClosed())
                            $page=$ticket=null; //Going back to main listing.
                    }
                }
            }elseif(!$errors['err']) {
                $errors['err']='Se a producido un error. No se a podido enviar la nota.';
            }
            break;
        case 'update':
            $page='editticket.inc.php';
            if(!$ticket || !$thisuser->canEditTickets())
                $errors['err']='Acci&oacute;n Denegada. No tienes permisos para editar los Tickets';
            elseif($ticket->update($_POST,$errors)){
                $msg='Ticket actualizado con &eacute;xito';
                $page='viewticket.inc.php';
            }elseif(!$errors['err']) {
                $errors['err']='Se a producido un Error. Intentalo de nuevo.';
            }
            break;
        case 'process':
            $isdeptmanager=($ticket->getDeptId()==$thisuser->getDeptId())?true:false;
            switch(strtolower($_POST['do'])):
                case 'change_priority':
                    if(!$thisuser->canManageTickets() && !$thisuser->isManager()){
                        $errors['err']='Acci&oacute;n Denegada. No tienes permiso para cambiar la prioridad del Ticket.';
                    }elseif(!$_POST['ticket_priority'] or !is_numeric($_POST['ticket_priority'])){
                        $errors['err']='Tienes que selecionar prioridad';
                    }
                    if(!$errors){
                        if($ticket->setPriority($_POST['ticket_priority'])){
                            $msg='El cambio de prioridad se arealizado con &eacute;xito';
                            $ticket->reload();
                            $note='La prioridad del tickets se a cambiado a "'.$ticket->getPriority().'" por '.$thisuser->getName();
                            $ticket->logActivity('Prioridad Cambiada',$note);
                        }else{
                            $errors['err']='Problema al cambiar prioridad. Intentalo de nuevo.';
                        }
                    }
                    break;
                case 'close':
                    if(!$thisuser->isadmin() && !$thisuser->canCloseTickets()){
                        $errors['err']='Acci&oacute;n Denegada. No tienes permiso para cerrar un ticket.';
                    }else{
                        if($ticket->close()){
                            $msg='Ticket #'.$ticket->getExtId().' el estado a cambiado a cerrado';
                            $note='Ticket de '.$thisuser->getName().'cerrado sin responder';
                            $ticket->logActivity('Ticket cerrado',$note);
                            $page=$ticket=null; //Going back to main listing.
                        }else{
                            $errors['err']='Problema al cerrar el Ticket. Intentalo de nuevo.';
                        }
                    }
                    break;
                case 'reopen':
                    //if they can close...then assume they can reopen.
                    if(!$thisuser->isadmin() && !$thisuser->canCloseTickets()){
                        $errors['err']='Acci&oacute;n Denegada. No tienes permiso para reabrir Tickets.';
                    }else{
                        if($ticket->reopen()){
                            $msg='Estado de Ticket cambiado a Abierto';
                            $note='Ticket reabierto (sin argumento)';
                            if($_POST['ticket_priority']) {
                                $ticket->setPriority($_POST['ticket_priority']);
                                $ticket->reload();
                                $note.=' y estado de prioridad cambiado a '.$ticket->getPriority();
                            }
                            $note.=' por '.$thisuser->getName();
                            $ticket->logActivity('Ticket reabierto',$note);
                        }else{
                            $errors['err']='Problema al reabrir Ticket. Intentalo de nuevo.';
                        }
                    }
                    break;
                case 'release':
                    if(!($staff=$ticket->getStaff()))
                        $errors['err']='Ticket sin asignar';
                    elseif($ticket->release()) {
                        $msg='Ticket liberado (sin asignar) por '.$staff->getName().' de '.$thisuser->getName();
                        $ticket->logActivity('Ticket no asignado',$msg);
                    }else
                        $errors['err']='Problema al liberar Ticket. Intentalo de nuevo';
                    break;
                case 'overdue':
                    //Mark the ticket as overdue
                    if(!$thisuser->isadmin() && !$thisuser->isManager()){
                        $errors['err']='Acci&oacute;n Denegada. No tienes permiso para marcar un Ticket como vencido.';
                    }else{
                        if($ticket->markOverdue()){
                            $msg='Ticket esta marcado como vencido';
                            $note=$msg;
                            if($_POST['ticket_priority']) {
                                $ticket->setPriority($_POST['ticket_priority']);
                                $ticket->reload();
                                $note.=' y se a cambiado el estado a '.$ticket->getPriority();
                            }
                            $note.=' de '.$thisuser->getName();
                            $ticket->logActivity('Ticket marcado como vencido',$note);
                        }else{
                            $errors['err']='Problema al marcar ticket como vencido. Intentalo de nuevo';
                        }
                    }
                    break;
                case 'banemail':
                    if(!$thisuser->isadmin() && !$thisuser->canManageBanList()){
                        $errors['err']='Acci&oacute;n Denegada. No tienes permiso para bloquear cuentas de correo';
                    }elseif(Banlist::add($ticket->getEmail(),$thisuser->getName())){
                        $msg='Email ('.$ticket->getEmail().') Agregado a la lista negra';
                        if($ticket->isOpen() && $ticket->close()) {
                            $msg.=' y el estado del ticket a cambiado a cerrado';
                            $ticket->logActivity('Ticket Cerrado',$msg);
                            $page=$ticket=null; //Going back to main listing.
                        }
                    }else{
                        $errors['err']='No se a podido agregar a la lista negra';
                    }
                    break;
                case 'unbanemail':
                    if(!$thisuser->isadmin() && !$thisuser->canManageBanList()){
                        $errors['err']='Acci&oacute;n Denegada. No tienes permiso para quitar emails de la lista negra.';
                    }elseif(Banlist::remove($ticket->getEmail())){
                        $msg='Email quitado de la lista negra';
                    }else{
                        $errors['err']='No se a podido quitar de la lista negra. Intentalo de nuevo.';
                    }
                    break;
                case 'delete': // Dude what are you trying to hide? bad customer support??
                    if(!$thisuser->isadmin() && !$thisuser->canDeleteTickets()){
                        $errors['err']='Acci&oacute;n Denegada. No tienes permiso para eliminar tickets';
                    }else{
                        if($ticket->delete()){
                            $page='tickets.inc.php'; //ticket is gone...go back to the listing.
                            $msg='Ticket eliminado para siempre';
                            $ticket=null; //clear the object.
                        }else{
                            $errors['err']='Problema al elimimar los Tickets. Intentalo de nuevo';
                        }
                    }
                    break;
                default:
                    $errors['err']='Debe seleccionar la acci&oacute;n a realizar';
            endswitch;
            break;
        default:
            $errors['err']='Acci&oacute;n incorrecta';
        endswitch;
        if($ticket && is_object($ticket))
            $ticket->reload();//Reload ticket info following post processing
    }elseif($_POST['a']) {
        switch($_POST['a']) {
            case 'mass_process':
                if(!$thisuser->canManageTickets())
                    $errors['err']='No tienes permiso para realizar acciones masivas, ponte en contacto con el Administrador para que te ceda este permiso.';    
                elseif(!$_POST['tids'] || !is_array($_POST['tids']))
                    $errors['err']='No se a seleccionado ningun ticket.';
                elseif(($_POST['reopen'] || $_POST['close']) && !$thisuser->canCloseTickets())
                    $errors['err']='No tienes permiso para reabrir o cerrar tickets.';
                elseif($_POST['delete'] && !$thisuser->canDeleteTickets())
                    $errors['err']='No tienes permiso para eliminar tickets';
                elseif(!$_POST['tids'] || !is_array($_POST['tids']))
                    $errors['err']='Tienes que seleccionar al menos un ticket';
        
                if(!$errors) {
                    $count=count($_POST['tids']);
                    if(isset($_POST['reopen'])){
                        $i=0;
                        $note='Ticket reabierto por '.$thisuser->getName();
                        foreach($_POST['tids'] as $k=>$v) {
                            $t = new Ticket($v);
                            if($t && @$t->reopen()) {
                                $i++;
                                $t->logActivity('Ticket reabierto',$note,false,'System');
                            }
                        }
                        $msg="$i de $count tickets seleccionados reabiertos";
                    }elseif(isset($_POST['close'])){
                        $i=0;
                        $note='Ticket cerrado sin responder por '.$thisuser->getName();
                        foreach($_POST['tids'] as $k=>$v) {
                            $t = new Ticket($v);
                            if($t && @$t->close()){ 
                                $i++;
                                $t->logActivity('Ticket cerrado',$note,false,'System');
                            }
                        }
                        $msg="$i de $count tickets seleccionados cerrados";
                    }elseif(isset($_POST['overdue'])){
                        $i=0;
                        $note='Ticket marcado como vencido por '.$thisuser->getName();
                        foreach($_POST['tids'] as $k=>$v) {
                            $t = new Ticket($v);
                            if($t && !$t->isoverdue())
                                if($t->markOverdue()) { 
                                    $i++;
                                    $t->logActivity('Ticket marcado como vencido',$note,false,'System');
                                }
                        }
                        $msg="$i de $count Tickets seleccionados marcados como vencidos";
                    }elseif(isset($_POST['delete'])){
                        $i=0;
                        foreach($_POST['tids'] as $k=>$v) {
                            $t = new Ticket($v);
                            if($t && @$t->delete()) $i++;
                        }
                        $msg="$i de $count Tickets seleccionados eliminados";
                    }
                }
                break;
            case 'open':
                $ticket=null;
                //TODO: check if the user is allowed to create a ticet.
                if(($ticket=Ticket::create_by_staff($_POST,$errors))) {
                    $ticket->reload();
                    $msg='Ticket creado con &eacute;xito';
                    if($thisuser->canAccessDept($ticket->getDeptId()) || $ticket->getStaffId()==$thisuser->getId()) {
                        //View the sucker
                        $page='viewticket.inc.php';
                    }else {
                        //Staff doesn't have access to the newly created ticket's department.
                        $page='tickets.inc.php';
                        $ticket=null;
                    }
                }elseif(!$errors['err']) {
                    $errors['err']='No se apodido crear el ticket, corrige el error e intentalo de nuevo.';
                }
                break;
        }
    }
    $crap='';
endif;
//Navigation 
$submenu=array();
/*quick stats...*/
$sql='SELECT count(open.ticket_id) as open, count(answered.ticket_id) as answered '.
     ',count(overdue.ticket_id) as overdue, count(assigned.ticket_id) as assigned '.
     ' FROM '.TICKET_TABLE.' ticket '.
     'LEFT JOIN '.TICKET_TABLE.' open ON open.ticket_id=ticket.ticket_id AND open.status=\'open\' AND open.isanswered=0 '.
     'LEFT JOIN '.TICKET_TABLE.' answered ON answered.ticket_id=ticket.ticket_id AND answered.status=\'open\' AND answered.isanswered=1 '.
     'LEFT JOIN '.TICKET_TABLE.' overdue ON overdue.ticket_id=ticket.ticket_id AND overdue.status=\'open\' AND overdue.isoverdue=1 '.
     'LEFT JOIN '.TICKET_TABLE.' assigned ON assigned.ticket_id=ticket.ticket_id AND assigned.staff_id='.db_input($thisuser->getId());
if(!$thisuser->isAdmin()){
    $sql.=' WHERE ticket.dept_id IN('.implode(',',$thisuser->getDepts()).') OR ticket.staff_id='.db_input($thisuser->getId());
}
//echo $sql;

$stats=db_fetch_array(db_query($sql));
//print_r($stats);
$nav->setTabActive('tickets');

if($cfg->showAnsweredTickets()) {
    $nav->addSubMenu(array('desc'=>'Abiertos ('.($stats['open']+$stats['answered']).')','title'=>'Tickets Abiertos', 'href'=>'tickets.php', 'iconclass'=>'Ticket'));
}else{
    if($stats['open'])
        $nav->addSubMenu(array('desc'=>'Abiertos ('.$stats['open'].')','title'=>'Tickets Abiertos', 'href'=>'tickets.php', 'iconclass'=>'Ticket'));
    if($stats['answered']) {
        $nav->addSubMenu(array('desc'=>'Respondidos ('.$stats['answered'].')','title'=>'Tickets Respondidos', 'href'=>'tickets.php?status=answered', 'iconclass'=>'answeredTickets')); 
    }
}

if($stats['assigned']) {
    if(!$sysnotice && $stats['assigned']>10)
        $sysnotice=$stats['assigned'].'Asignado a ti';

    $nav->addSubMenu(array('desc'=>'Mis Tickets ('.$stats['assigned'].')','title'=>'Mis Tickets Asignados','href'=>'tickets.php?status=assigned','iconclass'=>'assignedTickets'));
}

if($stats['overdue']) {
    $nav->addSubMenu(array('desc'=>'Vencidos ('.$stats['overdue'].')','title'=>'Tickets Vencidos',
                    'href'=>'tickets.php?status=overdue','iconclass'=>'overdueTickets'));

    if(!$sysnotice && $stats['overdue']>10)
        $sysnotice=$stats['overdue'] .' Tickets Vencidos';
}

$nav->addSubMenu(array('desc'=>'Cerrados','title'=>'Tickets Cerrados', 'href'=>'tickets.php?status=closed', 'iconclass'=>'closedTickets'));


if($thisuser->canCreateTickets()) {
    $nav->addSubMenu(array('desc'=>'Nuevo Ticket','href'=>'tickets.php?a=open','iconclass'=>'newTicket'));    
}

//Render the page...
$inc=$page?$page:'tickets.inc.php';

//If we're on tickets page...set refresh rate if the user has it configured. No refresh on search and POST to avoid repost.
if(!$_POST && $_REQUEST['a']!='search' && !strcmp($inc,'tickets.inc.php') && ($min=$thisuser->getRefreshRate())){ 
    define('AUTO_REFRESH',1);
}

require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
?>
