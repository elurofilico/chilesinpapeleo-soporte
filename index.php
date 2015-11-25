<?php
/*********************************************************************
    index.php

    Helpdesk landing page. Please customize it to fit your needs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/
require('client.inc.php');
//We are only showing landing page to users who are not logged in.
if($thisclient && is_object($thisclient) && $thisclient->isValid()) {
    require('tickets.php');
    exit;
}


require(CLIENTINC_DIR.'header.inc.php');
?>
<div class="hero-unit">
  <h1><?php  echo Format::htmlchars($title)?></h1>
  <h3>Unidad de Modernización y Gobierno Digital</h3>
  <p>Plataforma de atención para servicios públicos</p>
</div>
<div class="row">
  <div class="span6">
    <h2>Abrir nuevo ticket</h2>
    <p>Por favor, entregue el mayor número de detalles posibles. Si desea actualizar un ticket ya ingresado utilice el formulario a la derecha.</p>
    <p><a class="btn btn-success" href="open.php"><i class="icon-plus-sign icon-white"></i> Abrir nuevo ticket</a></p>
  </div>
  <div class="span6">
    <h2>Consultar estado de un ticket</h2>
    <p>Realice seguimiento al estado de avance de un ticket abierto.</p>
    <form action="login.php" method="post" class="form-horizontal">
      <div class="control-group">
        <label class="control-label">Correo electrónico:</label>
        <div class="controls">
          <input type="text" name="lemail" placeholder="correo electrónico">
        </div>
      <span class="help-block controls">Ej.: correo@electronico.cl</span>
      </div>
      <div class="control-group">
      <label class="control-label">ID Ticket:</label>
      <div class="controls">
        <input type="text" name="lticket" placeholder="número ticket">
      </div>
      <span class="help-block controls">Ej.: 12345</span>
  </div>
  <div class="control-group">
    <button type="submit" class="btn btn-info controls"><i class="icon-search icon-white"></i> Ver estado</button>
  </div>
    </form>
  </div>
</div>
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>
