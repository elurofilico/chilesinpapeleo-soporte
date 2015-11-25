<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');
$info=null;
if($dept && $_REQUEST['a']!='new'){
    //Editing Department.
    $title='Actualizar Departamento';
    $action='update';
    $info=$dept->getInfo();
}else {
    $title='Nuevo Departamento';
    $action='create';
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $info['ticket_auto_response']=isset($info['ticket_auto_response'])?$info['ticket_auto_response']:1;
    $info['message_auto_response']=isset($info['message_auto_response'])?$info['message_auto_response']:1;
}
$info=($errors && $_POST)?Format::input($_POST):Format::htmlchars($info);

?>
<div class="msg"><?php echo $title?></div>
<table width="100%" border="0" cellspacing=0 cellpadding=0>
 <form action="admin.php?t=dept&id=<?php echo $info['dept_id']?>" method="POST" name="dept">
 <input type="hidden" name="do" value="<?php echo $action?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a'])?>">
 <input type="hidden" name="t" value="dept">
 <input type="hidden" name="dept_id" value="<?php echo $info['dept_id']?>">
 <tr><td>
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header"><td colspan=2>Departamento</td></tr>
        <tr class="subheader"><td colspan=2 >Los departamentos dependen de la configuracion del correo y de los temas de soporte para los tickets entrantes.</td></tr>
        <tr><th>Nombre  Departamento:</th>
            <td><input type="text" name="dept_name" size=25 value="<?php echo $info['dept_name']?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['dept_name']?></font>
                    
            </td>
        </tr>
        <tr>
            <th>Email Departamento:</th>
            <td>
                <select name="email_id">
                    <option value="">Seleccionar</option>
                    <?php 
                    $emails=db_query('SELECT email_id,email,name,smtp_active FROM '.EMAIL_TABLE);
                    while (list($id,$email,$name,$smtp) = db_fetch_row($emails)){
                        $email=$name?"$name &lt;$email&gt;":$email;
                        if($smtp)
                            $email.=' (SMTP)';
                        ?>
                     <option value="<?php echo $id?>"<?php echo ($info['email_id']==$id)?'selected':''?>><?php echo $email?></option>
                    <?php 
                    }?>
                 </select>
                 &nbsp;<font class="error">*&nbsp;<?php echo $errors['email_id']?></font>&nbsp;(Email de Salida)
            </td>
        </tr>    
        <?php  if($info['dept_id']) { //update 
            $users= db_query('SELECT staff_id,CONCAT_WS(" ",firstname,lastname) as name FROM '.STAFF_TABLE.' WHERE dept_id='.db_input($info['dept_id']));
            ?>
        <tr>
            <th>Manager Departamento:</th>
            <td>
                <?php if($users && db_num_rows($users)) {?>
                <select name="manager_id">
                    <option value=0 >-------ninguno-------</option>
                    <option value=0 disabled >Seleccionar (opcional)</option>
                     <?php 
                     while (list($id,$name) = db_fetch_row($users)){ ?>
                        <option value="<?php echo $id?>"<?php echo ($info['manager_id']==$id)?'selected':''?>><?php echo $name?></option>
                     <?php }?>
                     
                </select>
                 <?php }else {?>
                       Sin Usuarios (Agregar Usuario)
                       <input type="hidden" name="manager_id"  value="0" />
                 <?php }?>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['manager_id']?></font>
            </td>
        </tr>
        <?php }?>
        <tr><th>Estado</th>
            <td>
                <input type="radio" name="ispublic"  value="1"   <?php echo $info['ispublic']?'checked':''?> />Publico
                <input type="radio" name="ispublic"  value="0"   <?php echo !$info['ispublic']?'checked':''?> />Privado
                &nbsp;<font class="error"><?php echo $errors['ispublic']?></font>
            </td>
        </tr>
        <tr>
            <th valign="top"><br/>Firma Departamento:</th>
            <td>
                <i>Obligatoria si el departamento es Publico</i>&nbsp;&nbsp;&nbsp;<font class="error"><?php echo $errors['dept_signature']?></font><br/>
                <textarea name="dept_signature" cols="21" rows="5" style="width: 60%;"><?php echo $info['dept_signature']?></textarea>
                <br>
                <input type="checkbox" name="can_append_signature" <?php echo $info['can_append_signature'] ?'checked':''?> > 
                Puede ser agregada a las respuestas (disponible como opci&oacute;n para departamentos publicos)  
            </td>
        </tr>
        <tr><th>Plantillas de Emails:</th>
            <td>
                <select name="tpl_id">
                    <option value=0 disabled>Seleccionar</option>
                    <option value="0" selected="selected">Est&aacute;ndar del Sistema</option>
                    <?php 
                    $templates=db_query('SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_TABLE.' WHERE tpl_id!='.db_input($cfg->getDefaultTemplateId()));
                    while (list($id,$name) = db_fetch_row($templates)){
                        $selected = ($info['tpl_id']==$id)?'SELECTED':''; ?>
                        <option value="<?php echo $id?>"<?php echo $selected?>><?php echo Format::htmlchars($name)?></option>
                    <?php 
                    }?>
                </select><font class="error">&nbsp;<?php echo $errors['tpl_id']?></font><br/>
                <i>Utilizado para correos salientes, alertas y notificaciones para usuarios y Staff.</i>
            </td>
        </tr>
        <tr class="header"><td colspan=2>Auto-Respuesta</td></tr>
        <tr class="subheader"><td colspan=2>
            La opci&oacute;n de auto-respuesta en la secci&oacute;n de preferencias tiene que estar habilitada para que tenga efecto en el departamento.
            </td>
        </tr>
        <tr><th>Nuevo Ticket:</th>
            <td>
                <input type="radio" name="ticket_auto_response"  value="1"   <?php echo $info['ticket_auto_response']?'checked':''?> />Habilitar
                <input type="radio" name="ticket_auto_response"  value="0"   <?php echo !$info['ticket_auto_response']?'checked':''?> />Deshabilitar
            </td>
        </tr>
        <tr><th>Nuevo Mensaje:</th>
            <td>
                <input type="radio" name="message_auto_response"  value="1"   <?php echo $info['message_auto_response']?'checked':''?> />Habilitar
                <input type="radio" name="message_auto_response"  value="0"   <?php echo !$info['message_auto_response']?'checked':''?> />Deshabilitar
            </td>
        </tr>
        <tr>
            <th>Email de Auto-Respuesta:</th>
            <td>
                <select name="autoresp_email_id">
                    <option value="0" disabled>Seleccionar</option>
                    <option value="0" selected="selected">Email Dpto. (arriba)</option>
                    <?php 
                    $emails=db_query('SELECT email_id,email,name,smtp_active FROM '.EMAIL_TABLE.' WHERE email_id!='.db_input($info['email_id']));
                    if($emails && db_num_rows($emails)) {
                        while (list($id,$email,$name,$smtp) = db_fetch_row($emails)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            if($smtp)
                                $email.=' (SMTP)';
                            ?>
                            <option value="<?php echo $id?>"<?php echo ($info['autoresp_email_id']==$id)?'selected':''?>><?php echo $email?></option>
                        <?php 
                        }
                    }?>
                 </select>
                 &nbsp;<font class="error">&nbsp;<?php echo $errors['autoresp_email_id']?></font>&nbsp;<br/>
                 <i>Email utilizado para el envi&oacute; de respuestas autom&aacute;ticas, si esta habilitado.</i>
            </td>
        </tr>
    </table>
    </td></tr>
    <tr><td style="padding:10px 0 10px 200px;">
        <input class="button" type="submit" name="submit" value="Guardar">
        <input class="button" type="reset" name="reset" value="Restablecer">
        <input class="button" type="button" name="cancel" value="Cancelar" onClick='window.location.href="admin.php?t=dept"'>
    </td></tr>
    </form>
</table>
