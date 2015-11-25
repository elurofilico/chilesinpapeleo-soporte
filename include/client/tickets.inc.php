<?php 
if(!defined('OSTCLIENTINC') || !is_object($thisclient) || !$thisclient->isValid()) die('ElArteDeGanar.com');

//Get ready for some deep shit.
$qstr='&'; //Query string collector
$status=null;
if($_REQUEST['status']) { //Query string status has nothing to do with the real status used below.
    $qstr.='status='.urlencode($_REQUEST['status']);
    //Status we are actually going to use on the query...making sure it is clean!
    switch(strtolower($_REQUEST['status'])) {
     case 'open':
	$ger_status = 'Abierto';
	$status=$_REQUEST['status'];
	break;
     case 'closed':
	$ger_status = 'Cerrado';
        $status=$_REQUEST['status'];
        break;
     default:
        $status=''; //ignore
    }
}

//Restrict based on email of the user...STRICT!
$qwhere =' WHERE ticket.email='.db_input($thisclient->getEmail());

//STATUS
if($status){
    $qwhere.=' AND status='.db_input($status);    
}
//Admit this crap sucks...but who cares??
$sortOptions=array('date'=>'ticket.created','ID'=>'ticketID','pri'=>'priority_id','dept'=>'dept_name');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');

//Sorting options...
if($_REQUEST['sort']) {
        $order_by =$sortOptions[$_REQUEST['sort']];
}
if($_REQUEST['order']) {
    $order=$orderWays[$_REQUEST['order']];
}
if($_GET['limit']){
    $qstr.='&limit='.urlencode($_GET['limit']);
}

$order_by =$order_by?$order_by:'ticket.created';
$order=$order?$order:'DESC';
$pagelimit=$_GET['limit']?$_GET['limit']:PAGE_LIMIT;
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;

$qselect = 'SELECT ticket.ticket_id,ticket.ticketID,ticket.dept_id,isanswered,ispublic,subject,name '.
           ',dept_name,status,source,priority_id,ticket.created,ticket.updated,staff.firstname,staff.lastname';
$qfrom=' FROM ('.TICKET_TABLE.' ticket'.
       ' LEFT JOIN '.DEPT_TABLE.' dept ON ticket.dept_id=dept.dept_id )'.
       ' LEFT JOIN '.STAFF_TABLE.' staff ON ticket.staff_id=staff.staff_id';  

//Pagenation stuff....wish MYSQL could auto pagenate (something better than limit)
$total=db_count('SELECT count(*) '.$qfrom.' '.$qwhere);
$pageNav=new Pagenate($total,$page,$pagelimit);
$pageNav->setURL('view.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));

//Ok..lets roll...create the actual query
$qselect.=' ,count(attach_id) as attachments ';
$qfrom.=' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  ticket.ticket_id=attach.ticket_id ';
$qgroup=' GROUP BY ticket.ticket_id';
$query="$qselect $qfrom $qwhere $qgroup ORDER BY $order_by $order LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
$tickets_res = db_query($query);
$showing=db_num_rows($tickets_res)?$pageNav->showing():"";
$results_type=($status)?($ger_status).'Tickets':'Tickets';
$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting..
?>

<div class="page-header">
    <h1>Mis tickets</h1>
</div>
<div class="row">
    <?php if($errors['err']) {?>
        <p align="center" id="errormessage"><?php echo $errors['err']?></p>
    <?php }elseif($msg) {?>
        <p align="center" id="infomessage"><?php echo $msg?></p>
    <?php }elseif($warn) {?>
        <p id="warnmessage"><?php echo $warn?></p>
    <?php }?>
</div>

<div class="row">
    <div class="span7">
        <p><?php echo $showing?> <?php if($results_type == 'Tickets') echo 'tickets'; if($results_type == 'CerradoTickets') echo 'tickets cerrados'; if($results_type == 'AbiertoTickets') echo 'tickets abiertos'; ?></p>
    </div>
    <div class="span5">
    <p class="pull-right">
        <a class="btn btn-warning"  href="view.php?status=open"><i class="icon-folder-open icon-white"></i> Abiertos</a>             
        <a class="btn btn-info" href="view.php?status=closed"><i class="icon-briefcase icon-white"></i> Cerrados</a>             
        <a class="btn" href=""><i class="icon-refresh"></i> Actualizar</a>
    </p>
    </div>
</div>

<table class="table table-striped table-bordered table-hover table-condensed">
    <thead>
                <tr>
            <th nowrap>
                <a href="view.php?sort=ID&order=<?php echo $negorder?><?php echo $qstr?>" title="Ordenar por Ticket-ID <?php echo $negorder?>">Ticket #</a></th>
            <th>
                <a href="view.php?sort=date&order=<?php echo $negorder?><?php echo $qstr?>" title="Ordenar por Fecha <?php echo $negorder?>">Creado</a></th>
        <th >Actualizado</th>
            <th>Estado</th>
            <th>Asunto</th>
        <!-- <th width="150">Email</th> -->
        </tr>
    </thead>
    <tbody>
        <?php 
        $class = "row1";
        $total=0;
        if($tickets_res && ($num=db_num_rows($tickets_res))):
            $defaultDept=Dept::getDefaultDeptName();
            while ($row = db_fetch_array($tickets_res)) {
                $dept=$row['ispublic']?$row['dept_name']:$defaultDept; //Don't show hidden/non-public depts.
                $subject=Format::htmlchars(Format::truncate($row['subject'],40));
                $ticketID=$row['ticketID'];
                if($row['isanswered'] && !strcasecmp($row['status'],'open')) {
                    $subject="<strong>$subject</strong>";
                    $ticketID="<strong>$ticketID</strong>";
                }
                $stati = $row['status'];
		switch(strtolower($stati)){ //Status is overloaded
    			case 'open':
       			    $stati='Abierto';
    			    break;
    			case 'closed':
        		    $stati='Cerrado';
    			    break;
		}
	        ?>
            <tr id="<?php echo $row['ticketID']?>">
                <td title="<?php echo $row['email']?>" nowrap>
                    <a class="Icon <?php echo strtolower($row['source'])?>Ticket" title="<?php echo $row['email']?>" href="view.php?id=<?php echo $row['ticketID']?>">
                        <?php echo $ticketID?></a></td>
                <td nowrap><?php echo Format::db_date($row['created'])?></td>
                <td><?php if($row['updated']!='0000-00-00 00:00:00'){
                    echo Format::db_datetime($row['updated']);}
                    else{
                        echo "Sin actualización";
                    }?></td>
                <td><?php echo $stati?></td>
                <td><a href="view.php?id=<?php echo $row['ticketID']?>"><?php echo $subject?></a>
                    &nbsp;<?php echo $row['attachments']?"<span class='Icon file'>&nbsp;</span>":''?></td>
	<!-- <td>&nbsp;<?php  /*=Format::truncate($row['email'],40) */ ?></td> -->
            </tr>
            <?php 
            $class = ($class =='row2') ?'row1':'row2';
            } //end of while.
        else: //not tickets found!! ?> 
            <tr class="<?php echo $class?>"><td colspan=7><strong>No se ha encontrado ningun Ticket.</strong></td></tr>
        <?php 
        endif; ?>
     </table>
     <div class="pagination">
    <ul>
    <?php
    if($num>0 && $pageNav->getNumPages()>1){ //if we actually had any tickets returned?>
     <li>P&aacute;gina:<?php echo $pageNav->getPageLinks()?>&nbsp;</li>
    <?php }?>
    </ul>
    </div>
<?php 
