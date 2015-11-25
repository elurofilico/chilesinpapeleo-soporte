<?php 
if(!defined('OSTSCPINC') || !is_object($thisuser) || !$thisuser->isStaff()) die('Acceso Denegado');
$info=($_POST && $errors)?Format::input($_POST):array(); //on error...use the post data
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
<table width="80%" border="0" cellspacing=1 cellpadding=2>
   <form action="tickets.php" method="post" enctype="multipart/form-data">
    <input type='hidden' name='a' value='open'>
    <tr><td align="left" colspan=2>Rellena el formulario para abrir un Ticket Nuevo.</td></tr>
    <tr>
        <td align="left" nowrap width="20%"><b>Email:</b></td>
        <td>
            <input type="text" id="email" name="email" size="25" value="<?php echo $info['email']?>">
            &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['email']?></font>
            <?php  if($cfg->notifyONNewStaffTicket()) {?>
               &nbsp;&nbsp;&nbsp;
               <input type="checkbox" name="alertuser" <?php echo (!$errors || $info['alertuser'])? 'checked': ''?>>Enviar alerta al usuario.
            <?php }?>
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
        <td align="left">Tel&eacute;fono:</td>
        <td><input type="text" name="phone" size="25" value="<?php echo $info['phone']?>">
            &nbsp;Ext&nbsp;<input type="text" name="phone_ext" size="6" value="<?php echo $info['phone_ext']?>">
            <font class="error">&nbsp;<?php echo $errors['phone']?></font></td>
    </tr>
    <?php 
    $services= db_query('SELECT topic_id,topic FROM '.TOPIC_TABLE.' WHERE isactive=1 ORDER BY topic');
    if($services && db_num_rows($services)){ ?>
    <tr>
        <td align="left" valign="top">Institución:</td>
        <td>
            <select name="topicId">
                <option value="" selected >Seleccionar</option>
                <?php 
                 while (list($topicId,$topic) = db_fetch_row($services)){
                    $selected = ($info['topicId']==$topicId)?'selected':''; ?>
                    <option value="<?php echo $topicId?>"<?php echo $selected?>><?php echo $topic?></option>
                <?php 
                 }?>
            </select>
            <font class="error"><b>*</b>&nbsp;<?php echo $errors['topicId']?></font>
        </td>
    </tr>
    <?php 
    }?>
    <tr>
    	<td align="left">Área de ayuda</td>
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
        &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['servicio_codigo']?></font>
    	</td>
    </tr>
    <tr height=2px><td align="left" colspan=2 >&nbsp;</td></tr>
    <tr>
	<td align="left" ><b>Origen del Ticket:</b></td>
        <td align=2>
            <select name="source">
		<option value="" selected >Seleccionar</option>
                <option value="Phone" <?php echo ($info['source']=='Phone')?'selected':''?>>Tel&eacute;fono</option>
                <option value="Email" <?php echo ($info['source']=='Email')?'selected':''?>>Email</option>
                <option value="Other" <?php echo ($info['source']=='Other')?'selected':''?>>Otro</option>
            </select>
            &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['source']?></font>
        </td>
    </tr>
    <tr>
        <td align="left"><b>Asesor:</b></td>
        <td>
            <select name="deptId">
                <option value="" selected >Seleccionar</option>
                <?php 
                 $services= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name');
                 while (list($deptId,$dept) = db_fetch_row($services)){
                    $selected = ($info['deptId']==$deptId)?'selected':''; ?>
                    <option value="<?php echo $deptId?>"<?php echo $selected?>><?php echo $dept?></option>
                <?php 
                 }?>
            </select>
            &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['deptId']?></font>
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
        <td align="left" valign="top"><b>Resumen:</b></td>
        <td>
            <i>Visible al Usuario/Cliente.</i><font class="error"><b>*&nbsp;<?php echo $errors['issue']?></b></font><br/>
            <?php 
            $sql='SELECT premade_id,title FROM '.KB_PREMADE_TABLE.' WHERE isenabled=1';
            $canned=db_query($sql);
            if($canned && db_num_rows($canned)) {
            ?>
             Respuesta Predefinida:&nbsp;
              <select id="canned" name="canned"
                onChange="getCannedResponse(this.options[this.selectedIndex].value,this.form,'issue');this.selectedIndex='0';" >
                <option value="0" selected="selected">Seleccionar</option>
                <?php while(list($cannedId,$title)=db_fetch_row($canned)) { ?>
                <option value="<?php echo $cannedId?>" ><?php echo Format::htmlchars($title)?></option>
                <?php }?>
              </select>&nbsp;&nbsp;&nbsp;<label><input type='checkbox' value='1' name=append checked="true" />Anexar</label>
            <?php }?>
            <textarea name="issue" cols="55" rows="8" wrap="soft"><?php echo $info['issue']?></textarea></td>
    </tr>
    <?php if($cfg->canUploadFiles()) {
        ?>
    <tr>
        <td>Adjunto:</td>
        <td>
            <input type="file" name="attachment"><font class="error">&nbsp;<?php echo $errors['attachment']?></font>
        </td>
    </tr>
    <?php }?>
    <tr>
        <td align="left" valign="top">Nota Interna:</td>
        <td>
            <i>Nota interna (Opcional).</i><font class="error"><b>&nbsp;<?php echo $errors['note']?></b></font><br/>
            <textarea name="note" cols="55" rows="5" wrap="soft"><?php echo $info['note']?></textarea></td>
    </tr>

    <tr>
        <td align="left" valign="top">Fecha de Vencimiento:</td>
        <td>
            <i>La hora esta basada en tu zona horaria (GM <?php echo $thisuser->getTZoffset()?>)</i>&nbsp;<font class="error">&nbsp;<?php echo $errors['time']?></font><br>
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
                $info['pri']=$info['pri']?$info['pri']:$cfg->getDefaultPriorityId();
                while($row=db_fetch_array($priorities)){ ?>
                    <option value="<?php echo $row['priority_id']?>" <?php echo $info['pri']==$row['priority_id']?'selected':''?> ><?php echo $row['priority_desc']?></option>
              <?php }?>
            </select>
        </td>
       </tr>
    <?php  }?>
    <tr>
        <td>Asignar:</td>
        <td>
            <select id="staffId" name="staffId">
                <option value="0" selected="selected">-Asignar a-</option>
                <?php 
                    //TODO: make sure the user's group is also active....DO a join.
                    $sql=' SELECT staff_id,CONCAT_WS(", ",lastname,firstname) as name FROM '.STAFF_TABLE.' WHERE isactive=1 AND onvacation=0 ';
                    $depts= db_query($sql.' ORDER BY lastname,firstname ');
                    while (list($staffId,$staffName) = db_fetch_row($depts)){
                        $selected = ($info['staffId']==$staffId)?'selected':''; ?>
                        <option value="<?php echo $staffId?>"<?php echo $selected?>><?php echo $staffName?></option>
                    <?php 
                    }?>
            </select><font class='error'>&nbsp;<?php echo $errors['staffId']?></font>
                &nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="alertstaff" <?php echo (!$errors || $info['alertstaff'])? 'checked': ''?>>Enviar alerta al Staff asignado.
        </td>
    </tr>
    <tr>
        <td>Firma:</td>
        <td> <?php 
            $appendStaffSig=$thisuser->appendMySignature();
            $info['signature']=!$info['signature']?'none':$info['signature']; //change 'none' to 'mine' to default to staff signature.
            ?>
            <div style="margin-top: 2px;">
                <label><input type="radio" name="signature" value="none" checked > Ninguna</label>
                <?php if($appendStaffSig) {?>
                    <label> <input type="radio" name="signature" value="mine" <?php echo $info['signature']=='mine'?'checked':''?> > Firma Personal</label>
                 <?php }?>
                 <label><input type="radio" name="signature" value="dept" <?php echo $info['signature']=='dept'?'checked':''?> > Firma del Dpto (si existe)</label>
            </div>
        </td>
    </tr>
    <tr height=2px><td align="left" colspan=2 >&nbsp;</td></tr>
    <tr>
        <td></td>
        <td>
            <input class="button" type="submit" name="submit_x" value="Enviar Ticket">
            <input class="button" type="reset" value="Restablecer">
            <input class="button" type="button" name="Abbrechen" value="Cancelar" onClick='window.location.href="tickets.php"'>    
        </td>
    </tr>
  </form>
</table>
<script type="text/javascript">
    
    var options = {
        script:"ajax.php?api=tickets&f=searchbyemail&limit=10&",
        varname:"input",
        json: true,
        shownoresults:false,
        maxresults:10,
        callback: function (obj) { document.getElementById('email').value = obj.id; document.getElementById('name').value = obj.info; return false;}
    };
    var autosug = new bsn.AutoSuggest('email', options);
</script>
