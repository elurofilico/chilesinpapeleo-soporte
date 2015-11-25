<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');


$select='SELECT tpl.*,count(dept.tpl_id) as depts ';
$from='FROM '.EMAIL_TEMPLATE_TABLE.' tpl '.
      'LEFT JOIN '.DEPT_TABLE.' dept USING(tpl_id) ';
$where='';
$sortOptions=array('date'=>'tpl.created','name'=>'tpl.name');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
//Sorting options...
if($_REQUEST['sort']) {
    $order_column =$sortOptions[$_REQUEST['sort']];
}

if($_REQUEST['order']) {
    $order=$orderWays[$_REQUEST['order']];
}
$order_column=$order_column?$order_column:'name';
$order=$order?$order:'ASC';
$order_by=" ORDER BY $order_column $order ";

$total=db_count('SELECT count(*) '.$from.' '.$where);
$pagelimit=1000;//No limit.
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,$pagelimit);
$pageNav->setURL('admin.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
$query="$select $from $where GROUP BY tpl.tpl_id $order_by";
//echo $query;
$result = db_query($query);
$showing=db_num_rows($result)?$pageNav->showing():'';
$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting..
$deletable=0;
?>
<div class="msg">Plantillas de Correo (autorespuestas)</div>
<hr>
<div><b><?php echo $showing?></b></div>
 <table width="100%" border="0" cellspacing=1 cellpadding=2>
   <form action="admin.php?t=templates" method="POST" name="tpl" onSubmit="return checkbox_checker(document.forms['tpl'],1,0);">
   <input type=hidden name='t' value='templates'>
   <input type=hidden name='do' value='mass_process'>
   <tr><td>
    <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
        <tr>
	        <th width="20">&nbsp;</th>
	        <th width="417">
                <a href="admin.php?t=templates&sort=name&order=<?php echo $negorder?><?php echo $qstr?>" title="Ordenar por Nombre <?php echo $negorder?>">Nombre</a></th>
            <th width="71" nowrap>En Uso</th>
	        <th width="170" nowrap>&nbsp;&nbsp;
                <a href="admin.php?t=templates&sort=date&order=<?php echo $negorder?><?php echo $qstr?>" title="Sortieren nach Erstellungsdatum <?php echo $negorder?>">Ultima actualizaci&oacute;n</a></th>
            <th width="170" nowrap>Creada</th>
        </tr>
        <?php 
        $class = 'row1';
        $total=0;
        $sids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($result && db_num_rows($result)):
            $dtpl=$cfg->getDefaultTemplateId();
            while ($row = db_fetch_array($result)) {
                $sel=false;
                $disabled='';
                if($dtpl==$row['tpl_id'] || $row['depts'])
                    $disabled='disabled';
                else {
                    $deletable++;
                    if($sids && in_array($row['tpl_id'],$sids)){
                        $class="$class highlight";
                        $sel=true;
                    }
                }
                ?>
            <tr class="<?php echo $class?>" id="<?php echo $row['tpl_id']?>">
                <td width=20>
                  <input type="checkbox" name="ids[]" value="<?php echo $row['tpl_id']?>" <?php echo $sel?'checked':''?> <?php echo $disabled?>
                        onClick="highLight(this.value,this.checked);">
                <td><a href="admin.php?t=templates&id=<?php echo $row['tpl_id']?>"><?php echo $row['name']?></a></td>
                <td><?php echo $disabled?'Si':'No'?></td>
                <td><?php echo Format::db_datetime($row['updated'])?></td>
                <td><?php echo Format::db_datetime($row['created'])?></td>
            </tr>
            <?php 
            $class = ($class =='row2') ?'row1':'row2';
            } //end of while.
        else: //nothin' found!! ?> 
            <tr class="<?php echo $class?>"><td colspan=5><b>La consulta a devuelto 0 resultados</b>&nbsp;&nbsp;<a href="admin.php?t=templates">Lista Index</a></td></tr>
        <?php 
        endif; ?>
     </table>
    </td></tr>
    <?php 
    if(db_num_rows($result)>0 && $deletable): //Show options..
     ?>
    <tr>
        <td align="center">
            <input class="button" type="submit" name="delete" value="Eliminar Plantilla" 
                     onClick='return confirm("&iquest;Est&aacute; seguro que desea eliminar la plantilla seleccionada?");'>
        </td>
    </tr>
    <?php 
    endif;
    ?>
    </form>
 </table>
 <br/>
 <div class="msg">A&ntilde;adir Nueva Plantilla</div>
 <hr>
 <div>
   Para a&ntilde;adir una nueva plantilla elige un nombre, selecciona una existente para copiar y editala posteriormente.<br/>
   <form action="admin.php?t=templates" method="POST" >
    <input type=hidden name='t' value='templates'>
    <input type=hidden name='do' value='add'>
    Nombre:
    <input name="name" size=30 value="<?php echo ($errors)?Format::htmlchars($_REQUEST['name']):''?>" />
    <font class="error">*&nbsp;<?php echo $errors['name']?></font>&nbsp;&nbsp;
    Copiar: 
    <select name="copy_template">
        <option value=0>Seleccionar plantilla a copiar</option>
          <?php 
          $result=db_query('SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_TABLE);
          while (list($id,$name)= db_fetch_row($result)){ ?>
              <option value="<?php echo $id?>"><?php echo $name?></option>
                  <?php 
          }?>
     </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['copy_template']?></font>
     &nbsp;&nbsp; <input class="button" type="submit" name="add" value="A&ntilde;adir">
 </div>
 <br/>
 <div class="msg">Variables</div>
 <hr>
 <div>
 Las variables se utilizan en las plantillas de correo como repositorios. Ten en cuenta estas variables se utilizan en funci&oacute;n del contexto. 
 <table width="100%" border="0" cellspacing=1 cellpadding=2>
    <tr><td width="50%" valign="top"><b>Variables B&aacute;sicas</b></td><td><b>Otras Variables</b></td></tr>
    <tr>
        <td width="50%" valign="top">
            <table width="100%" border="0" cellspacing=1 cellpadding=1>
                <tr><td width="100">%id</td><td>Ticket ID (ID interno)</td></tr>
                <tr><td>%ticket</td><td>Ticket ID (externo ID)</td></tr>
                <tr><td>%email</td><td>Correo Electr&oacute;nico</td></tr>
                <tr><td>%name</td><td>Nombre</td></tr>
                <tr><td>%subject</td><td>Asunto</td></tr>
                <tr><td>%topic</td><td>Tema de Ayuda (solo web)</td></tr>
                <tr><td>%phone</td><td>Tel&eacute;fono</td></tr>
                <tr><td>%status</td><td>Estado</td></tr>
                <tr><td>%priority</td><td>Priorida</td></tr>
                <tr><td>%dept</td><td>Departamento</td></tr>
                <tr><td>%assigned_staff</td><td>Personal Asignado (de existir)</td></tr>
                <tr><td>%createdate</td><td>Creado</td></tr>
                <tr><td>%duedate</td><td>Vencido</td></tr>
                <tr><td>%closedate</td><td>Fecha Cerrado</td></tr>
        </table>
        </td>
        <td valign="top">
            <table width="100%" border="0" cellspacing=1 cellpadding=1>
                <tr><td width="100">%message</td><td>Mensaje (entrante)</td></tr>
                <tr><td>%response</td><td>Respuesta (saliente)</td></tr>
                <tr><td>%note</td><td>Nota Interna</td></tr>
                <tr><td>%staff</td><td>Nombre de Staffs (alertas y notificaciones)</td></tr>
                <tr><td>%assignee</td><td>Staff a quien se asigna el ticket</td></tr>
                <tr><td>%assigner</td><td>Staff que asigna el ticket</td></tr>
                <tr><td>%url</td><td>URL base (FQDN)</td></tr>

            </table>
        </td>
    </tr>
 </table>
 </div>




