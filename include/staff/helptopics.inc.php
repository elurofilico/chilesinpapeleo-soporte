<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');

//List all help topics
$sql='SELECT topic_id,isactive,topic.noautoresp,topic.dept_id,topic,dept_name,priority_desc,topic.created,topic.updated FROM '.TOPIC_TABLE.' topic '.
     ' LEFT JOIN '.DEPT_TABLE.' dept ON dept.dept_id=topic.dept_id '.
     ' LEFT JOIN '.TICKET_PRIORITY_TABLE.' pri ON pri.priority_id=topic.priority_id ';
$services=db_query($sql.' ORDER BY topic'); 
?>
<div class="msg">Temas de Ayuda</div>
<table width="100%" border="0" cellspacing=1 cellpadding=2>
   <form action="admin.php?t=settings" method="POST" name="topic" onSubmit="return checkbox_checker(document.forms['topic'],1,0);">
   <input type='hidden' name='t' value='topics'>
   <input type=hidden name='do' value='mass_process'>
   <tr><td>
    <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
        <tr>
	        <th width="7px">&nbsp;</th>
	        <th>Temas de Ayuda</th>
            <th>Estado</th>
            <th>Auto-Respuesta</th>
            <th>Departamento</th>
            <th>Prioridad</th>
	        <th>ULtima Actualizaci&oacute;n</th>
        </tr>
        <?php 
        $class = 'row1';
        $total=0;
        $ids=($errors && is_array($_POST['tids']))?$_POST['tids']:null;
        if($services && db_num_rows($services)):
            while ($row = db_fetch_array($services)) {
                $sel=false;
                if(($ids && in_array($row['topic_id'],$ids)) or ($row['topic_id']==$topicID)){
                    $class="$class highlight";
                    $sel=true;
                }
                ?>
            <tr class="<?php echo $class?>" id="<?php echo $row['topic_id']?>">
                <td width=7px>
                 <input type="checkbox" name="tids[]" value="<?php echo $row['topic_id']?>" <?php echo $sel?'checked':''?>  onClick="highLight(this.value,this.checked);">
                <td><a href="admin.php?t=topics&id=<?php echo $row['topic_id']?>"><?php echo Format::htmlchars(Format::truncate($row['topic'],30))?></a></td>
                <td><?php echo $row['isactive']?'Activo':'<b>Deshabilitado</b>'?></td>
                <td>&nbsp;&nbsp;<?php echo $row['noautoresp']?'No':'<b>Si</b>'?></td>
                <td><a href="admin.php?t=dept&id=<?php echo $row['dept_id']?>"><?php echo $row['dept_name']?></a></td>
                <td><?php echo $row['priority_desc']?></td>
                <td><?php echo Format::db_datetime($row['updated'])?></td>
            </tr>
            <?php 
            $class = ($class =='row2') ?'row1':'row2';
            } //end of while.
        else: //notthing! ?> 
            <tr class="<?php echo $class?>"><td colspan=8><b>La consulta ha devuelto 0 resultados</b></td></tr>
        <?php 
        endif; ?>
    </table>
    </td></tr>
    <?php 
    if(db_num_rows($services)>0): //Show options..
     ?>
    <tr>
        <td style="padding-left:20px">
            Seleccionar:&nbsp;
            <a href="#" onclick="return select_all(document.forms['topic'],true)">Todos</a>&nbsp;&nbsp;|
            <a href="#" onclick="return toogle_all(document.forms['topic'],true)">Invertir Selecci&oacute;n</a>&nbsp;&nbsp;|
	    <a href="#" onclick="return reset_all(document.forms['topic'])">Ninguno</a>&nbsp;&nbsp;
        </td>
    </tr>
    <tr>
        <td align="center">
            <input class="button" type="submit" name="enable" value="Habilitar"
                onClick=' return confirm("&iquest;Est&aacute; seguro que desea habilitar el servicio seleccionado?");'>
            <input class="button" type="submit" name="disable" value="Deshabilitar" 
                onClick=' return confirm("&iquest;Est&aacute; seguro que desea deshabilitar el servicio seleccionado?");'>
            <input class="button" type="submit" name="delete" value="Eliminar" 
                onClick=' return confirm("&iquest;Est&aacute; seguro que desea eliminar el servicio seleccionado?");'>
        </td>
    </tr>
    <?php 
    endif;
    ?>
    </form>
</table>
