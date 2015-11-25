<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');
//Get the config info.
$config=($errors && $_POST)?Format::input($_POST):$cfg->getConfig();
?>
<table width="100%" border="0" cellspacing=0 cellpadding=0>
    <form action="admin.php?t=attach" method="post">
    <input type="hidden" name="t" value="attach">
    <tr>
      <td>
        <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
          <tr class="header">
            <td colspan=2>&nbsp;Configuraci&oacute;n de Archivos Adjuntos</td>
          </tr>
          <tr class="subheader">
            <td colspan=2">
	       Antes de habilitar esta funci&oacute;n asegurate de entender las condiciones de seguridad y los problemas relativos a la misma.</td>
          </tr>
          <tr>
            <th width="165">Permitir Adjuntos:</th>
            <td>
              <input type="checkbox" name="allow_attachments" <?php echo $config['allow_attachments'] ?'checked':''?>><b>Permitir Adjuntos</b>
                &nbsp; (<i>Configuraci&oacute;n Global</i>)
                &nbsp;<font class="error">&nbsp;<?php echo $errors['allow_attachments']?></font>
            </td>
          </tr>
          <tr>
            <th>Adjuntos por email:</th>
            <td>
                <input type="checkbox" name="allow_email_attachments" <?php echo $config['allow_email_attachments'] ? 'checked':''?> > Permitir Adjuntos por Email
                    &nbsp;<font class="warn">&nbsp;<?php echo $warn['allow_email_attachments']?></font>
            </td>
          </tr>
         <tr>
            <th>Adjuntos Online:</th>
            <td>
                <input type="checkbox" name="allow_online_attachments" <?php echo $config['allow_online_attachments'] ?'checked':''?> >
                    Habilitar archivos adjuntos Online<br/>&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="allow_online_attachments_onlogin" <?php echo $config['allow_online_attachments_onlogin'] ?'checked':''?> >
                    Solo usuarios autenticados. (<i>deben haber iniciado sesi&oacute;n antes de poder adjuntar un archivo </i>)
                    <font class="warn">&nbsp;<?php echo $warn['allow_online_attachments']?></font>
            </td>
          </tr>
          <tr>
            <th>Archivo de respuesta del Staff:</th>
            <td>
                <input type="checkbox" name="email_attachments" <?php echo $config['email_attachments']?'checked':''?> >Permitir adjuntos de correo al usuario
            </td>
          </tr>
          <tr>
            <th nowrap>Tama&ntilde;o m&aacute;ximo permitido:</th>
            <td>
              <input type="text" name="max_file_size" value="<?php echo $config['max_file_size']?>"> <i>bytes</i>
                <font class="error">&nbsp;<?php echo $errors['max_file_size']?></font>
            </td>
          </tr>
          <tr>
            <th>Carpeta de Adjuntos:</th>
            <td>
                El Usuario debe tener acceso de escritura a la carpeta. (chmod 777)&nbsp;<font class="error">&nbsp;<?php echo $errors['upload_dir']?></font><br>
              <input type="text" size=60 name="upload_dir" value="<?php echo $config['upload_dir']?>"> 
              <font color=red>
              <?php echo $attwarn?>
              </font><br>
            (ej.de ruta absoluta) <i>/home/nombredeusuario/www/soporte/carpeta de adjuntos</i></td>
          </tr>
          <tr>
            <th valign="top"><br/>
            Tipos de archivos permitidos:</th>
            <td>
                Introduce las extensiones de fichero permitidas separadas por coma (.doc, .pdf) </i> <br>
                Para aceptar cualquier fichero introduce <b><i>.*</i></b> (NO recomendado).
                <textarea name="allowed_filetypes" cols="21" rows="4" style="width: 65%;" wrap=HARD ><?php echo $config['allowed_filetypes']?></textarea>
            </td>
          </tr>
        </table>
    </td></tr>
    <tr><td style="padding:10px 0 10px 200px">
        <input class="button" type="submit" name="submit" value="Guardar Cambios">
        <input class="button" type="reset" name="reset" value="Restablecer Cambios">
    </td></tr>
  </form>
</table>
