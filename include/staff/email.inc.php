<?php 
if(!defined('OSTADMININC') || basename($_SERVER['SCRIPT_NAME'])==basename(__FILE__)) die('ElArteDeGanar.com'); //Say hi to our friend..
if(!$thisuser || !$thisuser->isadmin()) die('Acceso Denegado');

$info=($_POST && $errors)?$_POST:array(); //Re-use the post info on error...savekeyboards.org
if($email && $_REQUEST['a']!='new'){
    $title='Editar Cuenta de Correo'; 
    $action='update';
    if(!$info) {
        $info=$email->getInfo();
        $info['userpass']=$info['userpass']?Misc::decrypt($info['userpass'],SECRET_SALT):'';
    }
    $qstr='?t=email&id='.$email->getId();
}else {
   $title='A&ntilde;adir Nueva Cuenta de Correo';
   $action='create';
   $info['smtp_auth']=isset($info['smtp_auth'])?$info['smtp_auth']:1;
}

$info=Format::htmlchars($info);
//get the goodies.
$depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE);
$priorities= db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
?>
<div class="msg"><?php echo $title?></div>
<table width="100%" border="0" cellspacing=0 cellpadding=0>
<form action="admin.php<?php echo $qstr?>" method="post">
 <input type="hidden" name="do" value="<?php echo $action?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a'])?>">
 <input type="hidden" name="t" value="email">
 <input type="hidden" name="email_id" value="<?php echo $info['email_id']?>">
 <tr><td>
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header"><td colspan=2>Informaci&oacute;n de la Cuenta</td></tr>
        <tr class="subheader">
            <td colspan=2 >Los ajustes son principalmente para los tickets enviados por correo electr&oacute;nico.</td>
        </tr>
        <tr>
            <th>Cuenta de Correo:</th>
            <td>
                <input type="text" name="email" size=30 value="<?php echo $info['email']?>">&nbsp;<font class="error">*&nbsp;<?php echo $errors['email']?></font>
            </td>
        </tr>
        <tr><th>Nombre Remitente:</th>
            <td>
                <input type="text" name="name" size=30 value="<?php echo $info['name']?>">&nbsp;<font class="error">&nbsp;<?php echo $errors['name']?></font>
                &nbsp;&nbsp;(<i>Opcional</i>)
            </td>
        </tr>
        <tr>
            <th>Prioridad Tickets nuevos:</th>
            <td>
                <select name="priority_id">
                    <option value=0>Seleccionar</option>
                    <?php 
                    while (list($id,$name) = db_fetch_row($priorities)){
                        $selected = ($info['priority_id']==$id)?'selected':''; ?>
                        <option value="<?php echo $id?>"<?php echo $selected?>><?php echo $name?></option>
                    <?php 
                    }?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['priority_id']?></font>
            </td>
        </tr>
        <tr>
            <th>Dpto. Ticket Nuevo:</th>
            <td>
                <select name="dept_id">
                    <option value=0>Seleccionar</option>
                    <?php 
                    while (list($id,$name) = db_fetch_row($depts)){
                        $selected = ($info['dept_id']==$id)?'selected':''; ?>
                        <option value="<?php echo $id?>"<?php echo $selected?>>Dpto - <?php echo $name?></option>
                    <?php 
                    }?>
                </select>&nbsp;<font class="error">&nbsp;<?php echo $errors['dept_id']?></font>&nbsp;
            </td>
        </tr>
        <tr>
            <th>Respuesta Automatica:</th>
            <td>
                <input type="checkbox" name="noautoresp" value=1 <?php echo $info['noautoresp']? 'checked': ''?> >
                <b>Deshabilitar</b> Resp. Automatica para esta cuenta. 
                &nbsp;(<i>esto sobrescribe la configuraci&oacute;n del departamento</i>)
            </td>
        </tr>
        <tr class="subheader">
            <td colspan=2 ><b>Datos de Acceso (opcional) </b> requerida cuando IMAP/POP y/o SMTP estan activados.</td>
        </tr>
        <tr><th>Usuario</th>
            <td><input type="text" name="userid" size=35 value="<?php echo $info['userid']?>" autocomplete='off' >
                &nbsp;<font class="error">&nbsp;<?php echo $errors['userid']?></font>
            </td>
        </tr>
        <tr><th>Contrase&ntilde;a</th>
            <td>
               <input type="password" name="userpass" size=35 value="<?php echo $info['userpass']?>" autocomplete='off'>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['userpass']?></font>
            </td>
        </tr>
        <tr class="header">
          <td colspan=2>Ajustes de la cuenta para la captura de correo (Opcional)</b></td></tr>
        <tr class="subheader"><td colspan=2>
             Opciones para captura de correo entrante. La captura de correo tiene que estar habilitada con el autocron activo o mediante un "cron" externo (Email Fetch)<br>
            <b>Ten paciencia... El sistema intentara validarse en el servidor de correo para verificar la informaci&oacute;n de acceso</b>
            <font class="error">&nbsp;<?php echo $errors['mail']?></font></td></tr>
        <tr><th>Estado</th>
            <td>
                <label><input type="radio" name="mail_active"  value="1"   <?php echo $info['mail_active']?'checked':''?> />Habilitar</label>
                <label><input type="radio" name="mail_active"  value="0"   <?php echo !$info['mail_active']?'checked':''?> />Deshabilitar</label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_active']?></font>
            </td>
        </tr>
        <tr><th>Host</th>
            <td><input type="text" name="mail_host" size=35 value="<?php echo $info['mail_host']?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_host']?></font>
            </td>
        </tr>
        <tr><th>Puerto</th>
            <td><input type="text" name="mail_port" size=6 value="<?php echo $info['mail_port']?$info['mail_port']:''?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_port']?></font>
            </td>
        </tr>
        <tr><th>Protocolo</th>
            <td>
                <select name="mail_protocol">
                    <option value='POP'>Seleccionar</option>
                    <option value='POP' <?php echo ($info['mail_protocol']=='POP')?'selected="selected"':''?> >POP</option>
                    <option value='IMAP' <?php echo ($info['mail_protocol']=='IMAP')?'selected="selected"':''?> >IMAP</option>
                </select>
                <font class="error">&nbsp;<?php echo $errors['mail_protocol']?></font>
            </td>
        </tr>

        <tr><th>Encriptaci&oacute;n</th>
            <td>
                 <label><input type="radio" name="mail_encryption"  value="NONE"
                    <?php echo ($info['mail_encryption']!='SSL')?'checked':''?> />Ninguna</label>
                 <label><input type="radio" name="mail_encryption"  value="SSL"
                    <?php echo ($info['mail_encryption']=='SSL')?'checked':''?> />SSL</label>
                <font class="error">&nbsp;<?php echo $errors['mail_encryption']?></font>
            </td>
        </tr>
        <tr><th>Frecuencia de captura</th>
            <td>
                <input type="text" name="mail_fetchfreq" size=4 value="<?php echo $info['mail_fetchfreq']?$info['mail_fetchfreq']:''?>"> Intervalo de demora en minutos
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_fetchfreq']?></font>
            </td>
        </tr>
        <tr><th>Correos por captura</th>
            <td>
                <input type="text" name="mail_fetchmax" size=4 value="<?php echo $info['mail_fetchmax']?$info['mail_fetchmax']:''?>"> Numero m&aacute;ximo de correos por captura.
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_fetchmax']?></font>
            </td>
        </tr>
        <tr><th>Eliminar Mensajes</th>
            <td>
                <input type="checkbox" name="mail_delete" value=1 <?php echo $info['mail_delete']? 'checked': ''?> >
                    Eliminar mensajes capturados (<i>recomendado cuando se utiliza POP</i>)
                &nbsp;<font class="error">&nbsp;<?php echo $errors['mail_delete']?></font>
            </td>
        </tr>
        <tr class="header"><td colspan=2>Configuraci&oacute;n SMTP (Opcional)</b></td></tr>
        <tr class="subheader"><td colspan=2>
             Al configurarlo, la cuenta de correo utilizara el servidor SMTP en lugar de la funci&oacute;n interna de correo  PHP para los correos salientes<br>
            <b>Ten paciencia, el sistema intentara validarse en el servidor SMTP para verificar la informaci&oacute;n de acceso.</b>
                <font class="error">&nbsp;<?php echo $errors['smtp']?></font></td></tr>
        <tr><th>Estado</th>
            <td>
                <label><input type="radio" name="smtp_active"  value="1"   <?php echo $info['smtp_active']?'checked':''?> />Habilitar</label>
                <label><input type="radio" name="smtp_active"  value="0"   <?php echo !$info['smtp_active']?'checked':''?> />Deshabilitar</label>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_active']?></font>
            </td>
        </tr>
        <tr><th>Host SMTP</th>
            <td><input type="text" name="smtp_host" size=35 value="<?php echo $info['smtp_host']?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_host']?></font>
            </td>
        </tr>
        <tr><th>Puerto SMTP</th>
            <td><input type="text" name="smtp_port" size=6 value="<?php echo $info['smtp_port']?$info['smtp_port']:''?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['smtp_port']?></font>
            </td>
        </tr>
        <tr><th>&iquest;Requiere autenticaci&oacute;n?</th>
            <td>

                 <label><input type="radio" name="smtp_auth"  value="1"
                    <?php echo $info['smtp_auth']?'checked':''?> />Si</label>
                 <label><input type="radio" name="smtp_auth"  value="0"
                    <?php echo !$info['smtp_auth']?'checked':''?> />No</label>
                <font class="error">&nbsp;<?php echo $errors['smtp_auth']?></font>
            </td>
        </tr>
        <tr><th>Encriptaci&oacute;n</th>
            <td>El mejor m&eacute;todo de autenticaci&oacute;n disponibles es auto-seleccionado en base a lo que tu servidor soporte.</td>
        </tr>
    </table>
   </td></tr>
   <tr><td style="padding:10px 0 10px 220px;">
            <input class="button" type="submit" name="submit" value="Guardar">
            <input class="button" type="reset" name="reset" value="Restablecer">
            <input class="button" type="button" name="cancel" value="Cancelar" onClick='window.location.href="admin.php?t=email"'>
        </td>
     </tr>
</form>
</table>
