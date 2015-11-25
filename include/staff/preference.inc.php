<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin()) die('Acceso Denegado');

//Get the config info.
$config=($errors && $_POST)?Format::input($_POST):Format::htmlchars($cfg->getConfig());
//Basic checks for warnings...
$warn=array();
if($config['allow_attachments'] && !$config['upload_dir']) {
    $errors['allow_attachments']='Debes crear una carpeta para los archivos adjuntos.';    
}else{
    if(!$config['allow_attachments'] && $config['allow_email_attachments'])
        $warn['allow_email_attachments']='*Adjuntos por email Desactivado.';
    if(!$config['allow_attachments'] && ($config['allow_online_attachments'] or $config['allow_online_attachments_onlogin']))
        $warn['allow_online_attachments']='<br>*Adjuntos por web Desactivado.';
}

if(!$errors['enable_captcha'] && $config['enable_captcha'] && !extension_loaded('gd'))
    $errors['enable_captcha']='Se nesecita biblioteca GD para que el Capcha pueda funcionar';
    

//Not showing err on post to avoid alarming the user...after an update.
if(!$errors['err'] &&!$msg && $warn )
    $errors['err']='Se an detectado errores, mira las advertencias a continuaci&oacute;n';
    
$gmtime=Misc::gmtime();
$depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE.' WHERE ispublic=1');
$templates=db_query('SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_TABLE.' WHERE cfg_id='.db_input($cfg->getId()));
?>
<div class="msg">Preferencias del Sistema y Configuraci&oacute;n&nbsp;&nbsp;(v<?php echo $config['ostversion']?>)</div>
<table width="100%" border="0" cellspacing=0 cellpadding=0>
 <form action="admin.php?t=pref" method="post">
 <input type="hidden" name="t" value="pref">
 <tr><td>
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header" ><td colspan=2>Configuraci&oacute;n General</td></tr>
        <tr class="subheader">
            <td colspan=2">El modo Sin Servicio desactivar&aacute; la interface de cliente y &uacute;nicamente permitir&aacute; a los administradores acceder al Panel de Control</td>
        </tr>
        <tr><th><b>Estado del Sistema</b></th>
            <td>
                <input type="radio" name="isonline"  value="1"   <?php echo $config['isonline']?'checked':''?> /><b>En Servicio</b> (Activo)
                <input type="radio" name="isonline"  value="0"   <?php echo !$config['isonline']?'checked':''?> /><b>Sin Servicio</b> (Inactivo)
                &nbsp;<font class="warn">&nbsp;<?php echo $config['isoffline']?'osTicket offline':''?></font>
            </td>
        </tr>
        <tr><th>URL del Centro de Ayuda:</th>
            <td>
                <input type="text" size="40" name="helpdesk_url" value="<?php echo $config['helpdesk_url']?>"> 
                &nbsp;<font class="error">*&nbsp;<?php echo $errors['helpdesk_url']?></font></td>
        </tr>
        <tr><th>Titulo del Centro:</th>
            <td><input type="text" size="40" name="helpdesk_title" value="<?php echo $config['helpdesk_title']?>"> </td>
        </tr>
        <tr><th>Plantillas de correo por defecto:</th>
            <td>
                <select name="default_template_id">
                    <option value=0>Selecciona Plantilla</option>
                    <?php 
                    while (list($id,$name) = db_fetch_row($templates)){
                        $selected = ($config['default_template_id']==$id)?'SELECTED':''; ?>
                        <option value="<?php echo $id?>"<?php echo $selected?>><?php echo $name?></option>
                    <?php 
                    }?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['default_template_id']?></font>
            </td>
        </tr>
        <tr><th>Departamento por defecto:</th>
            <td>
                <select name="default_dept_id">
                    <option value=0>Selecionar Departamento</option>
                    <?php 
                    while (list($id,$name) = db_fetch_row($depts)){
                    $selected = ($config['default_dept_id']==$id)?'SELECTED':''; ?>
                    <option value="<?php echo $id?>"<?php echo $selected?>>Departamento <?php echo $name?></option>
                    <?php 
                    }?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['default_dept_id']?></font>
            </td>
        </tr>
        <tr><th>Tama&ntilde;o de P&aacute;gina:</th>
            <td>
                <select name="max_page_size">
                    <?php 
                     $pagelimit=$config['max_page_size'];
                    for ($i = 5; $i <= 50; $i += 5) {
                        ?>
                        <option <?php echo $config['max_page_size'] == $i ? 'SELECTED':''?> value="<?php echo $i?>"><?php echo $i?></option>
                        <?php 
                    }?>
                </select>
            </td>
        </tr>
        <tr><th>Registro del Sistema:</th>
            <td>
                <select name="log_level">
                    <option value=0 <?php echo $config['log_level'] == 0 ? 'selected="selected"':''?>>Ninguno (Desactivar Registro)</option>
                    <option value=3 <?php echo $config['log_level'] == 3 ? 'selected="selected"':''?>> Depurar</option>
                    <option value=2 <?php echo $config['log_level'] == 2 ? 'selected="selected"':''?>> Advertencia</option>
                    <option value=1 <?php echo $config['log_level'] == 1 ? 'selected="selected"':''?>> Error</option>
                </select>
                &nbsp;Purgar registro pasado
                <select name="log_graceperiod">
                    <option value=0 selected> Deshabilitado</option>
                    <?php 
                    for ($i = 1; $i <=12; $i++) {
                        ?>
                        <option <?php echo $config['log_graceperiod'] == $i ? 'SELECTED':''?> value="<?php echo $i?>"><?php echo $i?>&nbsp;<?php echo ($i>1)?'Meses':'Mes'?></option>
                        <?php 
                    }?>
                </select>
            </td>
        </tr>
        <tr><th>Inicio de secci&oacute;n excesivo:</th>
            <td>
                <select name="staff_max_logins">
                  <?php 
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['staff_max_logins']==$i)?'selected="selected"':''),$i);
                    }
                    ?>
                </select> Intentos permitidos dentro de los
                <select name="staff_login_timeout">
                  <?php 
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['staff_login_timeout']==$i)?'selected="selected"':''),$i);
                    }
                    ?>
                </select> Minutos establecidos
            </td>
        </tr>
        <tr><th>Desconexi&oacute;n Autom&aacute;tica Staff:</th>
            <td>
              <input type="text" name="staff_session_timeout" size=6 value="<?php echo $config['staff_session_timeout']?>">
                (<i>Inactividad max. en minutos. Poner 0 para desahabilitar </i>)
            </td>
        </tr>
       <tr><th>Enlazar IP a la sesi&oacute;n de Staff:</th>
            <td>
              <input type="checkbox" name="staff_ip_binding" <?php echo $config['staff_ip_binding']?'checked':''?>>
               La sesi&oacute;n del Staff se enlazara a la IP.
            </td>
        </tr>

        <tr><th>Inicio de secci&oacute;n excesivo Cliente:</th>
            <td>
                <select name="client_max_logins">
                  <?php 
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['client_max_logins']==$i)?'selected="selected"':''),$i);
                    }

                    ?>
                </select> Intentos permitidos dentro de los
                &nbsp;
                <select name="client_login_timeout">
                  <?php 
                    for ($i = 1; $i <= 10; $i++) {
                        echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['client_login_timeout']==$i)?'selected="selected"':''),$i);
                    }
                    ?>
                </select> Minutos establecidos
            </td>
        </tr>

        <tr><th>Desconexi&oacute;n Autom&aacute;tica Cliente:</th>
            <td>
              <input type="text" name="client_session_timeout" size=6 value="<?php echo $config['client_session_timeout']?>">
                (<i>Inactividad max. en minutos. Poner 0 para desahabilitar</i>)
            </td>
        </tr>
        <tr><th>Habilitar Enlaces:</th>
            <td>
              <input type="checkbox" name="clickable_urls" <?php echo $config['clickable_urls']?'checked':''?>>
                Hacer los enlaces activos
            </td>
        </tr>
        <tr><th>Habilitar Auto Cron:</th>
            <td>
              <input type="checkbox" name="enable_auto_cron" <?php echo $config['enable_auto_cron']?'checked':''?>>
                Habilitar llamada cron en la actividad del Staff
            </td>
        </tr>
    </table>
    
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header"><td colspan=2>Ajustes de Fecha y Hora</td></tr>
        <tr class="subheader">
            <td colspan=2>Consulta el <a href="http://php.net/date" target="_blank">Manual PHP oficial</a> para aplicar los par&aacute;metros soportados.</td>
        </tr>
        <tr><th>Formato de Hora:</th>
            <td>
                <input type="text" name="time_format" value="<?php echo $config['time_format']?>">
                    &nbsp;<font class="error">*&nbsp;<?php echo $errors['time_format']?></font>
                    <i><?php echo Format::date($config['time_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving'])?></i></td>
        </tr>
        <tr><th>Formato de Fecha:</th>
            <td><input type="text" name="date_format" value="<?php echo $config['date_format']?>">
                        &nbsp;<font class="error">*&nbsp;<?php echo $errors['date_format']?></font>
                        <i><?php echo Format::date($config['date_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving'])?></i>
            </td>
        </tr>
        <tr><th>Formato de Fecha y Hora:</th>
            <td><input type="text" name="datetime_format" value="<?php echo $config['datetime_format']?>">
                        &nbsp;<font class="error">*&nbsp;<?php echo $errors['datetime_format']?></font>
                        <i><?php echo Format::date($config['datetime_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving'])?></i>
            </td>
        </tr>
        <tr><th>Formato de Dia, Fecha y Hora:</th>
            <td><input type="text" name="daydatetime_format" value="<?php echo $config['daydatetime_format']?>">
                        &nbsp;<font class="error">*&nbsp;<?php echo $errors['daydatetime_format']?></font>
                        <i><?php echo Format::date($config['daydatetime_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving'])?></i>
            </td>
        </tr>
        <tr><th>Zona Horaria:</th>
            <td>
                <select name="timezone_offset">
                    <?php 
                    $gmoffset = date("Z") / 3600; //Server's offset.
                    echo"<option value=\"$gmoffset\">Server Time (GMT $gmoffset:00)</option>"; //Default if all fails.
                    $timezones= db_query('SELECT offset,timezone FROM '.TIMEZONE_TABLE);
                    while (list($offset,$tz) = db_fetch_row($timezones)){
                        $selected = ($config['timezone_offset'] ==$offset) ?'SELECTED':'';
                        $tag=($offset)?"GMT $offset ($tz)":" GMT ($tz)";
                        ?>
                        <option value="<?php echo $offset?>"<?php echo $selected?>><?php echo $tag?></option>
                        <?php 
                    }?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Horario de Verano:</th>
            <td>
                <input type="checkbox" name="enable_daylight_saving" <?php echo $config['enable_daylight_saving'] ? 'checked': ''?>>Habilitar horario de verano
            </td>
        </tr>
    </table>
   
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header"><td colspan=2>Configuraci&oacute;n y Opciones de tickets</td></tr>
        <tr class="subheader"><td colspan=2>Cuando se activa, se abre autom&aacute;ticamente las entradas para Tickets</td></tr>
        <tr><th valign="top">Generaci&oacute;n de IDs:</th>
            <td>
                <input type="radio" name="random_ticket_ids"  value="0"   <?php echo !$config['random_ticket_ids']?'checked':''?> /> Secuencial
                <input type="radio" name="random_ticket_ids"  value="1"   <?php echo $config['random_ticket_ids']?'checked':''?> />Aleatoria  (Recomendado)
            </td>
        </tr>
        <tr><th valign="top">Prioridad de los Tickets:</th>
            <td>
                <select name="default_priority_id">
                    <?php 
                    $priorities= db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
                    while (list($id,$tag) = db_fetch_row($priorities)){ ?>
                        <option value="<?php echo $id?>"<?php echo ($config['default_priority_id']==$id)?'selected':''?>><?php echo $tag?></option>
                    <?php 
                    }?>
                </select> &nbsp;Prioridad por defecto<br/>
                <input type="checkbox" name="allow_priority_change" <?php echo $config['allow_priority_change'] ?'checked':''?>>
                    Permitir al usuario establecer la prioridad (para tickets nuevos)<br/>
                <input type="checkbox" name="use_email_priority" <?php echo $config['use_email_priority'] ?'checked':''?> >
                    Utilizar la prioridad por email cuando sea posible (para tickets desde el correo) 

            </td>
        </tr>
        <tr><th>Numero <b>M&aacute;ximo</b> de Tickets:</th>
            <td>
              <input type="text" name="max_open_tickets" size=4 value="<?php echo $config['max_open_tickets']?>"> 
                por Email. (<i>Ayuda contra el Spam y el control de flujos. 0 para ilimitados </i>)
            </td>
        </tr>
        <tr><th>Tiempo de AutoBloqueo:</td>
            <td>
              <input type="text" name="autolock_minutes" size=4 value="<?php echo $config['autolock_minutes']?>">
                 <font class="error"><?php echo $errors['autolock_minutes']?></font>
                (<i>(Minutos a bloquear un ticket en curso. 0 para deshabilitar el bloqueo)</i>)
            </td>
        </tr>
        <tr><th>Plazo del Ticket:</th>
            <td>
              <input type="text" name="overdue_grace_period" size=4 value="<?php echo $config['overdue_grace_period']?>">
                (<i>Tiempo, en horas, antes de que un ticket sea marcado como Pasado. 0 para deshabilitar.</i>)
            </td>
        </tr>
        <tr><th>Tickets Reabiertos:</th>
            <td>
              <input type="checkbox" name="auto_assign_reopened_tickets" <?php echo $config['auto_assign_reopened_tickets'] ? 'checked': ''?>> 
                Auto asignar ticket reabierto al &uacute;ltimo editor. (<i> 3 Meses limite</i>)
            </td>
        </tr>
        <tr><th>Tickets Asignados:</th>
            <td>
              <input type="checkbox" name="show_assigned_tickets" <?php echo $config['show_assigned_tickets']?'checked':''?>>
                Mostrar los tickets asignados en la cola de "Abiertos".
            </td>
        </tr>
        <tr><th>Tickets Respondidos:</th>
            <td>
              <input type="checkbox" name="show_answered_tickets" <?php echo $config['show_answered_tickets']?'checked':''?>>
                Mostrar los tickets respondidos en la cola de "Abiertos".
            </td>
        </tr>
        <tr><th>Registro de Actividad de Tickets:</th>
            <td>
              <input type="checkbox" name="log_ticket_activity" <?php echo $config['log_ticket_activity']?'checked':''?>>
                Registra la actividad de los tickets como notas internas .
            </td>
        </tr>
        <tr><th>Identidad del Staff:</th>
            <td>
              <input type="checkbox" name="hide_staff_name" <?php echo $config['hide_staff_name']?'checked':''?>>
                Ocultar el nombre del Staff en las respuestas.
            </td>
        </tr>
        <tr><th>Verificaci&oacute;n Humana:</th>
            <td>
                <?php 
                   if($config['enable_captcha'] && !$errors['enable_captcha']) {?>
                        <img src="../captcha.php" border="0" align="left">&nbsp;
                <?php }?>
              <input type="checkbox" name="enable_captcha" <?php echo $config['enable_captcha']?'checked':''?>>
                Activar captcha para los Tickets nuevos via web.&nbsp;<font class="error">&nbsp;<?php echo $errors['enable_captcha']?></font><br/>
            </td>
        </tr>

    </table>
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header"><td colspan=2 >Opciones de Correo Electr&oacute;nico</td></tr>
        <tr class="subheader"><td colspan=2>Ten en cuenta que la Configuraci&oacute;n Global puede ser deshabilitada a nivel de departamento y de correo.</td></tr>
        <tr><th valign="top"><br><b>Correos Entrantes</b>:</th>
            <td><i>Para la funci&oacute;n de captura de correo POP/IMAP (email fetch) debe establecer tareas cronometradas o simplemente permitir el auto-cron. <a href="http://osticket.com/wiki/POP3/IMAP_Settings" target="_blank">Guia Email Fetch</a> (en Ingles)</i><br/>
                <input type="checkbox" name="enable_mail_fetch" value=1 <?php echo $config['enable_mail_fetch']? 'checked': ''?>  >Habilitar Captura de Correo POP/IMAP
                    &nbsp;&nbsp;(<i>(Configuraci&oacute;n global que puede deshabilitarse a nivel de email) </i>) <br/>
                <input type="checkbox" name="enable_email_piping" value=1 <?php echo $config['enable_email_piping']? 'checked': ''?>  > Habilitar Email Piping
               &nbsp;(<i>M&aacute;s Informaci&oacute;n y Configuraci&oacute;n: <a href="http://osticket.com/wiki/Email_Piping" target="_blank">Guia Email Piping</a> (en Ingles)</i>)<br/>
                <input type="checkbox" name="strip_quoted_reply" <?php echo $config['strip_quoted_reply'] ? 'checked':''?>>
                    Separar respuestas incluidas (<i>Depende del siguiente parametro:</i>)<br/>
                <input type="text" name="reply_separator" value="<?php echo $config['reply_separator']?>"> Separador
                &nbsp;<font class="error">&nbsp;<?php echo $errors['reply_separator']?></font>
            </td>
        </tr>
        <tr><th valign="top"><br><b>Correos Salientes</b>:</th>
            <td>
                <i><b>Email por defecto:</b> S&oacute;lo se refiere a correos electr&oacute;nicos salientes sin configuraci&oacute;n SMTP.</i><br/>
                <select name="default_smtp_id"
                    onChange="document.getElementById('overwrite').style.display=(this.options[this.selectedIndex].value>0)?'block':'none';">
                    <option value=0>Seleccionar</option>
                    <option value=0 selected="selected">Ninguno: Utilice la funci&oacute;n de correo PHP</option>
                    <?php 
                    $emails=db_query('SELECT email_id,email,name,smtp_host FROM '.EMAIL_TABLE.' WHERE smtp_active=1');
                    if($emails && db_num_rows($emails)) {
                        while (list($id,$email,$name,$host) = db_fetch_row($emails)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            $email=sprintf('%s (%s)',$email,$host);
                            ?>
                            <option value="<?php echo $id?>"<?php echo ($config['default_smtp_id']==$id)?'selected="selected"':''?>><?php echo $email?></option>
                        <?php 
                        }
                    }?>
                 </select>&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['default_smtp_id']?></font><br/>
                 <span id="overwrite" style="display:<?php echo ($config['default_smtp_id']?'display':'none')?>">
                    <input type="checkbox" name="spoof_default_smtp" <?php echo $config['spoof_default_smtp'] ? 'checked':''?>>
                        Permitir la suplantaci&oacute;n (sin sobreescribir).&nbsp;<font class="error">&nbsp;<?php echo $errors['spoof_default_smtp']?></font><br/>
                        </span>
             </td>
        </tr>
        <tr><th>Correo del Sistema :</th>
            <td>
                <select name="default_email_id">
                    <option value=0 disabled>Seleccionar</option>
                    <?php 
                    $emails=db_query('SELECT email_id,email,name FROM '.EMAIL_TABLE);
                    while (list($id,$email,$name) = db_fetch_row($emails)){ 
                        $email=$name?"$name &lt;$email&gt;":$email;
                        ?>
                     <option value="<?php echo $id?>"<?php echo ($config['default_email_id']==$id)?'selected':''?>><?php echo $email?></option>
                    <?php 
                    }?>
                 </select>
                 &nbsp;<font class="error">*&nbsp;<?php echo $errors['default_email_id']?></font></td>
        </tr>
        <tr><th valign="top">Correo de Alertas:</th>
            <td>
                <select name="alert_email_id">
                    <option value=0 disabled>Seleccionar</option>
                    <option value=0 selected="selected">Usar correo del sistema (arriba)</option>
                    <?php 
                    $emails=db_query('SELECT email_id,email,name FROM '.EMAIL_TABLE.' WHERE email_id != '.db_input($config['default_email_id']));
                    while (list($id,$email,$name) = db_fetch_row($emails)){
                        $email=$name?"$name &lt;$email&gt;":$email;
                        ?>
                     <option value="<?php echo $id?>"<?php echo ($config['alert_email_id']==$id)?'selected':''?>><?php echo $email?></option>
                    <?php 
                    }?>
                 </select>
                 &nbsp;<font class="error">*&nbsp;<?php echo $errors['alert_email_id']?></font>
                <br/><i>Se utiliza para enviar alertas y notas al Staff.</i>
            </td>
        </tr>
        <tr><th>Correo del Admin. del Sistema:</th>
            <td>
                <input type="text" size=25 name="admin_email" value="<?php echo $config['admin_email']?>">
                    &nbsp;<font class="error">*&nbsp;<?php echo $errors['admin_email']?></font></td>
        </tr>
    </table>

    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header"><td colspan=2>Respuestas Autom&aacute;ticas &nbsp;(Configuraci&oacute;n Global)</td></tr>
        <tr class="subheader"><td colspan=2">Ajuste global que puede ser desactivado a nivel de departamento.</td></tr>
        <tr><th valign="top">Ticket Nuevo:</th>
            <td><i>La autorespuesta incluye el ID del ticket necesario para verificar su estado en l&iacute;nea</i><br>
                <input type="radio" name="ticket_autoresponder"  value="1"   <?php echo $config['ticket_autoresponder']?'checked':''?> />Activar
                <input type="radio" name="ticket_autoresponder"  value="0"   <?php echo !$config['ticket_autoresponder']?'checked':''?> />Desactivar
            </td>
        </tr>
        <tr><th valign="top">Ticket Nuevo del Staff:</th>
            <td><i>Envio de notificaci&oacute;n cuando el Staff crea un ticket en nombre del usuario.</i><br>
                <input type="radio" name="ticket_notice_active"  value="1"   <?php echo $config['ticket_notice_active']?'checked':''?> />Activar
                <input type="radio" name="ticket_notice_active"  value="0"   <?php echo !$config['ticket_notice_active']?'checked':''?> />Desactivar
            </td>
        </tr>
        <tr><th valign="top">Mensaje Nuevo:</th>
            <td><i>Mensaje adjunto a una confirmaci&oacute;n de ticket existente</i><br>
                <input type="radio" name="message_autoresponder"  value="1"   <?php echo $config['message_autoresponder']?'checked':''?> />Activar
                <input type="radio" name="message_autoresponder"  value="0"   <?php echo !$config['message_autoresponder']?'checked':''?> />Desactivar
            </td>
        </tr>
        <tr><th valign="top">Notificaci&oacute; de Exceso:</th>
            <td><i>La notificación de bloqueo temporal se enviá una única vez cuando el usuario viola los limites establecidos.</i><br/>               
                <input type="radio" name="overlimit_notice_active"  value="1"   <?php echo $config['overlimit_notice_active']?'checked':''?> />Activar
                <input type="radio" name="overlimit_notice_active"  value="0"   <?php echo !$config['overlimit_notice_active']?'checked':''?> />Desactivar
                <br><i><b>NOTA:</b> los administradores recibe alertas de todas los bloqueos por defecto.</i><br>
            </td>
        </tr>
    </table>
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
        <tr class="header"><td colspan=2>Alertas y Notificaciones</td></tr>
        <tr class="subheader"><td colspan=2>
            Las Notificaciones enviadas a los usuarios Utilizan el "No Reply Email" tal y como el Staff utiliza el "Correo de Alerta". Configurado m&aacute;s arriba.</td>
        </tr>
        <tr><th valign="top">Alertas de Ticket Nuevo:</th>
            <td>
                <input type="radio" name="ticket_alert_active"  value="1"   <?php echo $config['ticket_alert_active']?'checked':''?> />Activar
                <input type="radio" name="ticket_alert_active"  value="0"   <?php echo !$config['ticket_alert_active']?'checked':''?> />Desactivar
                <br><i>Seleccionar destinarios</i>&nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_alert_active']?></font><br>
                <input type="checkbox" name="ticket_alert_admin" <?php echo $config['ticket_alert_admin']?'checked':''?>> Administradores
                <input type="checkbox" name="ticket_alert_dept_manager" <?php echo $config['ticket_alert_dept_manager']?'checked':''?>> Jefe Departamento
                <input type="checkbox" name="ticket_alert_dept_members" <?php echo $config['ticket_alert_dept_members']?'checked':''?>> Miembros Departamento
            </td>
        </tr>
        <tr><th valign="top">Alerta de Nuevo Mensaje:</th>
            <td>
              <input type="radio" name="message_alert_active"  value="1"   <?php echo $config['message_alert_active']?'checked':''?> />Activar
              <input type="radio" name="message_alert_active"  value="0"   <?php echo !$config['message_alert_active']?'checked':''?> />Desactivar
              <br><i>Seleccionar destinarios</i>&nbsp;<font class="error">&nbsp;<?php echo $errors['message_alert_active']?></font><br>
              <input type="checkbox" name="message_alert_laststaff" <?php echo $config['message_alert_laststaff']?'checked':''?>> Ultimo en responder
              <input type="checkbox" name="message_alert_assigned" <?php echo $config['message_alert_assigned']?'checked':''?>> Staff asignado
              <input type="checkbox" name="message_alert_dept_manager" <?php echo $config['message_alert_dept_manager']?'checked':''?>> Jefe Departamento
            </td>
        </tr>
        <tr><th valign="top">Alerta de Nueva Nota interna:</th>
            <td>
              <input type="radio" name="note_alert_active"  value="1"   <?php echo $config['note_alert_active']?'checked':''?> />Activar
              <input type="radio" name="note_alert_active"  value="0"   <?php echo !$config['note_alert_active']?'checked':''?> />Desactivar
              <br><i>Seleccionar destinarios</i>&nbsp;<font class="error">&nbsp;<?php echo $errors['note_alert_active']?></font><br>
              <input type="checkbox" name="note_alert_laststaff" <?php echo $config['note_alert_laststaff']?'checked':''?>> Ultimo en responder
              <input type="checkbox" name="note_alert_assigned" <?php echo $config['note_alert_assigned']?'checked':''?>> Staff asignado
              <input type="checkbox" name="note_alert_dept_manager" <?php echo $config['note_alert_dept_manager']?'checked':''?>> Jefe Departamento
            </td>
        </tr>
        <tr><th valign="top">Alerta de Ticket Vencido:</th>
            <td>
              <input type="radio" name="overdue_alert_active"  value="1"   <?php echo $config['overdue_alert_active']?'checked':''?> />Activar
              <input type="radio" name="overdue_alert_active"  value="0"   <?php echo !$config['overdue_alert_active']?'checked':''?> />Desactivar
              <br><i>El correo Admin recibe la alerta por defecto. Selecciona destinatarios adicionales:</i>&nbsp;<font class="error">&nbsp;<?php echo $errors['overdue_alert_active']?></font><br>
              <input type="checkbox" name="overdue_alert_assigned" <?php echo $config['overdue_alert_assigned']?'checked':''?>> Staff asignado
              <input type="checkbox" name="overdue_alert_dept_manager" <?php echo $config['overdue_alert_dept_manager']?'checked':''?>> Jefe Departamento
              <input type="checkbox" name="overdue_alert_dept_members" <?php echo $config['overdue_alert_dept_members']?'checked':''?>> Miembros Departamento
            </td>
        </tr>
        <tr><th valign="top">Errores del Sistema:</th>
            <td><i>	Los tipos de errores selecionados se env&iacute;an al correo Admin</i><br>
              <input type="checkbox" name="send_sys_errors" <?php echo $config['send_sys_errors']?'checked':'checked'?> disabled>Errores de Sistema
              <input type="checkbox" name="send_sql_errors" <?php echo $config['send_sql_errors']?'checked':''?>>Errores SQL
              <input type="checkbox" name="send_login_errors" <?php echo $config['send_login_errors']?'checked':''?>>Exceso de inicio de sesi&oacute;n
            </td>
        </tr> 
        
    </table>
 </td></tr>
 <tr>
    <td style="padding:10px 0 10px 240px;">
        <input class="button" type="submit" name="submit" value="Guardar Cambios">
        <input class="button" type="reset" name="reset" value="Restablecer">
    </td>
 </tr>
 </form>
</table>
