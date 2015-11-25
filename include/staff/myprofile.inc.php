<?php 
if(!defined('OSTSCPINC') || !is_object($thisuser)) die('ElArteDeGanar.com');

?>
<div class="msg">Mi Perfil</div>
<table width="100%" border="0" cellspacing=0 cellpadding=2>
 <form action="profile.php" method="post">
 <input type="hidden" name="t" value="info">
 <input type="hidden" name="id" value="<?php echo $thisuser->getId()?>">
    <tr>
        <td width="110"><b>Usuario:</b></td>
        <td>&nbsp;<?php echo $thisuser->getUserName()?></td>
    </tr>
    <tr>
        <td>Nombre:</td>
        <td><input type="text" name="firstname" value="<?php echo $rep['firstname']?>">
            &nbsp;<font class="error">*&nbsp;<?php echo $errors['firstname']?></font></td>
    </tr>
    <tr>
        <td>Apellidos:</td>
        <td><input type="text" name="lastname" value="<?php echo $rep['lastname']?>">
            &nbsp;<font class="error">*&nbsp;<?php echo $errors['lastname']?></font></td>
    </tr>
    <tr>
        <td>Email:</td>
        <td><input type="text" name="email" size=25 value="<?php echo $rep['email']?>">
            &nbsp;<font class="error">*&nbsp;<?php echo $errors['email']?></font></td>
    </tr>
    <tr>
        <td>Tel&eacute;fono:</td>
        <td>
            <input type="text" name="phone" value="<?php echo $rep['phone']?>" ><font class="error">&nbsp;<?php echo $errors['phone']?></font>&nbsp;Ext&nbsp;
            <input type="text" name="phone_ext" size=6 value="<?php echo $rep['phone_ext']?>" >
            <font class="error">&nbsp;<?php echo $errors['phone_ext']?></font>
        </td>
    </tr>
    <tr>
        <td>Movil:</td>
        <td><input type="text" name="mobile" value="<?php echo $rep['mobile']?>" >
            &nbsp;<font class="error">&nbsp;<?php echo $errors['mobile']?></font></td>
    </tr>
    <tr>
        <td valign="top">Firma:</td>
        <td><textarea name="signature" cols="21" rows="5" style="width: 60%;"><?php echo $rep['signature']?></textarea></td>
    </tr>
    <tr><td>&nbsp;</td>
        <td> <br/>
            <input class="button" type="submit" name="submit" value="Guardar">
            <input class="button" type="reset" name="reset" value="Restablecer">
            <input class="button" type="button" name="cancel" value="Cancelar" onClick='window.location.href="index.php"'>
        </td>
    </tr>
 </form>
</table> 
