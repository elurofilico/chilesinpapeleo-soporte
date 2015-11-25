<?php 
if(!defined('OSTCLIENTINC') || !is_object($ticket)) die('ElArteDeGanar.com'); //Say bye to our friend..

//Please customize the message below to fit your organization speak!
?>
<div>
    <?php if($errors['err']) {?>
        <p align="center" id="errormessage"><?php echo $errors['err']?></p>
    <?php }elseif($msg) {?>
        <p align="center" id="infomessage"><?php echo $msg?></p>
    <?php }elseif($warn) {?>
        <p id="warnmessage"><?php echo $warn?></p>
    <?php }?>
</div>
<div style="margin:5px 100px 100px 0;">Estimado(a)
    <?php echo Format::htmlchars($ticket->getName())?>,<br>
    <p>
     Se ha ingresado al sistema un "ticket" asociado a su consulta. Tomaremos contacto con usted en caso de ser necesarios más antecedentes.
     </p>
          
    <?php if($cfg->autoRespONNewTicket()){ ?>
    <p>El código del ticket se ha enviado a su dirección de correo electrónico <strong><?php echo $ticket->getEmail()?></strong><br/>Es importante recordar este código para hacer seguimiento al estado de avance de su ticket.
    </p>
    <p>
     Si desea proporcionar información adicional o comentarios sobre la misma consulta, siga las instrucciones incluidas en el correo electrónico.
    </p>
    <?php }?>
    <p>Mesa de soporte</p>
</div>
<?php 
unset($_POST); //clear to avoid re-posting on back button??
?>
