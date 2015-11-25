<?php 
if(!defined('OSTCLIENTINC')) die('ElArteDeGanar.com');

$e=Format::input($_POST['lemail']?$_POST['lemail']:$_GET['e']);
$t=Format::input($_POST['lticket']?$_POST['lticket']:$_GET['t']);
?>
    <div class="page-header">
    <h1>Consulta de tickets</h1>
    </div>

    <?php  if($errors['err']) {?>
        <div class="alert alert-error">
        <p id="errormessage"><?php  echo  $errors['err']?></p>
    </div>
    <?php  }elseif($warn) {?>
    <div class="alert alert-block">
        <p class="warnmessage"><?php  echo  $warn ?></p>
    </div>
    <?php  }?>
    <p>Para ver el estado de un ticket o entregar más información se requieren los siguientes datos de acceso:</p>

    <div class="alert alert-error">
    <p><?php  echo  Format::htmlchars($loginmsg)?></p>
</div>
    <form class="form-inline" action="login.php" method="post">
        <input type="text" class="input" name="lemail" value="<?php  echo $e ?>" placeholder="Correo electrónico">
        <input type="text" class="input-small" name="lticket" size="10" value="<?php  echo $t ?>" placeholder="Nº Ticket">
        <button type="submit" class="btn btn-info controls"><i class="icon-search icon-white"></i> Ver estado</button>
    </form>
    <div class="well">
    <p><strong>Nota</strong></p>
    <p>Si es la primera que toma contacto con nuestro equipo o no recuerda el número de ticket asignado (enviado al correo electrónico) haga clic <a href="open.php"><strong>Aquí</strong></a> para abrir un nuevo ticket.</p>
    </div>
    