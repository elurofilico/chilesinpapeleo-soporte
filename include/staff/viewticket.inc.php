<?php 
//Note that ticket is initiated in tickets.php.
if(!defined('OSTSCPINC') || !@$thisuser->isStaff() || !is_object($ticket) ) die('Ruta Invalida');
if(!$ticket->getId() or (!$thisuser->canAccessDept($ticket->getDeptId()) and $thisuser->getId()!=$ticket->getStaffId())) die('Acceso Denegado');

$info=($_POST && $errors)?Format::input($_POST):array(); //Re-use the post info on error...savekeyboards.org

//Auto-lock the ticket if locking is enabled..if locked already simply renew it.
if($cfg->getLockTime() && !$ticket->acquireLock())
    $warn.='No se puede bloquear el Ticket.';

//We are ready baby...lets roll. Akon rocks! 
$dept  = $ticket->getDept();  //Dept
$staff = $ticket->getStaff(); //Assiged staff.
$lock  = $ticket->getLock();  //Ticket lock obj
$id=$ticket->getId(); //Ticket ID.
$stati = $ticket->getStatus();
switch(strtolower($stati)){ //Status is overloaded
    case 'open':
        $ger_status='Abierto';
        break;
    case 'closed':
        $ger_status='Cerrado';
        break;
    default:
        $ger_status='Abierto';
}

if($staff)
    $warn.='&nbsp;&nbsp;<span class="Icon assignedTicket">Ticket asignado a '.$staff->getName().'</span>';
if(!$errors['err'] && ($lock && $lock->getStaffId()!=$thisuser->getId()))
    $errors['err']='Este Ticket est&aacute; bloqueado por otro miembro del personal!';
if(!$errors['err'] && ($emailBanned=BanList::isbanned($ticket->getEmail())))
    $errors['err']='Este Email esta en la lista negra! Debe ser removido de la lista para responder';    
if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">Marcado como Vencido!</span>';
    
?>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td class="msg" width=50%>
            Ticket ID<?php echo $ticket->getExtId()?>&nbsp;<a href="tickets.php?id=<?php echo $id?>" title="Refrescar"><span class="Icon refresh">&nbsp;</span></a></td>

        <td class="msg" width=50%>
            <?php  if($thisuser->canEditTickets() || ($thisuser->isManager() && $dept->getId()==$thisuser->getDeptId())) { ?>
             <a href="tickets.php?id=<?php echo $id?>&a=edit" title="Ticket &auml;ndern" class="Icon editTicket">Editar Ticket</a>
            <?php }?>
        </td>
    </tr>
    <tr>
     <td width=50%>	
		<table align="center" class="ticketinfo" cellspacing="1" cellpadding="3" width="100%" border=0>
			<tr>
				<th>Estado:</th>
				<td><?php echo $ger_status?></td>
			</tr>
			<tr>
        		<th>Prioridad:</th>
        		<td><?php echo $ticket->getPriority()?></td>
   	 		</tr>
            <tr>
                <th>Asesor:</th>
                <td><?php echo Format::htmlchars($ticket->getDeptName())?></td>
            </tr>
			<tr>
                <th>Creado:</th>
                <td><?php echo Format::db_datetime($ticket->getCreateDate())?></td>
            </tr>
                        <tr><th>Institución:</th>
                <td><?php 
                    $ht=$ticket->getHelpTopic();
                    echo Format::htmlchars($ht?$ht:'N/A');
                    ?>
                </td>
            </tr>

		</table>
     </td>
     <td width=50% valign="top">
        <table align="center" class="ticketinfo" cellspacing="1" cellpadding="3" width="100%" border=0>
            <tr>
                <th>Nombre:</th>
                <td><?php echo Format::htmlchars($ticket->getName())?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php  
                    echo $ticket->getEmail();
                    if(($related=$ticket->getRelatedTicketsCount())) {
                        echo sprintf('&nbsp;&nbsp;<a href="tickets.php?a=search&query=%s" title="Tickets Relacionados">(<b>%d</b>)</a>',
                                    urlencode($ticket->getEmail()),$related);
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Tel&eacute;fono:</th>
                <td><?php echo $ticket->getPhoneNumber()?></td>
            </tr>
            <tr>
		<th>Origen:</th>
                <td><?php echo $ticket->getSource()?></td>
            </tr>
        </table>
     </td>
    </tr>
    <tr><td colspan=2 class="msg">Asunto: <?php echo Format::htmlchars($ticket->getSubject())?></td></tr>
    <tr>
     <td valign="top" width=50%>
        <table align="center" class="ticketinfo" cellspacing="1" cellpadding="3" width="100%" border=0>
            <tr>
                <th>Staff asignado:</th>
                <td><?php echo $staff?Format::htmlchars($staff->getName()):'- Sin Asignar -'?></td>
            </tr>
            <tr>
                <th nowrap>Última Respuesta:</th>
                <td><?php echo Format::db_datetime($ticket->getLastResponseDate())?></td>
            </tr>
            <?php 
            if($ticket->isOpen()){ ?>
            <tr>
                <th>Fecha de cierre:</th>
                <td><?php echo Format::db_datetime($ticket->getDueDate())?></td>
            </tr>
            <?php 
            }else { ?>
            <tr>
                <th>Cerrado el:</th>
                <td><?php echo Format::db_datetime($ticket->getCloseDate())?></td>
            </tr>
            <?php 
            }
            ?>
        </table>
     </td>
     <td width=50% valign="top">
        <table align="center" class="ticketinfo" cellspacing="1" cellpadding="3" width="100%" border=0>
            <tr>
                <th>Área de ayuda:</th>
                <td><?php echo $ticket->getServicio()->nombre?$ticket->getServicio()->nombre:' Sin área ';?></td>
            </tr>
            <tr>
                <th>Direcci&oacute;n IP:</th>
                <td><?php echo $ticket->getIP()?></td>
            </tr>
            <tr><th nowrap>Ultimo Mensaje:</th>
                <td><?php echo Format::db_datetime($ticket->getLastMessageDate())?></td>
            </tr>
        </table>
     </td>
    </tr>
</table>
<div>
    <?php if($errors['err'] && $_POST['a']=='process') {?>
        <p align="center" id="errormessage"><?php echo $errors['err']?></p>
    <?php }elseif($msg && $_POST['a']=='process' || $_POST['a']=='update' ) {?>
        <p align="center" id="infomessage"><?php echo $msg?></p>
    <?php }elseif($warn) {?>
        <p id="warnmessage"><?php echo $warn?></p>
    <?php }?>
</div>
<?php 
//Ticket adminstrative options...admin,managers and staff with manage perm allowed.
if($thisuser->canManageTickets() || $thisuser->isManager()){ ?> 
<table cellpadding="0" cellspacing="2" border="0" width="100%" class="ticketoptions">
    <tr><td>
        <form name=action action='tickets.php?id=<?php echo $id?>' method=post class="inline" >
            <input type='hidden' name='ticket_id' value="<?php echo $id?>"/>
             <input type='hidden' name='a' value="process"/>
            <span for="do"> &nbsp;<b>Acci&oacute;n:</b></span>
            <select id="do" name="do" 
              onChange="this.form.ticket_priority.disabled=strcmp(this.options[this.selectedIndex].value,'change_priority','reopen','overdue')?false:true;">
                <option value="">Seleccionar</option>
                <option value="change_priority" <?php echo $info['do']=='change_priority'?'selected':''?> >Cambiar Prioridad</option>
                <?php if(!$ticket->isoverdue()){ ?>
                <option value="overdue" <?php echo $info['do']=='overdue'?'selected':''?> >Marcar como Vencido</option>
                <?php }?>
                <?php if($ticket->isAssigned()){ ?>
                <option value="release" <?php echo $info['do']=='release'?'selected':''?> >Liberar (desasignar)</option>
                <?php }?>
                
                <?php if($thisuser->canCloseTickets()){
                    //if you can close a ticket...reopening it is given.
                    if($ticket->isOpen()){?>
                     <option value="close" <?php echo $info['do']=='close'?'selected':''?> >Cerrar Ticket</option>
                    <?php }else{?>
                        <option value="reopen" <?php echo $info['do']=='reopen'?'selected':''?> >Reabrir</option>
                    <?php }
                }?>
                <?php 
                 if($thisuser->canManageBanList()) {
                    if(!$emailBanned) {?>    
                        <option value="banemail" >Bloquear Email<?php echo $ticket->isOpen()?'&amp;Cerrar':''?></option>
                    <?php }else{?>
                        <option value="unbanemail">Desbloquear Email</option>
                    <?php }
                 }?>
                
                <?php if($thisuser->canDeleteTickets()){ //oooh...fear the deleters! ?>
                <option value="delete" >Borrar Ticket</option>
                <?php }?>
            </select>
            <span for="ticket_priority">Prioridad:</span>
            <select id="ticket_priority" name="ticket_priority" <?php echo !$info['do']?'disabled':''?> >
                <option value="0" selected="selected">-La Actual-</option>
                <?php 
                $priorityId=$ticket->getPriorityId();
                $resp=db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
                while($row=db_fetch_array($resp)){ ?>
                    <option value="<?php echo $row['priority_id']?>" <?php echo $priorityId==$row['priority_id']?'disabled':''?> ><?php echo $row['priority_desc']?></option>
                <?php }?>
            </select>
                &nbsp;&nbsp;
            <input class="button" type="submit" value="Cambiar">
        </form>
    </tr></td>
</table>
<?php }?>
<br>
<?php 
//Internal Notes

$sql ='SELECT note_id,title,note,source,created FROM '.TICKET_NOTE_TABLE.' WHERE ticket_id='.db_input($id).' ORDER BY created DESC';
if(($resp=db_query($sql)) && ($notes=db_num_rows($resp))){
    $display=($notes>5)?'none':'block'; //Collapse internal notes if more than 5.
?>
<div align="left">
    <a class="Icon note" href="#" onClick="toggleLayer('ticketnotes'); return false;">Notas Internas (<?php echo $notes?>)</a><br><br>
    <div id='ticketnotes' style="display:<?php echo $display?>;text-align:center;"> 
        <?php 
        while($row=db_fetch_array($resp)) {?>
        <table align="center" class="note" cellspacing="0" cellpadding="1" width="100%" border=0>
            <tr><th><?php echo Format::fecha($row['created'])?>&nbsp;-&nbsp;Creada por <?php echo $row['source']?></th></tr>
            <?php  if($row['title']) {?>
            <tr class="header"><td><?php echo Format::display($row['title'])?></td></tr>
            <?php } ?>
            <tr><td><?php echo Format::display($row['note'])?></td></tr>
        </table>
     <?php } ?>
   </div>
</div>
<?php } ?>
<div align="left">
    <a class="Icon thread" href="#" onClick="toggleLayer('ticketthread'); return false;">Historial del Ticket</a>
    <div id="ticketthread">
	<?php 
	    //get messages
        $sql='SELECT msg.msg_id,msg.created,msg.message,count(attach_id) as attachments  FROM '.TICKET_MESSAGE_TABLE.' msg '.
            ' LEFT JOIN '.TICKET_ATTACHMENT_TABLE." attach ON  msg.ticket_id=attach.ticket_id AND msg.msg_id=attach.ref_id AND ref_type='M' ".
            ' WHERE  msg.ticket_id='.db_input($id).
            ' GROUP BY msg.msg_id ORDER BY created'; 
	    $msgres =db_query($sql);
	    while ($msg_row = db_fetch_array($msgres)) {
		    ?>
		    <table align="center" class="message" cellspacing="0" cellpadding="1" width="100%" border=0>
		        <tr><th><?php echo Format::fecha($msg_row['created'])?></th></tr>
                <?php if($msg_row['attachments']>0){ ?>
                <tr class="header"><td><?php echo $ticket->getAttachmentStr($msg_row['msg_id'],'M')?></td></tr> 
                <?php }?>
                <tr><td><?php echo Format::display($msg_row['message'])?>&nbsp;</td></tr>
		    </table>
            <?php 
            //get answers for messages
            $sql='SELECT resp.*,count(attach_id) as attachments FROM '.TICKET_RESPONSE_TABLE.' resp '.
                ' LEFT JOIN '.TICKET_ATTACHMENT_TABLE." attach ON  resp.ticket_id=attach.ticket_id AND resp.response_id=attach.ref_id AND ref_type='R' ".
                ' WHERE msg_id='.db_input($msg_row['msg_id']).' AND resp.ticket_id='.db_input($id).
                ' GROUP BY resp.response_id ORDER BY created';
		    $resp =db_query($sql);
		    while ($resp_row = db_fetch_array($resp)) {
                $respID=$resp_row['response_id'];
                ?>
    		    <table align="center" class="response" cellspacing="0" cellpadding="1" width="100%" border=0>
    		        <tr><th><?php echo Format::fecha($resp_row['created'])?>&nbsp;-&nbsp;<?php echo $resp_row['staff_name']?></th></tr>
                    <?php if($resp_row['attachments']>0){ ?>
                    <tr class="header">
                        <td><?php echo $ticket->getAttachmentStr($respID,'R')?></td></tr>
                    <?php }?>
			        <tr><td> <?php echo Format::display($resp_row['response'])?></td></tr>
		        </table>
	        <?php }
            $msgid =$msg_row['msg_id'];
	    }?>
    </div>
</div>
<table align="center" cellspacing="0" cellpadding="3" width="90%" border=0>
  <?php if($_POST['a']!='process') {?>
  <tr> <td align="center">
     <?php if($errors['err']) {?>
        <p align="center" id="errormessage"><?php echo $errors['err']?></p>
    <?php }elseif($msg) {?>
        <p align="center" id="infomessage"><?php echo $msg?></p>
    <?php }?> 
  </td></tr>
  <?php }?>
  <tr> <td align="center">
        <div class="tabber">
            <div id="reply" class="tabbertab" align="left">
                <h2>Responder</h2>
                <p>
                    <form action="tickets.php?id=<?php echo $id?>#reply" name="reply" id="replyform" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="ticket_id" value="<?php echo $id?>">
                        <input type="hidden" name="msg_id" value="<?php echo $msgid?>">
                        <input type="hidden" name="a" value="reply">
                        <div><font class="error">&nbsp;<?php echo $errors['response']?></font></div>
                        <div>
                           <?php 
                             $sql='SELECT premade_id,title FROM '.KB_PREMADE_TABLE.' WHERE isenabled=1 '.
                                ' AND (dept_id=0 OR dept_id='.db_input($ticket->getDeptId()).')';
                            $canned=db_query($sql);
                            if($canned && db_num_rows($canned)) {
                             ?>
                               Respuesta Predefinida
                               <select id="canned" name="canned"
                                onChange="getCannedResponse(this.options[this.selectedIndex].value,this.form,'response');this.selectedIndex='0';" >
                                <option value="0" selected="selected">Seleccionar</option>
                                <?php while(list($cannedId,$title)=db_fetch_row($canned)) { ?>
                                 <option value="<?php echo $cannedId?>" ><?php echo Format::htmlchars($title)?></option>
                                <?php }?>
                               </select>&nbsp;&nbsp;&nbsp;<label><input type='checkbox' value='1' name=append checked="true" />Incluir</label>
                            <?php }?>
                            <textarea name="response" id="response" cols="90" rows="9" wrap="soft" style="width:90%"><?php echo $info['response']?></textarea>
                        </div>
                        <?php  if($cfg->canUploadFiles()){ //TODO: may be allow anyways and simply email out attachment?? ?>
                        <div style="margin-top: 3px;">
                            <label for="attachment" >Adjuntar Archivo:</label>
                            <input type="file" name="attachment" id="attachment" size=30px value="<?php echo $info['attachment']?>" /> 
                                <font class="error">&nbsp;<?php echo $errors['attachment']?></font>
                        </div>
                        <?php  }?>
                        <?php 
                         $appendStaffSig=$thisuser->appendMySignature();
                         $appendDeptSig=$dept->canAppendSignature();
                         $info['signature']=!$info['signature']?'none':$info['signature']; //change 'none' to 'mine' to default to staff signature.
                         if($appendStaffSig || $appendDeptSig) { ?>
                          <div style="margin-top: 10px;">
                                <label for="signature" nowrap>Incluir Firma:</label>
                                <label><input type="radio" name="signature" value="none" checked > Ninguna</label>
                                <?php if($appendStaffSig) {?>
                               <label> <input type="radio" name="signature" value="mine" <?php echo $info['signature']=='mine'?'checked':''?> > Mi Firma</label>
                                <?php }?>
                                <?php if($appendDeptSig) {?>
                                <label><input type="radio" name="signature" value="dept" <?php echo $info['signature']=='dept'?'checked':''?> > Firma del Departamento</label>
                                <?php }?>
                           </div>
                         <?php }?>
                        <div style="margin-top: 3px;">
                            <b>Estado del Ticket:</b>
                            <?php 
                            $checked=isset($info['ticket_status'])?'checked':''; //Staff must explicitly check the box to change status..
                            if($ticket->isOpen()){?>
                            <label><input type="checkbox" name="ticket_status" id="l_ticket_status" value="Close" <?php echo $checked?> > Cerrar al Responder</label>
                            <?php }else{ ?>
                            <label><input type="checkbox" name="ticket_status" id="l_ticket_status" value="Reopen" <?php echo $checked?> > Reabrir al Responder</label>
                            <?php }?>
                        </div>
                        <p>
                            <div  style="margin-left: 50px; margin-top: 30px; margin-bottom: 10px;border: 0px;">
                                <input class="button" type='submit' value='Enviar Respuesta' />
                                <input class="button" type='reset' value='Restablecer' />
                                <input class="button" type='button' value='Cancelar' onClick="history.go(-1)" />
                            </div>
                        </p>
                    </form>                
                </p>
            </div>
            <div id="notes" class="tabbertab"  align="left">
                <h2>Nota Interna</h2>
                <p>
                    <form action="tickets.php?id=<?php echo $id?>#notes" name="notes" class="inline" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="ticket_id" value="<?php echo $id?>">
                        <input type="hidden" name="a" value="postnote">
                        <div>
                            <label for="title"> Asunto :</label>
                            <input type="text" name="title" id="title" value="<?php echo $info['title']?>" size=30px />
                            </select><font class="error">*&nbsp;<?php echo $errors['title']?></font>
                        </div>
                        <div style="margin-top: 3px;">
                            <label for="note" valign="top"> Notificaci&oacute;n.
                                <font class="error">*&nbsp;<?php echo $errors['note']?></font></label><br/>
                            <textarea name="note" id="note" cols="80" rows="7" wrap="soft" style="width:90%"><?php echo $info['note']?></textarea>
                        </div>

                        <?php 
                         //When the ticket is assigned Allow assignee, admin or ANY dept manager to close it
                        if(!$ticket->isAssigned() || $thisuser->isadmin()  || $thisuser->isManager() || $thisuser->getId()==$ticket->getStaffId()) {
                         ?>
                        <div style="margin-top: 3px;">
                            <b>Estado del Ticket:</b>
                            <?php 
                            $checked=($info && isset($info['ticket_status']))?'checked':''; //not selected by default.
                            if($ticket->isOpen()){?>
                            <label><input type="checkbox" name="ticket_status" id="ticket_status" value="Close" <?php echo $checked?> >Cerrar Ticket</label>
                            <?php }else{ ?>
                            <label><input type="checkbox" name="ticket_status" id="ticket_status" value="Reopen" <?php echo $checked?> > Reabrir Ticket</label>
                            <?php }?>
                        </div>
                        <?php }?>
                        <p>
                            <div  align="left" style="margin-left: 50px;margin-top: 10px; margin-bottom: 10px;border: 0px;">
                                <input class="button" type='submit' value='Enviar Nota' />
                                <input class="button" type='reset' value='Restablecer' />
                                <input class="button" type='button' value='Cancelar' onClick="history.go(-1)" />
                            </div>
                        </p>
                    </form>
                </p>
            </div>
            <?php 
            if($thisuser->canTransferTickets()) { 
                ?>
            <div id="transfer" class="tabbertab"  align="left">
                <h2>Transferir a Departamento</h2>
                <p>

                    <form action="tickets.php?id=<?php echo $id?>#transfer" name="notes" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="ticket_id" value="<?php echo $id?>">
                        <input type="hidden" name="a" value="transfer">
                        <div>
                            <span for="dept_id">Departamento:</span>
                            <select id="dept_id" name="dept_id">
                                <option value="" selected="selected">- Selecciona Departamento -</option>
                                <?php 
                                $depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE.' WHERE dept_id!='.db_input($ticket->getDeptId()));
                                while (list($deptId,$deptName) = db_fetch_row($depts)){
                                    $selected = ($info['dept_id']==$deptId)?'selected':''; ?>
                                    <option value="<?php echo $deptId?>"<?php echo $selected?>><?php echo $deptName?></option>
                                <?php 
                                }?>
                            </select><font class='error'>&nbsp;*<?php echo $errors['dept_id']?></font>
                        </div>
                        <div>
                            <span >Comentarios / Motivos de la transferencia (<i>nota interna</i>)
                                <font class='error'>&nbsp;*<?php echo $errors['message']?></font></span>
                            <textarea name="message" id="message" cols="80" rows="7" wrap="soft" style="width:90%;"><?php echo $info['message']?></textarea>
                        </div>
                        <p>
                            <div  style="margin-left: 50px; margin-top: 5px; margin-bottom: 10px;border: 0px;" align="left">
                                <input class="button" type='submit' value='Transferir' />
                                <input class="button" type='reset' value='Restablecer' />
                                <input class="button" type='button' value='Cancelar' onClick="history.go(-1)" />
                            </div>
                        </p>
                    </form>
                </p>
            </div>
            <?php }?>
            <?php 
             //When the ticket is assigned Allow assignee, admin or ANY dept manager to reassign the ticket.
            if(!$ticket->isAssigned() || $thisuser->isadmin()  || $thisuser->isManager() || $thisuser->getId()==$ticket->getStaffId()) {
                 ?>
            <div id="assign" class="tabbertab"  align="left">
                
                <h2><?php echo $staff?'Reasignar Ticket':'Asignar Ticket'?></h2>
                <p>
                    <form action="tickets.php?id=<?php echo $id?>#assign" name="notes" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="ticket_id" value="<?php echo $id?>">
                        <input type="hidden" name="a" value="assign">
                        <div>
                            <span for="staffId">Miembro del Staff:</span>
                            <select id="staffId" name="staffId">
                                <option value="0" selected="selected">- Seleccionar -</option>
                                <?php 
                                //TODO: make sure the user's group is also active....DO a join.
                                $sql=' SELECT staff_id,CONCAT_WS(", ",lastname,firstname) as name FROM '.STAFF_TABLE.
                                     ' WHERE isactive=1 AND onvacation=0 ';
                                if($ticket->isAssigned()) 
                                    $sql.=' AND staff_id!='.db_input($ticket->getStaffId());
                                $depts= db_query($sql.' ORDER BY lastname,firstname ');
                                while (list($staffId,$staffName) = db_fetch_row($depts)){
                                    
                                    $selected = ($info['staffId']==$staffId)?'selected':''; ?>
                                    <option value="<?php echo $staffId?>"<?php echo $selected?>><?php echo $staffName?></option>
                                <?php 
                                }?>
                            </select><font class='error'>&nbsp;*<?php echo $errors['staffId']?></font>
                        </div>
                        <div>
                            <span >Comentarios. &nbsp;(<i>nota interna</i>)
                                <font class='error'>&nbsp;*<?php echo $errors['assign_message']?></font></span>
                            <textarea name="assign_message" id="assign_message" cols="80" rows="7" 
                                wrap="soft" style="width:90%;"><?php echo $info['assign_message']?></textarea>
                        </div>
                        <p>
                            <div  style="margin-left: 50px; margin-top: 5px; margin-bottom: 10px;border: 0px;" align="left">
                                <input class="button" type='submit' value='Asignar' />
                                <input class="button" type='reset' value='Restablecer' />
                                <input class="button" type='button' value='Cancelar' onClick="history.go(-1)" />
                            </div>
                        </p>
                    </form>
                </p>
            </div>
            <?php }?>
        </div>
    </td>
 </tr>
</table>
