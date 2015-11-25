    <div class="page-header">
    <h1>Nuevo ticket</h1>
    </div>
    <?php 
    if(!defined('OSTCLIENTINC')) die('ElArteDeGanar.com'); //Say bye to our friend..
    $info=($_POST && $errors)?Format::input($_POST):array(); //on error...use the post data
    ?>
        <?php  if($errors['err']) {?>
        <div class="alert alert-error">
            <p id="errormessage"><strong>Error</strong><br/><br/><?php  echo $errors['err']?></p>
            <ol>
            <?php if ($errors['email']){ ?>
                <li><?php echo $errors['email']?></li>
            <?php } ?>
            <?php if ($errors['name']){ ?>
                <li><?php echo $errors['name']?></li>
            <?php } ?>
            <?php if($errors['phone']) { ?>
                <li><?php echo $errors['phone']?></li>
            <?php } ?>
            <?php if($errors['phone_ext']) { ?>
                <li><?php echo $errors['phone_ext']?></li>
            <?php } ?>
            <?php if ($errors['topicId']){ ?>
                <li><?php echo $errors['topicId']?></li>
            <?php } ?>
            <?php if($errors['subject']){ ?>
                <li><?php echo $errors['subject']?></li>
            <?php } ?>
            <?php if($errors['message']){ ?>
                <li><?php echo $errors['message']?></li>
            <?php } ?>
            <?php if($errors['servicio_codigo']) { ?>
                <li><?php echo $errors['servicio_codigo']?></li>
            <?php } ?>
            <?php if($errors['captcha']) { ?>
                <li><?php echo $errors['captcha']?></li>
            <?php } ?>
            </ol>
        </div>
        <?php  }elseif($msg) {?>
        <div class="alert alert-info">
            <p align="center" id="infomessage"><?php  echo $msg?></p>
        </div>
        <?php  }elseif($warn) {?>
        <div class="alert alert-info">
            <p id="warnmessage"><?php  echo $warn?></p>
        </div>
        <?php  }?>

        <p>Para abrir un nuevo ticket complete los datos en el siguiente formulario:</p>
    <form class="form-horizontal" action="open.php" method="POST" enctype="multipart/form-data">
        <legend>Datos de contacto</legend>
        <div class="control-group">
            <label class="control-label" for="inputEmail"><strong>Correo electrónico</strong></label>
            <div class="controls">
                <?php if ($thisclient && ($email=$thisclient->getEmail())) { ?>
                    <input type="hidden" name="email" value="<?php echo $email?>"><?php echo $email?>
                <?php }else {?>             
                    <input type="text" name="email" value="<?php echo $info['email']?>">
                <?php }?>
            </div>           
            <div class="">
                <span class="help-block controls">* Campo requerido</span>
        </div>     
       <div class="control-group">
            <label class="control-label" for="inputName"><strong>Nombre completo</strong></label>
            <div class="controls">
                <?php if ($thisclient && ($name=$thisclient->getName())) { ?>
                    <input type="hidden" name="name" value="<?php echo $name?>"><?php echo $name?>
                <?php }else {?>             
                    <input type="text" name="name" value="<?php echo $info['name']?>">
                <?php }?> 
            </div>           
            <span class="help-block controls">* Campo requerido</span>
        </div> 
       <div class="control-group">
            <label class="control-label" for="inputPhone"><strong>Teléfono</strong></label>
            <div class="controls">            
                <input type="text" name="phone" value="<?php echo $info['phone']?>">

            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="inputPhoneext"><strong>Anexo</strong></label>
            <div class="controls">            
                <input type="text" name="phone_ext" value="<?php echo $info['phone_ext']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="inputNombre"><strong>Institución</strong></label>
            <div class="controls">
                <select name="topicId">
                    <option value="" selected > - Seleccione -</option>
                    <?php 
                    $services= db_query('SELECT topic_id,topic FROM '.TOPIC_TABLE.' WHERE isactive=1 ORDER BY topic');
                    if($services && db_num_rows($services)) {
                        while (list($topicId,$topic) = db_fetch_row($services)){
                            $selected = ($info['topicId']==$topicId)?'selected':''; ?>
                    <option value="<?php echo $topicId?>"<?php echo $selected?>><?php echo $topic?></option>
                    <?php } }else{?>
                    <option value="0" >General</option>
                    <?php }?>
                </select>
            </div>
            <span class="help-block controls">* Campo requerido</span>
        </div>

        <legend>Contenido del ticket</legend> 
        <div class="control-group">
            <label class="control-label" for="inputPhoneext"><strong>Área de ayuda</strong></label>
            <div class="controls">            
                <select name="servicio_codigo" id="servicio_codigo">
                    <option value="">- Seleccione -</option>
                  <?php 
                  $servicios= db_query('SELECT codigo, nombre FROM seg_servicio ORDER BY nombre ASC');
                  if($servicios && db_num_rows($servicios)) {
                      while (list($codigo,$nombre) = db_fetch_row($servicios)){
                          $selected = ($info['servicio_codigo']==$codigo)?'selected':''; ?>
                  <option value="<?php echo $codigo?>"<?php echo $selected?>><?php echo $nombre?></option>
                  <?php } }else{?>
                    <option value="0" >General</option>
                  <?php }?>
                </select>
            </div>
            <span class="help-block controls">* Campo requerido</span>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputSubject"><strong>Asunto</strong></label>
            <div class="controls">            
                <input type="text" name="subject" value="<?php echo $info['subject']?>">
            </div>
            <span class="help-block controls">* Campo requerido</span>
        </div>
        <div class="control-group">
            <label class="control-label" for="inputContent"><strong>Contenido</strong></label>
            <div class="controls">
                <textarea name="message"><?php echo $info['message']?></textarea>
            </div>
            <span class="help-block controls">* Campo requerido</span>
        </div>

<?php if($cfg->allowPriorityChange() ) {
          $sql='SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE.' WHERE ispublic=1 ORDER BY priority_urgency DESC';
          if(($priorities=db_query($sql)) && db_num_rows($priorities)){ ?>
        <div class="control-group">
            <label class="control-label" for="inputNombre"><strong>Prioridad</strong></label>
            <div class="controls">
                <select name="pri">
                    <?php 
                    $info['pri']=$info['pri']?$info['pri']:$cfg->getDefaultPriorityId(); //use system's default priority.
                    while($row=db_fetch_array($priorities)){ ?>
                        <option value="<?php echo $row['priority_id']?>" <?php echo $info['pri']==$row['priority_id']?'selected':''?> ><?php echo $row['priority_desc']?></option>
                    <?php }?>                    
                </select>
            </div>
        </div>
<?php } }?>

<?php if(($cfg->allowOnlineAttachments() && !$cfg->allowAttachmentsOnlogin()) || ($cfg->allowAttachmentsOnlogin() && ($thisclient && $thisclient->isValid()))){ ?>
        <div class="control-group">
            <label class="control-label" for="inputSubject"><strong>Archivo adjunto</strong></label>
            <div class="controls">            
                <input type="file" name="attachment" value="<?php echo $info['attachment']?>">
            </div>
        </div>
<?php }?>


        <?php if($_POST && $errors && !$errors['captcha'])
            $errors['captcha']='El C&oacute;digo no es Correcto'; ?>
        <div class="control-group">
            <label class="control-label" for="inputSubject"><strong>Código de seguridad</strong></label>
            <div class="controls">
                <img src="captcha.php" align="left">      
                <input type="text" name="captcha" value="" size="7">
            </div>
            <span class="help-block controls">* Campo requerido, respete el uso de mayúsculas.</span>
        </div>
    
    <div class="form-actions">
    <button type="submit" class="btn btn-success">Crear ticket</button>
    <button type="reset" class="btn">Limpiar formulario</button>
    <button type="button" class="btn" onClick='window.location.href="index.php"'>Cancelar</button>
    </div>
    </form>
