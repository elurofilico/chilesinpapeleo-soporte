<?php 
if(!defined('OSTADMININC') || !$thisuser->isadmin() || !is_object($template)) die('Acceso Denegado');
$tpl=($errors && $_POST)?Format::input($_POST):Format::htmlchars($template->getInfo());
?>
<div class="msg">Plantillas de Correo (autorespuestas)</div>
<table width="100%" border="0" cellspacing=0 cellpadding=0>
  <form action="admin.php?t=templates" method="post">
    <input type="hidden" name="t" value="templates">
    <input type="hidden" name="do" value="update">
    <input type="hidden" name="id" value="<?php echo $template->getId()?>">
    <tr><td>
        <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform tpl">
            <tr class="header"><td colspan=2 >Informaci&oacute;n de la plantilla</td></tr>
            <tr class="subheader"><td colspan=2><b>Ultima Actualizaci&oacute;n <?php echo Format::db_daydatetime($template->getUpdateDate())?></b></td></tr>
            <tr>
                <th>Nombre</th>
                <td>
                    <input type="text" size="45" name="name" value="<?php echo $tpl['name']?>">
                            &nbsp;<font class="error">*&nbsp;<?php echo $errors['name']?></font></td>
            </tr>
            <tr>
                <th>Nota Interna:</th>
                <td><i>Notas Administrativas</i>&nbsp;<font class="error">&nbsp;<?php echo $errors['notes']?></font>
                    <textarea rows="5" cols="75" name="notes"><?php echo $tpl['notes']?></textarea>
                        &nbsp;<font class="error">&nbsp;<?php echo $errors['notes']?></font></td>
            </tr>
        </table>
        <div class="msg">Usuario</div>
        <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform tpl">
            <tr class="header"><td colspan=2 >Respuesta autom&aacute;tica para Nuevo Ticket </td></tr>
            <tr class="subheader"><td colspan=2 >
                Respuesta autom&aacute;tica enviada a los usuarios al abrir un ticket (si est&aacute; habilitado)
	        En esta respuesta se le manda al usuario la ID del Ticket que le facilita comprobar el estado en l&iacute;nea.</td>
                </tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="ticket_autoresp_subj" value="<?php echo $tpl['ticket_autoresp_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_autoresp_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</th>
                <td><textarea rows="7" cols="75" name="ticket_autoresp_body"><?php echo $tpl['ticket_autoresp_body']?></textarea>
                        &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_autoresp_body']?></font></td>
            </tr>
            <tr class="header"><td colspan=2 >Respuesta autom&aacute;tica para Nuevo Mensaje</td></tr>
            <tr class="subheader"><td colspan=2 > 
                Confirmaci&oacute;n enviada al usuario cuando se a&ntilde;ade un mensaje a un ticket existente (para tickets por correo y por web)</td>
            </tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="message_autoresp_subj" value="<?php echo $tpl['message_autoresp_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['message_autoresp_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</th>
                <td><textarea rows="7" cols="75" name="message_autoresp_body"><?php echo $tpl['message_autoresp_body']?></textarea>
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['message_autoresp_body']?></font></td>
            </tr>
            <tr class="header"><td colspan=2 >Notificaci&oacute;n de Nuevos Tickets</td></tr>
            <tr class="subheader"><td colspan=2 >
                Notificaci&oacute;n enviada al usuario, si esta activada, cuando un ticket es creado por el Staff en su nombre.</td>
                </tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="ticket_notice_subj" value="<?php echo $tpl['ticket_notice_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_notice_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</th>
                <td><textarea rows="7" cols="75" name="ticket_notice_body"><?php echo $tpl['ticket_notice_body']?></textarea>
                        &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_notice_body']?></font></td>
            </tr>
            <tr class="header"><td  colspan=2 >Notificaci&oacute;n de limite de tickets m&aacute;ximos superados</td></tr>
            <tr class="subheader"><td colspan=2 >
                Una notificaci&oacute;n &uacute;nica se envi&aacute; cuando un usuario ha alcanzado el numero m&aacute;ximo de tickets definidos en las preferencias. 
                <br/>Los Admin reciben una alerta cada vez que una solicitud de ticket se deniega por este motivo. .
            </td></tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="ticket_overlimit_subj" value="<?php echo $tpl['ticket_overlimit_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_overlimit_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</th>
                <td><textarea rows="7" cols="75" name="ticket_overlimit_body"><?php echo $tpl['ticket_overlimit_body']?></textarea>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_overlimit_body']?></font></td>
            </tr>
            <tr class="header"><td colspan=2 >&nbsp;Respuesta a un ticket</td></tr>
            <tr class="subheader"><td colspan=2 >
                Plantilla de mensaje utilizada cuando se responde a un ticket o respuesta simple, alertando al usuario de que se ha repondido su Ticket.
            </td></tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="ticket_reply_subj" value="<?php echo $tpl['ticket_reply_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_reply_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</td>
                <td><textarea rows="7" cols="75" name="ticket_reply_body"><?php echo $tpl['ticket_reply_body']?></textarea>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_reply_body']?></font></td>
            </tr>
        </table>
        <span class="msg">Autorespuestas para el Staff</span>
        <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform tpl">
            <tr class="header"><td colspan=2 >Alerta de Ticket Nuevo</td></tr>
            <tr class="subheader"><td colspan=2 >Alerta enviada al staff (si se ha activado) al registrarse nuevos Tickets.</td></tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="ticket_alert_subj" value="<?php echo $tpl['ticket_alert_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_alert_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</th>
                <td><textarea rows="7" cols="75" name="ticket_alert_body"><?php echo $tpl['ticket_alert_body']?></textarea>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_alert_body']?></font></td>
            </tr>
            <tr class="header"><td colspan=2 >Alerta de Nuevos Mensajes</td></tr>
            <tr class="subheader"><td colspan=2 >Alerta enviada al staff (si se ha activado) cuando un usuario responde a un ticket existente.</td></tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="message_alert_subj" value="<?php echo $tpl['message_alert_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['message_alert_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</th>
                <td><textarea rows="7" cols="75" name="message_alert_body"><?php echo $tpl['message_alert_body']?></textarea>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['message_alert_body']?></font></td>
            </tr>


            <tr class="header"><td colspan=2 >Alerta de Nueva Nota Interna</td></tr>
            <tr class="subheader"><td colspan=2 >Alerta enviada al staff (si se ha activado) cuando se a&ntilde;ade una nota interna a un ticket.</td></tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="note_alert_subj" value="<?php echo $tpl['note_alert_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['note_alert_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</th>
                <td><textarea rows="7" cols="75" name="note_alert_body"><?php echo $tpl['note_alert_body']?></textarea>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['note_alert_body']?></font></td>
            </tr>

            <tr class="header"><td colspan=2 >Alerta/Notificaci&oacute;n de asignaci&oacute;n de ticket</td></tr>
            <tr class="subheader"><td colspan=2 >Alerta enviada al staff cuando se le asigna un ticket.</td></tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="assigned_alert_subj" value="<?php echo $tpl['assigned_alert_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['assigned_alert_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</th>
                <td><textarea rows="7" cols="75" name="assigned_alert_body"><?php echo $tpl['assigned_alert_body']?></textarea>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['assigned_alert_body']?></font></td>
            </tr>
            <tr class="header"><td colspan=2 >Alerta/Notificaci&oacute;n de Ticket vencido</td></tr>
            <tr class="subheader"><td colspan=2 >Alerta enviada al staff sobre el estado de tickets o tickets vencidos.</td></tr>
            <tr>
                <th>Asunto</th>
                <td>
                    <input type="text" size="65" name="ticket_overdue_subj" value="<?php echo $tpl['ticket_overdue_subj']?>">
                            &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_overdue_subj']?></font></td>
            </tr>
            <tr>
                <th>Cuerpo del Mensaje:</th>
                <td><textarea rows="7" cols="75" name="ticket_overdue_body"><?php echo $tpl['ticket_overdue_body']?></textarea>
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_overdue_body']?></font></td>
            </tr>
        </table>
    </td></tr>
    <tr><td style="padding-left:175px">
        <input class="button" type="submit" name="submit" value="Guardar Cambios">
        <input class="button" type="reset" name="reset" value="Restablecer">
        <input class="button" type="button" name="cancel" value="Cancelar" onClick='window.location.href="admin.php?t=email"'></td>
    </tr>
  </form>
</table>
