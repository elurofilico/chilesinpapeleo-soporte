<?php
/*********************************************************************
    class.nav.php

    Navigation helper classes. Pointless BUT helps keep navigation clean and free from errors.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/
class StaffNav {
    var $tabs=array();
    var $submenu=array();

    var $activetab;
    var $ptype;

    function StaffNav($pagetype='staff'){
        global $thisuser;

        $this->ptype=$pagetype;
        $tabs=array();
        if($thisuser->isAdmin() && strcasecmp($pagetype,'admin')==0) {
            $tabs['dashboard']=array('desc'=>'Panel de Control','href'=>'admin.php?t=dashboard','title'=>'Administrar Registros');
            $tabs['settings']=array('desc'=>'Configuraci&oacute;n','href'=>'admin.php?t=settings','title'=>'Ajustes del Sitema');
            $tabs['emails']=array('desc'=>'Correos','href'=>'admin.php?t=email','title'=>'Ajustes de Correo');
            $tabs['topics']=array('desc'=>'&Aacute;reas de Ayuda','href'=>'admin.php?t=topics','title'=>'&Aacute;reas de Ayuda');
            $tabs['staff']=array('desc'=>'Personal','href'=>'admin.php?t=staff','title'=>'Admin y Miembros');
            $tabs['depts']=array('desc'=>'Departamentos','href'=>'admin.php?t=depts','title'=>'Departamentos');
        }else {
            $tabs['tickets']=array('desc'=>'Tickets','href'=>'tickets.php','title'=>'Lista de Tickets');
            if($thisuser && $thisuser->canManageKb()){
             $tabs['kbase']=array('desc'=>'Msg Predefinidos','href'=>'kb.php','title'=>'Gestionar Mensajes Predefinidos');
            }
            $tabs['directory']=array('desc'=>'Lista de Personal','href'=>'directory.php','title'=>'Lista de todo el personal');
            $tabs['profile']=array('desc'=>'Mi Cuenta','href'=>'profile.php','title'=>'Mi Perfil');
        }
        $this->tabs=$tabs;    
    }
    
    
    function setTabActive($tab){
            
        if($this->tabs[$tab]){
            $this->tabs[$tab]['active']=true;
            if($this->activetab && $this->activetab!=$tab && $this->tabs[$this->activetab])
                 $this->tabs[$this->activetab]['active']=false;
            $this->activetab=$tab;
            return true;
        }
        return false;
    }
    
    function addSubMenu($item,$tab=null) {
        
        $tab=$tab?$tab:$this->activetab;
        $this->submenu[$tab][]=$item;
    }

    
    
    function getActiveTab(){
        return $this->activetab;
    }        

    function getTabs(){
        return $this->tabs;
    }

    function getSubMenu($tab=null){
      
        $tab=$tab?$tab:$this->activetab;  
        return $this->submenu[$tab];
    }
    
}
?>
