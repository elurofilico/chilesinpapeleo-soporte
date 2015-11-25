<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');

$select='SELECT * ';
$from='FROM '.BANLIST_TABLE;
$where='';
//make sure the search query is 3 chars min...defaults to no query with warning message
if($_REQUEST['a']=='search') {
    if(!$_REQUEST['query'] || strlen($_REQUEST['query'])<3) {
        $errors['err']='T&eacute;rminos a buscar deben tener m&aacute;s de 3 caracteres.';
    }else{
        //Do the search
        $search=true;
        $qstr.='&a='.urlencode($_REQUEST['a']);
        $qstr.='&query='.urlencode($_REQUEST['query']);
        $searchTerm=trim($_REQUEST['query']);
        if(strpos($searchTerm,'@') && Validator::is_email($searchTerm)){ //pulling all tricks!
            $where=' WHERE email='.db_input($searchTerm);
        }else{
            $where=' WHERE email LIKE \'%'.db_real_escape($searchTerm,false).'%\'';
        } 
    }
}
//I admit this crap sucks...but who cares??
$sortOptions=array('date'=>'added','email'=>'email');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
//Sorting options...
if($_REQUEST['sort']) {
    $order_column =$sortOptions[$_REQUEST['sort']];
}

if($_REQUEST['order']) {
    $order=$orderWays[$_REQUEST['order']];
}


$order_column=$order_column?$order_column:'added';
$order=$order?$order:'DESC';

$order_by=" ORDER BY $order_column $order ";

$total=db_count('SELECT count(*) '.$from.' '.$where);
$pagelimit=$thisuser->getPageLimit();
$pagelimit=$pagelimit?$pagelimit:PAGE_LIMIT; //true default...if all fails.
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,$pagelimit);
$pageNav->setURL('admin.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
//Ok..lets roll...create the actual query
$query="$select $from $where $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
$banlist = db_query($query);
$showing=db_num_rows($banlist)?$pageNav->showing():'';
$result_type=($search)?'Resultados de la Busqueda':'Emails Restringidos';
$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting..
$showadd=($errors && $_POST['a']=='add')?true:false;
?>
<div id='search' align="left"  style="display:<?php echo $showadd?'none':'block'?>;">
    <form action="admin.php?t=settings" method="GET" >
        <input type='hidden' name='t' value='banlist'>
        <input type='hidden' name='a' value='search'>
        Consulta:&nbsp;<input type="text" name="query" value="<?php echo Format::htmlchars($_REQUEST['query'])?>">
        &nbsp;&nbsp;
        <input type="submit" name="search" class="button" value="Buscar">
        &nbsp;<a href="#" class="msg" onClick="showHide('add','search'); return false;">(Agregar)</a>
    </form>
</div>
<div id='add' style="display:<?php echo $showadd?'block':'none'?>;" align="left">
    <form action="admin.php?t=settings" method="POST" >
    <input type='hidden' name='t' value='banlist'>
    <input type='hidden' name='a' value='add'>
    Email:&nbsp;<input type="text" name="email" value="<?php echo $_POST['email']?>">
    &nbsp;
    <input type="submit" name="add" class="button" value="Agregar">
     &nbsp;<a href="#" class="msg" onClick="showHide('add','search'); return false;">(Buscar)</a>
    </form>
</div>
<div class="msg"><?php echo $result_type?>:&nbsp;<?php echo $showing?></div>
 <table width="92%" border="0" cellspacing=1 cellpadding=2>
   <form action="admin.php?t=banlist" method="POST" name="banlist" onSubmit="return checkbox_checker(document.forms['banlist'],1,0);">
   <input type=hidden name='t' value='banlist'>
   <input type=hidden name='a' value='remove'>
   <tr><td>
    <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
        <tr>
	        <th width="7px">&nbsp;</th>
	        <th width=250>
                <a href="admin.php?t=banlist&sort=email&order=<?php echo $negorder?><?php echo $qstr?>" title="Ordenar por Email <?php echo $negorder?>">Email</a></th>
	        <th width=200 >Remitente</th> 
	        <th width=150 nowrap>
                <a href="admin.php?t=banlist&sort=date&order=<?php echo $negorder?><?php echo $qstr?>" title="Ordenar por Fecha <?php echo $negorder?>">Agregado</a></th>
        </tr>
        <?php 
        $class = 'row1';
        $total=0;
        $sids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($banlist && db_num_rows($banlist)):
            while ($row = db_fetch_array($banlist)) {
                $sel=false;
                if($sids && in_array($row['id'],$sids)){
                    $class="$class highlight";
                    $sel=true;
                }
                ?>
            <tr class="<?php echo $class?>" id="<?php echo $row['id']?>">
                <td width=7px>
                  <input type="checkbox" name="ids[]" value="<?php echo $row['id']?>" <?php echo $sel?'checked':''?> 
                        onClick="highLight(this.value,this.checked);">
                <td><?php echo $row['email']?></td>
                <td><?php echo $row['submitter']?></td>
                <td><?php echo Format::db_datetime($row['added'])?></td>
            </tr>
            <?php 
            $class = ($class =='row2') ?'row1':'row2';
            } //end of while.
        else: //nothin' found!! ?> 
            <tr class="<?php echo $class?>"><td colspan=4><b>La consulta ha devuelto 0 resultados</b>&nbsp;&nbsp;<a href="admin.php?t=banlist">Lista Index </a></td></tr>
        <?php 
        endif; ?>
     </table>
    </td></tr>
    <?php 
    if(db_num_rows($banlist)>0): //Show options..
     ?>
    <tr>
        <td style="padding-left:20px">
            Auswahl:&nbsp;
            <a href="#" onclick="return select_all(document.forms['banlist'],true)">Todos</a>&nbsp;&nbsp;
            <a href="#" onclick="return toogle_all(document.forms['banlist'],true)">Invertir Selecci&oacute;n</a>&nbsp;&nbsp;
            <a href="#" onclick="return reset_all(document.forms['banlist'])">Ninguno</a>&nbsp;&nbsp;
            &nbsp;P&aacute;gina:<?php echo $pageNav->getPageLinks()?>
            
        </td>
    </tr>
    <tr>
        <td align="center">
            <input class="button" type="submit" name="delete" value="Eliminar de la Lista" 
                     onClick='return confirm("&iquest;Est&aacute; seguro que desea eliminar los Emails seleccionados de la lista negra?");'>
        </td>
    </tr>
    <?php 
    endif;
    ?>
    </form>
 </table>
