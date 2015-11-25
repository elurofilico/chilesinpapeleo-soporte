<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');


$info['phrase']=($errors && $_POST['phrase'])?Format::htmlchars($_POST['phrase']):$cfg->getAPIPassphrase();
$select='SELECT * ';
$from='FROM '.API_KEY_TABLE;
$where='';
$sortOptions=array('date'=>'created','ip'=>'ipaddr');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
//Sorting options...
if($_REQUEST['sort']) {
    $order_column =$sortOptions[$_REQUEST['sort']];
}

if($_REQUEST['order']) {
    $order=$orderWays[$_REQUEST['order']];
}
$order_column=$order_column?$order_column:'ipaddr';
$order=$order?$order:'ASC';
$order_by=" ORDER BY $order_column $order ";

$total=db_count('SELECT count(*) '.$from.' '.$where);
$pagelimit=1000;//No limit.
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,$pagelimit);
$pageNav->setURL('admin.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
$query="$select $from $where $order_by";
//echo $query;
$result = db_query($query);
$showing=db_num_rows($result)?$pageNav->showing():'';
$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting..
$deletable=0;
?>
<div class="msg">Clave API</div>
<hr>
<div><b><?php echo $showing?></b></div>
 <table width="100%" border="0" cellspacing=1 cellpadding=2>
   <form action="admin.php?t=api" method="POST" name="api" onSubmit="return checkbox_checker(document.forms['api'],1,0);">
   <input type=hidden name='t' value='api'>
   <input type=hidden name='do' value='mass_process'>
   <tr><td>
    <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
        <tr>
	        <th width="7px">&nbsp;</th>
	        <th>Clave API</th>
            <th width="10" nowrap>Activa</th>
            <th width="100" nowrap>&nbsp;&nbsp;Direcci&oacute;n IP</th>
	        <th width="150" nowrap>&nbsp;&nbsp;
                <a href="admin.php?t=api&sort=date&order=<?php echo $negorder?><?php echo $qstr?>" title="Ordenar por Fecha <?php echo $negorder?>">Creada</a></th>
        </tr>
        <?php 
        $class = 'row1';
        $total=0;
        $active=$inactive=0;
        $sids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($result && db_num_rows($result)):
            $dtpl=$cfg->getDefaultTemplateId();
            while ($row = db_fetch_array($result)) {
                $sel=false;
                $disabled='';
                if($row['isactive'])
                    $active++;
                else
                    $inactive++;
                    
                if($sids && in_array($row['id'],$sids)){
                    $class="$class highlight";
                    $sel=true;
                }
                ?>
            <tr class="<?php echo $class?>" id="<?php echo $row['id']?>">
                <td width=7px>
                  <input type="checkbox" name="ids[]" value="<?php echo $row['id']?>" <?php echo $sel?'checked':''?>
                        onClick="highLight(this.value,this.checked);">
                <td>&nbsp;<?php echo $row['apikey']?></td>
                <td><?php echo $row['isactive']?'<b>Si</b>':'No'?></td>
                <td>&nbsp;<?php echo $row['ipaddr']?></td>
                <td>&nbsp;<?php echo Format::db_datetime($row['created'])?></td>
            </tr>
            <?php 
            $class = ($class =='row2') ?'row1':'row2';
            } //end of while.
        else: //nothin' found!! ?> 
            <tr class="<?php echo $class?>"><td colspan=5><b>La consulta ha devuelto 0 resultados</b>&nbsp;&nbsp;<a href="admin.php?t=api">Lista Index </a></td></tr>
        <?php 
        endif; ?>
     
     </table>
    </td></tr>
    <?php 
    if(db_num_rows($result)>0): //Show options..
     ?>
    <tr>
        <td align="center">
            <?php 
            if($inactive) {?>
                <input class="button" type="submit" name="enable" value="Activar"
                     onClick='return confirm("Seguro que quieres HABILITAR la API seleccionada?");'>
            <?php 
            }
            if($active){?>
            &nbsp;&nbsp;
                <input class="button" type="submit" name="disable" value="Desactivar"
                     onClick='return confirm("Seguro que quieres DESHABILITAR la API seleccionada??");'>
            <?php }?>
            &nbsp;&nbsp;
            <input class="button" type="submit" name="delete" value="Eliminar" 
                     onClick='return confirm("Seguro que quieres ELIMINAR la llave seleccionada?");'>
        </td>
    </tr>
    <?php 
    endif;
    ?>
    </form>
 </table>
 <br/>
 <div class="msg">Agregar IP nueva</div>
 <hr>
 <div>
   Agregar IP nueva.&nbsp;&nbsp;<font class="error"><?php echo $errors['ip']?></font>
   <form action="admin.php?t=api" method="POST" >
    <input type=hidden name='t' value='api'>
    <input type=hidden name='do' value='add'>
    Nueva IP:
    <input name="ip" size=30 value="<?php echo ($errors['ip'])?Format::htmlchars($_REQUEST['ip']):''?>" />
    <font class="error">*&nbsp;</font>&nbsp;&nbsp;
     &nbsp;&nbsp; <input class="button" type="submit" name="add" value="Agregar">
    </form>
 </div>
 <br/>
 <div class="msg">Frase secreta API</div>
 <hr>
 <div>
   La frase secreta debe contener al menos 3 palabras. Es necesaria para generar las claves de las  APIs.<br/>
   <form action="admin.php?t=api" method="POST" >
    <input type=hidden name='t' value='api'>
    <input type=hidden name='do' value='update_phrase'>
    Frase Secreta:
    <input name="phrase" size=50 value="<?php echo Format::htmlchars($info['phrase'])?>" />
    <font class="error">*&nbsp;<?php echo $errors['phrase']?></font>&nbsp;&nbsp;
     &nbsp;&nbsp; <input class="button" type="submit" name="update" value="Enviar">
    </form>
    <br/><br/>
    <div><i>Ten en cuenta que cambiando la frase secreta no invalida las Keys existentes. Para regenerar una Key  nueva tienes que borrar la anterior y crear una nueva.</i></div>
 </div>
