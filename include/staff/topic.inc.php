<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');

$info=($_POST && $errors)?Format::input($_POST):array(); //Re-use the post info on error...savekeyboards.org
if($topic && $_REQUEST['a']!='new'){
    $title='Editar &Aacute;rea de Ayuda';
    $action='update';
    $info=$info?$info:$topic->getInfo();
}else {
   $title='Nueva &Aacute;rea de Ayuda';
   $action='create';
   $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
}
//get the goodies.
$depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE);
$priorities= db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
?>
<form action="admin.php?t=topics" method="post">
 <input type="hidden" name="do" value="<?php echo $action?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a'])?>">
 <input type='hidden' name='t' value='topics'>
 <input type="hidden" name="topic_id" value="<?php echo $info['topic_id']?>">
<table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
    <tr class="header"><td colspan=2><?php echo $title?></td></tr>
    <tr class="subheader">
        <td colspan=2 >Desactivar respuesta autom&aacute;tica para este &aacute;rea (sobrescribe la configuraci&oacute;n del departamento) </td>
    </tr>
    <tr>
        <th width="20%">&Aacute;rea de Ayuda:</th>
        <td><input type="text" name="topic" size=45 value="<?php echo $info['topic']?>">
            &nbsp;<font class="error">*&nbsp;<?php echo $errors['topic']?></font></td>
    </tr>
    <tr><th>Estado del &Aacute;rea</th>
        <td>
            <input type="radio" name="isactive"  value="1"   <?php echo $info['isactive']?'checked':''?> />Activar
            <input type="radio" name="isactive"  value="0"   <?php echo !$info['isactive']?'checked':''?> />Desactivar
        </td>
    </tr>
    <tr>
        <th nowrap>Respuesta Autom&aacute;tica:</th>
        <td>
            <input type="checkbox" name="noautoresp" value=1 <?php echo $info['noautoresp']? 'checked': ''?> >
                <b>Desactivar</b> respuesta autom&aacute;tica para este &aacute;rea.   (<i>sobrescribe la configuraci&oacute;n del departamento</i>)
        </td>
    </tr>
    <tr>
        <th>Prioridad de Ticket Nuevo:</th>
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
        <th nowrap>Departamento para Tickets Nuevos:</th>
        <td>
            <select name="dept_id">
                <option value=0>Seleccionar</option>
                <?php 
                while (list($id,$name) = db_fetch_row($depts)){
                    $selected = ($info['dept_id']==$id)?'selected':''; ?>
                    <option value="<?php echo $id?>"<?php echo $selected?>>Departamento <?php echo $name?></option>
                <?php 
                }?>
            </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['dept_id']?></font>
        </td>
    </tr>
</table>
<div style="padding-left:220px;">
    <input class="button" type="submit" name="submit" value="Guardar">
    <input class="button" type="reset" name="reset" value="Restablecer">
    <input class="button" type="button" name="cancel" value="Cancelar" onClick='window.location.href="admin.php?t=topics"'>
</div>
</form>
