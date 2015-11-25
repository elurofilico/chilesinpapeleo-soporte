<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');

$rep=null;
$newuser=true;
if($staff && $_REQUEST['a']!='new'){
    $rep=$staff->getInfo();
    $title='Editar Datos de: '.$rep['firstname'].' '.$rep['lastname'];
    $action='update';
    $pwdinfo='Para restablecer la contrase&ntilde;a escriba una nueva a continuaci&oacute;n';
    $newuser=false;
}else {
    $title='A&ntilde;adir Nuevo Miembro';
    $pwdinfo='Contrase&ntilde;a temporal (obligatoria)';
    $action='create';
    $rep['resetpasswd']=isset($rep['resetpasswd'])?$rep['resetpasswd']:1;
    $rep['isactive']=isset($rep['isactive'])?$rep['isactive']:1;
    $rep['dept_id']=$rep['dept_id']?$rep['dept_id']:$_GET['dept'];
    $rep['isvisible']=isset($rep['isvisible'])?$rep['isvisible']:1;
}
$rep=($errors && $_POST)?Format::input($_POST):Format::htmlchars($rep);

//get the goodies.
$groups=db_query('SELECT group_id,group_name FROM '.GROUP_TABLE);
$depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE);

?>
<div class="msg"><?php echo $title?></div>
<table width="100%" border="0" cellspacing=0 cellpadding=0>
<form action="admin.php" method="post">
 <input type="hidden" name="do" value="<?php echo $action?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a'])?>">
 <input type="hidden" name="t" value="staff">
 <input type="hidden" name="staff_id" value="<?php echo $rep['staff_id']?>">
 <tr><td>
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header"><td colspan=2>Cuenta de Usuario</td></tr>
        <tr class="subheader"><td colspan=2>Informaci&oacute;n de la Cuenta</td></tr>
        <tr>
            <th>Nombre:</th>
            <td><input type="text" name="username" value="<?php echo $rep['username']?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['username']?></font></td>
        </tr>
        <tr>
            <th>Departamento:</th>
            <td>
                <select name="dept_id">
                    <option value=0>Seleccionar</option>
                    <?php 
                    while (list($id,$name) = db_fetch_row($depts)){
                        $selected = ($rep['dept_id']==$id)?'selected':''; ?>
                        <option value="<?php echo $id?>"<?php echo $selected?>>Departamento <?php echo $name?></option>
                    <?php 
                    }?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['dept']?></font>
            </td>
        </tr>
        <tr>
            <th>Grupo de Usuarios:</th>
            <td>
                <select name="group_id">
                    <option value=0>Seleccionar</option>
                    <?php 
                    while (list($id,$name) = db_fetch_row($groups)){
                        $selected = ($rep['group_id']==$id)?'selected':''; ?>
                        <option value="<?php echo $id?>"<?php echo $selected?>><?php echo $name?></option>
                    <?php 
                    }?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['group']?></font>
            </td>
        </tr>
        <tr>
            <th>Nombre y Apellidos:</th>
            <td>
                <input type="text" name="firstname" value="<?php echo $rep['firstname']?>">&nbsp;<font class="error">*</font>
                &nbsp;&nbsp;&nbsp;<input type="text" name="lastname" value="<?php echo $rep['lastname']?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['name']?></font></td>
        </tr>
        <tr>
            <th>Email:</th>
            <td><input type="text" name="email" size=25 value="<?php echo $rep['email']?>">
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['email']?></font></td>
        </tr>
        <tr>
            <th>Tel&eacute;fono:</th>
            <td>
                <input type="text" name="phone" value="<?php echo $rep['phone']?>" >&nbsp;Ext.&nbsp;
                <input type="text" name="phone_ext" size=6 value="<?php echo $rep['phone_ext']?>" >
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['phone']?></font></td>
        </tr>
        <tr>
            <th>Movil:</th>
            <td>
                <input type="text" name="mobile" value="<?php echo $rep['mobile']?>" >
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['mobile']?></font></td>
        </tr>
        <tr>
            <th valign="top">Firma:</th>
            <td><textarea name="signature" cols="21" rows="5" style="width: 60%;"><?php echo $rep['signature']?></textarea></td>
        </tr>
        <tr>
            <th>Contrase&ntilde;a:</th>
            <td>
                <i><?php echo $pwdinfo?></i>&nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['npassword']?></font> <br/>
                <input type="password" name="npassword" AUTOCOMPLETE=OFF >&nbsp;
            </td>
        </tr>
        <tr>
            <th>Confirmar Contrase&ntilde;a:</th>
            <td class="mainTableAlt"><input type="password" name="vpassword" AUTOCOMPLETE=OFF >
                &nbsp;<font class="error">&nbsp;<?php echo $errors['vpassword']?></font></td>
        </tr>
        <tr>
            <th>Forzar Cambio de Contrase&ntilde;a:</th>
            <td>
                <input type="checkbox" name="resetpasswd" <?php echo $rep['resetpasswd'] ? 'checked': ''?>>Requerir cambio de contraseña en el siguiente inicio de sesi&oacute;n</td>
        </tr>
        <tr class="header"><td colspan=2>Estado y Configuraci&oacute;n de Permisos</td></tr>
        <tr class="subheader">
          <td colspan=2>
            Los permisos para los miembros del Staff estan tambi&eacute;n basados en la asignaci&oacute;n de grupo.<br> <b>El Administrador no se ve afectado por los permisos del grupo.</b></td>
        </tr> 
        <tr><th><b>Estado de la Cuenta</b></th>
            <td>
                        <input type="radio" name="isactive"  value="1" <?php echo $rep['isactive']?'checked':''?> /><b>Activa</b>
                        <input type="radio" name="isactive"  value="0" <?php echo !$rep['isactive']?'checked':''?> /><b>Bloqueada</b>
                        &nbsp;&nbsp;
            </td>
        </tr>
        <tr><th><b>Tipo de Cuenta</b></th>
            <td class="mainTableAlt">
                        <input type="radio" name="isadmin"  value="1" <?php echo $rep['isadmin']?'checked':''?> /><font color="red"><b>Administradores</b></font>
                        <input type="radio" name="isadmin"  value="0" <?php echo !$rep['isadmin']?'checked':''?> /><b>Miembros del Staff</b>
                        &nbsp;&nbsp;
            </td>
        </tr>
        <tr><th>Mostrar en el Listado</th>
            <td>
               <input type="checkbox" name="isvisible" <?php echo $rep['isvisible'] ? 'checked': ''?>>Mostrar miembro en el directorio del Staff
            </td>
        </tr>
        <tr><th>Modo Vacaciones</th>
            <td class="mainTableAlt">
             <input type="checkbox" name="onvacation" <?php echo $rep['onvacation'] ? 'checked': ''?>>
                Miembro en modo vacaciones. (<i>No asignarle Tickets o Alertas</i>)
                &nbsp;<font class="error">&nbsp;<?php echo $errors['vacation']?></font>
            </td>
        </tr>
    </table>
   </td></tr>
   <tr><td style="padding:5px 0 10px 210px;">
        <input class="button" type="submit" name="submit" value="Guardar">
        <input class="button" type="reset" name="reset" value="Restablecer">
        <input class="button" type="button" name="cancel" value="Cancelar" onClick='window.location.href="admin.php?t=staff"'>
    </td></tr>
  </form>
</table>
