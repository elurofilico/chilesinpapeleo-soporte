<?php 
if(!defined('OSTADMININC') || basename($_SERVER['SCRIPT_NAME'])==basename(__FILE__)) die('El Sentido comun de las cosas '); //Say hi to our friend..
if(!$thisuser || !$thisuser->isadmin()) die('Acceso Denegado');

$info=($_POST && $errors)?Format::input($_POST):Format::htmlchars($cfg->getSMTPInfo());
?>
<div class="msg"><?php echo $title?></div>
<table width="98%" border="0" cellspacing=0 cellpadding=0>
<form action="admin.php?t=smtp" method="post">
 <input type="hidden" name="do" value="save">
 <input type="hidden" name="t" value="smtp">
 <tr><td>
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header"><td colspan=2>Configuraci&oacute;n de servidor SMTP (Opcional)</b></td></tr>
        <tr class="subheader"><td colspan=2>
         Al Activarlo, la cuenta de correo utilizara el servidor SMTP en lugar de la funci&oacute;n interna de correo de PHP para los correos salientes<br>
            <b>Por favor ten paciencia, el sistema intentara validarse en el servidor SMTP para verificar la informaci&oacute;n de acceso.</b></td></tr>
        <tr><th>Activar SMTP</th>
            <td>
                <input type="radio" name="isenabled"  value="1"   <?php echo $info['isenabled']?'checked':''?> /><b>Si</b>
                <input type="radio" name="isenabled"  value="0"   <?php echo !$info['isenabled']?'checked':''?> />No
                &nbsp;<font class="error">&nbsp;<?php echo $errors['isenabled']?></font>
            </td>
        </tr>
        <tr><th>Host SMTP</th>
            <td><input type="text" name="host" size=35 value="<?php echo $info['host']?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['host']?></font>
            </td>
        </tr>
        <tr><th>Puerto SMTP </th>
            <td><input type="text" name="port" size=6 value="<?php echo $info['port']?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['port']?></font>
            </td>
        </tr>
        <tr><th>Encriptaci&oacute;n</th>
            <td>
                 <input type="radio" name="issecure"  value="0"  
                    <?php echo !$info['issecure']?'checked':''?> />Ninguna
                 <input type="radio" name="issecure"  value="1"   
                    <?php echo $info['issecure']?'checked':''?> />TLS (segura)
                <font class="error">&nbsp;<?php echo $errors['issecure']?></font>
            </td>
        </tr>
        <tr><th>Nombre de Usuario</th>
            <td class="mainTableAlt"><input type="text" name="userid" size=35 value="<?php echo $info['userid']?>" autocomplete='off' >
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['userid']?></font>
            </td>
        </tr>
        <tr><th>Contrase&ntilde;a</th>
            <td><input type="password" name="userpass" size=35 value="<?php echo $info['userpass']?>" autocomplete='off'>
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['userpass']?></font>
            </td>
        </tr>
        <tr><th>Cuenta de Correo</th>
            <td>
                <input type="text" name="fromaddress" size=30 value="<?php echo $info['fromaddress']?>">
                    &nbsp;<font class="error">*&nbsp;<?php echo $errors['fromaddress']?></font>
            </td>
        </tr>
        <tr><th>Nombre de Email:</th>
            <td>
                <input type="text" name="fromname" size=30 value="<?php echo $info['fromname']?>">&nbsp;<font class="error">&nbsp;<?php echo $errors['fromname']?></font>
                &nbsp;&nbsp;(<i>Nombre del remitente opcional.</i>)
            </td>
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
