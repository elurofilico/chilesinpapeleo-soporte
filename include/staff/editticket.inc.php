<?php 
if(!defined('OSTSCPINC') || !is_object($ticket) || !is_object($thisuser) || !$thisuser->isStaff()) die('Acceso Denegado');

if(!($thisuser->canEditTickets() || ($thisuser->isManager() && $ticket->getDeptId()==$thisuser->getDeptId()))) die('Acceso Denegado, Error Permanente.');

if($_POST && $errors){
    $info=Format::input($_POST);
}else{
    $info=array('email'=>$ticket->getEmail(),
                'name' =>$ticket->getName(),
                'phone'=>$ticket->getPhone(),
                'phone_ext'=>$ticket->getPhoneExt(),
                'pri'=>$ticket->getPriorityId(),
                'topicId'=>$ticket->getTopicId(),
                'topic'=>$ticket->getHelpTopic(),
                'subject' =>$ticket->getSubject(),
                'servicio_codigo' =>$ticket->getServicio()->codigo,
                'duedate' =>$ticket->getDueDate()?(Format::userdate('m/d/Y',Misc::db2gmtime($ticket->getDueDate()))):'',
                'time'=>$ticket->getDueDate()?(Format::userdate('G:i',Misc::db2gmtime($ticket->getDueDate()))):'',
                );
    /*Note: Please don't make me explain how dates work - it is torture. Trust me! */
}

?>
<div width="100%">
    <?php if($errors['err']) {?>
        <p align="center" id="errormessage"><?php echo $errors['err']?></p>
    <?php }elseif($msg) {?>
        <p align="center" class="infomessage"><?php echo $msg?></p>
    <?php }elseif($warn) {?>
        <p class="warnmessage"><?php echo $warn?></p>
    <?php }?>
</div>
<table width="100%" border="0" cellspacing=1 cellpadding=2>
  <form action="tickets.php?id=<?php echo $ticket->getId()?>" method="post">
    <input type='hidden' name='id' value='<?php echo $ticket->getId()?>'>
    <input type='hidden' name='a' value='update'>
    <tr><td align="left" colspan=2 class="msg">
        Actualizar # Ticket<?php echo $ticket->getExtId()?>&nbsp;&nbsp;(<a href="tickets.php?id=<?php echo $ticket->getId()?>" style="color:black;">Ver Ticket</a>)<br></td></tr>
    <tr>
        <td align="left" nowrap width="120"><b>Email:</b></td>
        <td>
            <input type="text" id="email" name="email" size="25" value="<?php echo $info['email']?>">
            &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['email']?></font>
        </td>
    </tr>
    <tr>
        <td align="left" ><b>Nombre:</b></td>
        <td>
            <input type="text" id="name" name="name" size="25" value="<?php echo $info['name']?>">
            &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['name']?></font>
        </td>
    </tr>
    <tr>
        <td align="left"><b>Asunto:</b></td>
        <td>
            <input type="text" name="subject" size="35" value="<?php echo $info['subject']?>">
            &nbsp;<font class="error">*&nbsp;<?php echo $errors['subject']?></font>
        </td>
    </tr>
    <tr>
        <td align="left"><b>Servicio:</b></td>
        <td>
            <select name="servicio_codigo" id="servicio_codigo">
            	<option value="">- Seleccione -</option>
	            <?php 
	            $servicios= db_query('SELECT codigo, nombre FROM seg_servicio ORDER BY nombre ASC');
	            if($servicios && db_num_rows($servicios)) {
	                while (list($codigo,$nombre) = db_fetch_row($servicios)){
	                    $selected = ($info['servicio_codigo']==$codigo)?'selected':''; ?>
	            <option value="<?php echo $codigo?>"<?php echo $selected?>><?php echo $nombre?></option>
	            <?php } }else{?>
	            	<option value="0" >General</option>
	            <?php }?>
            </select>
        </td>
    </tr>
    <tr>
        <td align="left">Tel&eacute;fono:</td>
        <td><input type="text" name="phone" size="25" value="<?php echo $info['phone']?>">
             &nbsp;Ext.&nbsp;<input type="text" name="phone_ext" size="6" value="<?php echo $info['phone_ext']?>">
            &nbsp;<font class="error">&nbsp;<?php echo $errors['phone']?></font></td>
    </tr>
    <tr height=1px><td align="left" colspan=2 >&nbsp;</td></tr>
    <tr>
        <td align="left" valign="top">Fecha Vencimiento:</td>
        <td>
            <i>La hora depender&aacute; de su zona horaria (GM <?php echo $thisuser->getTZoffset()?>)</i>&nbsp;<font class="error">&nbsp;<?php echo $errors['time']?></font><br>
            <input id="duedate" name="duedate" value="<?php echo Format::htmlchars($info['duedate'])?>"
                onclick="event.cancelBubble=true;calendar(this);" autocomplete=OFF>
            <a href="#" onclick="event.cancelBubble=true;calendar(getObj('duedate')); return false;"><img src='images/cal.png'border=0 alt=""></a>
            &nbsp;&nbsp;
            <?php 
             $min=$hr=null;
             if($info['time'])
                list($hr,$min)=explode(':',$info['time']);
                echo Misc::timeDropdown($hr,$min,'time');
            ?>
            &nbsp;<font class="error">&nbsp;<?php echo $errors['duedate']?></font>
        </td>
    </tr>
    <?php 
      $sql='SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE.' ORDER BY priority_urgency DESC';
      if(($priorities=db_query($sql)) && db_num_rows($priorities)){ ?>
      <tr>
        <td align="left">Prioridad:</td>
        <td>
            <select name="pri">
              <?php 
                while($row=db_fetch_array($priorities)){ ?>
                    <option value="<?php echo $row['priority_id']?>" <?php echo $info['pri']==$row['priority_id']?'selected':''?> ><?php echo $row['priority_desc']?></option>
              <?php }?>
            </select>
        </td>
       </tr>
    <?php  }?>

    <?php 
    $services= db_query('SELECT topic_id,topic,isactive FROM '.TOPIC_TABLE.' ORDER BY topic');
    if($services && db_num_rows($services)){ ?>
    <tr>
        <td align="left" valign="top">Tema de Ayuda:</td>
        <td>
            <select name="topicId">    
                <option value="0" selected >Ninguno</option>
                <?php if(!$info['topicId'] && $info['topic']){ //old helptopic?>
                <option value="0" selected ><?php echo $info['topic']?> (eliminado)</option>
                <?php 
                }
                 while (list($topicId,$topic,$active) = db_fetch_row($services)){
                    $selected = ($info['topicId']==$topicId)?'selected':'';
                    $status=$active?'AKtiv':'Inactivo';
                    ?>
                    <option value="<?php echo $topicId?>"<?php echo $selected?>><?php echo $topic?>&nbsp;&nbsp;&nbsp;(<?php echo $status?>)</option>
                <?php 
                 }?>
            </select>
            &nbsp;(opcional)<font class="error">&nbsp;<?php echo $errors['topicId']?></font>
        </td>
    </tr>
    <?php 
    }?>
    <tr>
        <td align="left" valign="top"><b>Nota Interna:</b></td>
        <td>
            <i>Razon de Edici&oacute;n.</i><font class="error"><b>*&nbsp;<?php echo $errors['note']?></b></font><br/>
            <textarea name="note" cols="45" rows="5" wrap="soft"><?php echo $info['note']?></textarea></td>
    </tr>
    <tr height=2px><td align="left" colspan=2 >&nbsp;</td></tr>
    <tr>
        <td></td>
        <td>
            <input class="button" type="submit" name="submit_x" value="Actualizar Ticket">
            <input class="button" type="reset" value="Restablecer">
            <input class="button" type="button" name="cancel" value="Cancelar" onClick='window.location.href="tickets.php?id=<?php echo $ticket->getId()?>"'>    
        </td>
    </tr>
  </form>
</table>
