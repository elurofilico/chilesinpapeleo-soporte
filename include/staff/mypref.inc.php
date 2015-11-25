<?php 
if(!defined('OSTSCPINC') || !is_object($thisuser) || !$rep) die('Por la Causa');
?>
<div class="msg">&nbsp;Mis Preferencias</div>
<table width="100%" border="0" cellspacing=2 cellpadding=3>
 <form action="profile.php" method="post">
 <input type="hidden" name="t" value="pref">
 <input type="hidden" name="id" value="<?php echo $thisuser->getId()?>">
    <tr>
        <td width="145" nowrap>Tama&ntilde;o m&aacute;ximo de p&aacute;gina:</td>        
        <td>
            <select name="max_page_size">
                <?php 
                $pagelimit=$rep['max_page_size']?$rep['max_page_size']:$cfg->getPageSize();
                for ($i = 5; $i <= 50; $i += 5) {?>
                    <option <?php echo $pagelimit== $i ? 'SELECTED':''?>><?php echo $i?></option>
                <?php }?>
            </select> Tickets/Art&iacute;culos por p&aacute;gina.
        </td>
    </tr>
    <tr>
        <td nowrap>Refrescar P&aacute;gina:</td>
    <td>
            <input type="input" size=3 name="auto_refresh_rate" value="<?php echo $rep['auto_refresh_rate']?>">
          (<i>Cada cuantos minutos se refrescara la p&aacute;gina de Tickets. Entrar 0 para deshabilitar</i>)
        </td>
    </tr>
    <tr>
        <td nowrap>Zona Horaria Preferida:</td>
        <td>
            <select name="timezone_offset">
                <?php 
                $gmoffset  = date("Z") / 3600; //Server's offset.
                $currentoffset = ($rep['timezone_offset']==NULL)?$cfg->getTZOffset():$rep['timezone_offset'];
                echo"<option value=\"$gmoffset\">Hora del Servidor (GMT $gmoffset:00)</option>"; //Default if all fails.
                $timezones= db_query('SELECT offset,timezone FROM '.TIMEZONE_TABLE);
                while (list($offset,$tz) = db_fetch_row($timezones)){
                    $selected = ($currentoffset==$offset) ?'SELECTED':'';
                    $tag=($offset)?"GMT $offset ($tz)":" GMT ($tz)"; ?>
                    <option value="<?php echo $offset?>"<?php echo $selected?>><?php echo $tag?></option>
                <?php }?>
            </select>
        </td>
    </tr>
    <tr>
        <td>Horario de Verano:</td>
        <td>
            <input type="checkbox" name="daylight_saving" <?php echo $rep['daylight_saving'] ? 'checked': ''?>>Habilitar horario de verano
        </td>
    </tr>
   <tr><td>Hora Actual:</td>
        <td><b><i><?php echo Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$rep['timezone_offset'],$rep['daylight_saving'])?></i></b></td>
    </tr>  
    <tr>
        <td>&nbsp;</td>
        <td><br>
            <input class="button" type="submit" name="submit" value="Guardar">
            <input class="button" type="reset" name="reset" value="Restablecer">
            <input class="button" type="button" name="cancel" value="Cancelar" onClick='window.location.href="profile.php"'>
        </td>
    </tr>
 </form>
</table>
