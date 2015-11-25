<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');

//List all groups.   
$sql='SELECT grp.group_id,group_name,group_enabled,count(staff.staff_id) as users, grp.created,grp.updated'
     .' FROM '.GROUP_TABLE.' grp LEFT JOIN '.STAFF_TABLE.' staff USING(group_id)';
$groups=db_query($sql.' GROUP BY grp.group_id ORDER BY group_name');    
$showing=($num=db_num_rows($groups))?'Grupo de usuarios':'No Grupos';
?>
<div class="msg"><?php echo $showing?></div>
<table width="100%" border="0" cellspacing=1 cellpadding=2>
    <form action="admin.php?t=groups" method="POST" name="groups" onSubmit="return checkbox_checker(document.forms['groups'],1,0);">
    <input type=hidden name='a' value='update_groups'>
    <tr><td>
    <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
        <tr>
	        <th width="20">&nbsp;</th>
	        <th width=200>Nombre del Grupo</th>
            <th width=118>Estado del Grupo</th>
	        <th width=80>Miembros</th>
	        <th width="138">Agregado</th>
	        <th width="288">Ultima Actualizaci&oacute;n</th>
        </tr>
        <?php 
        $class = 'row1';
        $total=0;
        $grps=($errors && is_array($_POST['grps']))?$_POST['grps']:null;
        if($groups && db_num_rows($groups)):
            while ($row = db_fetch_array($groups)) {
                $sel=false;
                if(($grps && in_array($row['group_id'],$grps)) || ($gID && $gID==$row['group_id']) ){
                    $class="$class highlight";
                    $sel=true;
                }
                ?>
            <tr class="<?php echo $class?>" id="<?php echo $row['group_id']?>">
                <td width=20>
                  <input type="checkbox" name="grps[]" value="<?php echo $row['group_id']?>" <?php echo $sel?'checked':''?>  onClick="highLight(this.value,this.checked);">
                <td><a href="admin.php?t=grp&id=<?php echo $row['group_id']?>"><?php echo Format::htmlchars($row['group_name'])?></a></td>
                <td><b><?php echo $row['group_enabled']?'Activo':'Deshabilitado'?></b></td>
                <td>&nbsp;&nbsp;<a href="admin.php?t=staff&gid=<?php echo $row['group_id']?>"><?php echo $row['users']?></a></td>
                <td><?php echo Format::db_date($row['created'])?></td>
                <td><?php echo Format::db_datetime($row['updated'])?></td>
            </tr>
            <?php 
            $class = ($class =='row2') ?'row1':'row2';
            } //end of while.
        else: //not tickets found!! ?> 
            <tr class="<?php echo $class?>"><td colspan=6><b>La consulta ha devuelto 0 resultados</b></td></tr>
        <?php 
        endif; ?>
    </table>
    <?php 
    if(db_num_rows($groups)>0): //Show options..
     ?>
    <tr>
        <td style="padding-left:20px;">
            Seleccionar:&nbsp;
            <a href="#" onclick="return select_all(document.forms['groups'],true)">Todos</a>&nbsp;&nbsp;|
            <a href="#" onclick="return toogle_all(document.forms['groups'],true)">Invertir Selecci&oacute;n</a>&nbsp;&nbsp;|
            <a href="#" onclick="return reset_all(document.forms['groups'])">Ninguno</a>&nbsp;&nbsp;
        </td>
    </tr>
    <tr>
        <td align="center">
            <input class="button" type="submit" name="activate_grps" value="Habilitar" 
                onClick=' return confirm("&iquest;Est&aacute; seguro que desea habiltar los grupos selecionados?");'>
            <input class="button" type="submit" name="disable_grps" value="Deshabilitar" 
                onClick=' return confirm("&iquest;Est&aacute; seguro que desea deshabiltar los grupos selecionados?");'>
            <input class="button" type="submit" name="delete_grps" value="Eliminar" 
                onClick=' return confirm("&iquest;Est&aacute; seguro que desea eliminar los grupos selecionados?");'>
        </td>
    </tr>
    <?php 
    endif;
    ?>
    </form>
</table>
