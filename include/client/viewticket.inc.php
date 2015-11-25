<?php 
if(!defined('OSTCLIENTINC') || !is_object($thisclient) || !is_object($ticket)) die('ElArteDeGanar.com'); //bye..see ya
//Double check access one last time...
if(strcasecmp($thisclient->getEmail(),$ticket->getEmail())) die('Acceso Denegado');

$info=($_POST && $errors)?Format::input($_POST):array(); //Re-use the post info on error...savekeyboards.org

$dept = $ticket->getDept();
//Making sure we don't leak out internal dept names
$dept=($dept && $dept->isPublic())?$dept:$cfg->getDefaultDept();
//We roll like that...
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
?>
<?php  if($errors['err']) {?>
<div class="alert alert-error">
    <p id="errormessage">
    	<strong>Error</strong>
    	<br/><br/>
    	<?php  echo $errors['err']?>
    </p>
    <ol>
    <?php if ($errors['message']){ ?>
        <li><?php echo $errors['message']?></li>
    <?php } ?>
    </ol>
</div>
<?php } ?>
<h3>Ticket #<?php echo $ticket->getExtId()?></h3>
<table class="table table-striped table-bordered table-condensed">
    <tr>
       <th>Estado</th>
       <td><?php echo $ger_status?></td>
    </tr>
    <tr>
       <th>Asesor</th>
       <td><?php echo Format::htmlchars($dept->getName())?></td>
   </tr>
   <tr>
        <th>Fecha de ingreso</th>
        <td><?php echo Format::db_datetime($ticket->getCreateDate())?></td>
    </tr>
    <tr>
        <th>Nombre completo</th>
        <td><?php echo Format::htmlchars($ticket->getName())?></td>
    </tr>
    <tr>    
        <th>Correo electrónico</th>
        <td><?php echo $ticket->getEmail()?></td>
    </tr>
    <tr>
        <th>Tel&eacute;fono</th>
        <td><?php echo $ticket->getPhoneNumber()?></td>
    </tr>
    <tr>
        <th>Asunto</th>
        <td><?php echo Format::htmlchars($ticket->getSubject())?></td>
    </tr>
    <tr>
    	<th>Área de ayuda</th>
    	<td><?php echo $ticket->getServicio()->nombre; ?></td>
    </tr>
</table>

<h3>Historial del ticket</h3>

        <?php 
	    //get messages
        $sql='SELECT msg.*, count(attach_id) as attachments  FROM '.TICKET_MESSAGE_TABLE.' msg '.
            ' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  msg.ticket_id=attach.ticket_id AND msg.msg_id=attach.ref_id AND ref_type=\'M\' '.
            ' WHERE  msg.ticket_id='.db_input($ticket->getId()).
            ' GROUP BY msg.msg_id ORDER BY created';
	    $msgres =db_query($sql);
	    while ($msg_row = db_fetch_array($msgres)):
		    ?>
		    <table class="table table-bordered table-condensed">
		        <tr class="warning">
                    <th><i class="icon-user"></i> <?php echo Format::htmlchars($ticket->getName())?><span class="pull-right"><?php echo Format::fecha($msg_row['created'])?></span></th>
                </tr>
                <?php if($msg_row['attachments']>0){ ?>
                <tr class="warning">
                    <td><?php echo $ticket->getAttachmentStr($msg_row['msg_id'],'M')?></td>
                </tr> 
                <?php }?>
                <tr class="warning">
                    <td><?php echo Format::display($msg_row['message'])?></td>
                </tr>
		    </table>
            <?php 
            //get answers for messages
            $sql='SELECT resp.*,count(attach_id) as attachments FROM '.TICKET_RESPONSE_TABLE.' resp '.
                ' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  resp.ticket_id=attach.ticket_id AND resp.response_id=attach.ref_id AND ref_type=\'R\' '.
                ' WHERE msg_id='.db_input($msg_row['msg_id']).' AND resp.ticket_id='.db_input($ticket->getId()).
                ' GROUP BY resp.response_id ORDER BY created';
            //echo $sql;
		    $resp =db_query($sql);
		    while ($resp_row = db_fetch_array($resp)) {
                $respID=$resp_row['response_id'];
                $name=$cfg->hideStaffName()?'staff':Format::htmlchars($resp_row['staff_name']);
                ?>
    		    <table class="table table-bordered table-condensed">
    		        <tr class="success">
    			        <th><i class="icon-leaf"></i> <?php echo $name?> <span class="pull-right"><?php echo Format::fecha($resp_row['created']);?></span></th>
                    </tr>
                    <?php if($resp_row['attachments']>0){ ?>
                    <tr class="success">
                        <td><?php echo $ticket->getAttachmentStr($respID,'R')?></td>
                    </tr>                
                    <?php }?>
			        <tr class="success">
				        <td> <?php echo Format::display($resp_row['response'])?></td>
                    </tr>
		        </table>
		    <?php 
		    } //endwhile...response loop.
            $msgid =$msg_row['msg_id'];
        endwhile; //message loop.
     ?>

        <?php if($_POST && $errors['err']) {?>
            <p align="center" id="errormessage"><?php echo $errors['err']?></p>
        <?php }elseif($msg) {?>
            <p align="center" id="infomessage"><?php echo $msg?></p>
        <?php }?>
        <?php if($ticket->isClosed()) {?>
            <div class="alert">
    <strong>Atención</strong> Al enviar una respuesta el ticket será reabierto.
    </div>
        <?php }?> 
<h3>Nuevo mensaje</h3>
        <form action="view.php?id=<?php echo $id?>#reply" name="reply" method="post" enctype="multipart/form-data" class="form-horizontal">
            <input type="hidden" name="id" value="<?php echo $ticket->getExtId()?>">
            <input type="hidden" name="respid" value="<?php echo $respID?>">
            <input type="hidden" name="a" value="postmessage">
            <div class="control-group">
                <label class="control-label"><strong>Contenido</strong> *<?php echo $errors['message']?></label>
                <div class="controls">
                <textarea name="message" id="message" rows="7"><?php echo $info['message']?></textarea>
                </div>
            </div>
            <?php  if($cfg->allowOnlineAttachments()) {?>
            <div class="control-group">
                <label class="control-label">Adjuntar Archivo</label>
                <div class="controls">
                    <input type="file" name="attachment" id="attachment" size=30px value="<?php echo $info['attachment']?>" />
                </div>
                <label>&nbsp;<?php echo $errors['attachment']?></label>
            </div>
            <?php }?>
            <div class="form-actions">
                <input class="btn" type='submit' value='Enviar' />
                <input class="btn" type='reset' value='Limpiar formulario' />
                <input class="btn" type='button' value='Cancelar' onClick='window.location.href="view.php"' />
            </div>
        </form>

