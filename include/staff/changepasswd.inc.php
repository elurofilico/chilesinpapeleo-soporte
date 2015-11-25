<?php 
if(!defined('OSTSCPINC') || !is_object($thisuser)) die('Kwaheri');
$rep=Format::htmlchars($rep);
?>
<div class="msg">Cambio de Contrase&ntilde;a</div>
<table width="100%" border="0" cellspacing=0 cellpadding=2>
    <form action="profile.php" method="post">
    <input type="hidden" name="t" value="passwd">
    <input type="hidden" name="id" value="<?php echo $thisuser->getId()?>">
    <tr>
        <td width="120">Contrase&ntilde;a Actual:</td>
        <td>
            <input type="password" name="password" AUTOCOMPLETE=OFF value="<?php echo $rep['password']?>">
            &nbsp;<font class="error">*&nbsp;<?php echo $errors['password']?></font></td>
    </tr>
    <tr>
        <td>Contrase&ntilde;a Nueva:</td>
        <td>
            <input type="password" name="npassword" AUTOCOMPLETE=OFF value="<?php echo $rep['npassword']?>">
            &nbsp;<font class="error">*&nbsp;<?php echo $errors['npassword']?></font></td>
    </tr>
    <tr>
        <td>Repita Contrase&ntilde;a:</td>
        <td>
            <input type="password" name="vpassword" AUTOCOMPLETE=OFF value="<?php echo $rep['vpassword']?>">
            &nbsp;<font class="error">*&nbsp;<?php echo $errors['vpassword']?></font></td>
    </tr>
    <tr><td >&nbsp;</td>
         <td><br/>
            <input class="button" type="submit" name="submit" value="Enviar">
            <input class="button" type="reset" name="reset" value="Restablecer">
            <input class="button" type="button" name="cancel" value="Cancelar" onClick='window.location.href="profile.php"'>
        </td>
    </tr>
    </form>
</table> 
