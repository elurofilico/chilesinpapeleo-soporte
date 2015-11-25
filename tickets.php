<?php
/*********************************************************************
    tickets.php

    Main client/user interface.
    Note that we are using external ID. The real (local) ids are hidden from user.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/
require('secure.inc.php');
if(!is_object($thisclient) || !$thisclient->isValid()) die('Acceso Denegado'); //Double check again.

require_once(INCLUDE_DIR.'class.ticket.php');
$ticket=null;
$inc='tickets.inc.php'; //Default page...show all tickets.
//Check if any id is given...
if(($id=$_REQUEST['id']?$_REQUEST['id']:$_POST['ticket_id']) && is_numeric($id)) {
    //id given fetch the ticket info and check perm.
    $ticket= new Ticket(Ticket::getIdByExtId((int)$id));
    if(!$ticket or !$ticket->getEmail()) {
        $ticket=null; //clear.
        $errors['err']='Acceso Denegado. Posiblemente ID de Ticket Erronea';
    }elseif(strcasecmp($thisclient->getEmail(),$ticket->getEmail())){
        $errors['err']='Violaci&oacute;n de Seguridad. Violaci&oacute;nes repetidas resultar&aacute;n en el bloqueo de tu cuenta.';
        $ticket=null; //clear.
    }else{
        //Everything checked out.
        $inc='viewticket.inc.php';
    }
}
//Process post...depends on $ticket object above.
if($_POST && is_object($ticket) && $ticket->getId()):
    $errors=array();
    switch(strtolower($_POST['a'])){
    case 'postmessage':
        if(strcasecmp($thisclient->getEmail(),$ticket->getEmail())) { //double check perm again!
            $errors['err']='Acceso Denegado. Posiblemente ID de Ticket Erronea';
            $inc='tickets.inc.php'; //Show the tickets.               
        }

        if(!$_POST['message'])
            $errors['message']='Mensaje requerido';
        //check attachment..if any is set
        if($_FILES['attachment']['name']) {
            if(!$cfg->allowOnlineAttachments()) //Something wrong with the form...user shouldn't have an option to attach
                $errors['attachment']='Archivo [ '.$_FILES['attachment']['name'].' ] Rechazado';
            elseif(!$cfg->canUploadFileType($_FILES['attachment']['name']))
                $errors['attachment']='Extensi&oacute;n de archivo no v&aacute;lido [ '.$_FILES['attachment']['name'].' ]';
            elseif($_FILES['attachment']['size']>$cfg->getMaxFileSize())
                $errors['attachment']='Archivo muy grande. Max. '.$cfg->getMaxFileSize().' bytes permitidos';
        }
                    
        if(!$errors){
            //Everything checked out...do the magic.
            if(($msgid=$ticket->postMessage($_POST['message'],'Web'))) {
                if($_FILES['attachment']['name'] && $cfg->canUploadFiles() && $cfg->allowOnlineAttachments())
                    $ticket->uploadAttachment($_FILES['attachment'],$msgid,'M');
                    
                $msg='Mensaje enviado con &eacute;xito';
            }else{
                $errors['err']='No se a podido enviar el mensaje. Intentalo de nuevo';
            }
        }else{
            $errors['err']=$errors['err']?$errors['err']:'A ocurrido un Error. Intentalo de nuevo';
        }
        break;
    default:
        $errors['err']='Acci&oacute;n desconocida';
    }
    $ticket->reload();
endif;
include(CLIENTINC_DIR.'header.inc.php');
include(CLIENTINC_DIR.$inc);
include(CLIENTINC_DIR.'footer.inc.php');
?>
