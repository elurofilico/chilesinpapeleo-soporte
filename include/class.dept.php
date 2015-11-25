<?php
/*********************************************************************
    class.dept.php
    
    Department class

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/
class Dept {
    var $id;
    var $name;
    var $signature;
    
    var $tplId;

    var $emailId;
    var $email;

    var $autorespEmail;
    
    var $managerId;
    var $manager;
    
    var $row;
  
    function Dept($id=0){
        $this->id=0;
        if($id && ($info=$this->getInfoById($id))){
            $this->row=$info;
            $this->id=$info['dept_id'];
            $this->tplId=$info['tpl_id'];
            $this->emailId=$info['email_id'];
            $this->managerId=$info['manager_id'];
            $this->deptname=$info['dept_name'];
            $this->signature=$info['dept_signature'];
            $this->getEmail(); //Auto load email struct.
        }
    }

    function getId(){
        return $this->id;
    }
    
    function getName(){
        return $this->deptname;
    }

        
    function getEmailId(){
        return $this->emailId;
    }

    function getEmail(){
        
        if(!$this->email && $this->emailId)
            $this->email= new Email($this->emailId);
            
        return $this->email;
    }

    function getTemplateId() {
         return $this->tplId;
    }
   
    function getAutoRespEmail() {

        if(!$this->autorespEmail && $this->row['autoresp_email_id'])
            $this->autorespEmail= new Email($this->row['autoresp_email_id']);
        else // Defualt to dept email if autoresp is not specified.
            $this->autorespEmail= $this->getEmail();

        return $this->autorespEmail;
    }
 
    function getEmailAddress() {
        return $this->email?$this->email->getAddress():null;
    }

    function getSignature() {
        
        return $this->signature;
    }

    function canAppendSignature() {
        return ($this->signature && $this->row['can_append_signature'])?true:false;
    }
    
    function getManagerId(){
        return $this->managerId;
    }

    function getManager(){
     
        if(!$this->manager && $this->managerId)
            $this->manager= new Staff($this->managerId);
        
        return $this->manager;
    }

    function isPublic() {
         return $this->row['ispublic']?true:false;
    }
    
    function autoRespONNewTicket() {
        return $this->row['ticket_auto_response']?true:false;
    }
        
    function autoRespONNewMessage() {
        return $this->row['message_auto_response']?true:false;
    }

    function noreplyAutoResp(){
         return $this->row['noreply_autoresp']?true:false;
    }
    
    function getInfo() {
        return $this->row;
    }

    function update($vars,&$errors) {
        if($this->save($this->getId(),$vars,$errors)){
            return true;
        }
        return false;
    }


    
	function getInfoById($id) {
		$sql='SELECT * FROM '.DEPT_TABLE.' WHERE dept_id='.db_input($id);
		if(($res=db_query($sql)) && db_num_rows($res))
            return db_fetch_array($res);
        
        return null;
	}

    
	function getIdByName($name) {
        $id=0;
        $sql ='SELECT dept_id FROM '.DEPT_TABLE.' WHERE dept_name='.db_input($name);
        if(($res=db_query($sql)) && db_num_rows($res))
            list($id)=db_fetch_row($res);

        return $id;
    }

    function getIdByEmail($email) {
        $id=0;
        $sql ='SELECT dept_id FROM '.DEPT_TABLE.' WHERE dept_email='.db_input($email);
        if(($res=db_query($sql)) && db_num_rows($res))
            list($id)=db_fetch_row($res);

        return $id;
    }

    function getNameById($id) {
        $sql ='SELECT dept_name FROM '.DEPT_TABLE.' WHERE dept_id='.db_input($id);
        if(($res=db_query($sql)) && db_num_rows($res))
            list($name)=db_fetch_row($res);
        return $name;
    }

    function getDefaultDeptName() {
        global $cfg;
        return Dept::getNameById($cfg->getDefaultDeptId());
    }


    function create($vars,&$errors) {
        return Dept::save(0,$vars,$errors);
    }


    function delete($id) {
        global $cfg; 
        if($id==$cfg->getDefaultDeptId())
            return 0;
        
        $sql='DELETE FROM '.DEPT_TABLE.' WHERE dept_id='.db_input($id);
        if(db_query($sql) && ($num=db_affected_rows())){
            // DO SOME HOUSE CLEANING
            //TODO: Do insert select internal note...
            //Move tickets to default Dept.
            db_query('UPDATE '.TICKET_TABLE.' SET dept_id='.db_input($cfg->getDefaultDeptId()).' WHERE dept_id='.db_input($id));
            //Move Dept members 
            //This should never happen..since delete should be issued only to empty Depts...but check it anyways 
            db_query('UPDATE '.STAFF_TABLE.' SET dept_id='.db_input($cfg->getDefaultDeptId()).' WHERE dept_id='.db_input($id));
            //make help topic using the dept default to default-dept.
            db_query('UPDATE '.TOPIC_TABLE.' SET dept_id='.db_input($cfg->getDefaultDeptId()).' WHERE dept_id='.db_input($id));
            return $num;
        }
        return 0;
        
    }

    function save($id,$vars,&$errors) {
        global $cfg;
                
        if($id && $id!=$_POST['dept_id'])
            $errors['err']='ID de departamento faltante o invalida';
            
        if(!$_POST['email_id'] || !is_numeric($_POST['email_id']))
            $errors['email_id']='Email de departamento Requerido';
            
        if(!is_numeric($_POST['tpl_id']))
            $errors['tpl_id']='Plantilla Requerida';
            
        if(!$_POST['dept_name']) {
            $errors['dept_name']='Nombre de departamento Requerido';
        }elseif(strlen($_POST['dept_name'])<4) {
            $errors['dept_name']='El nombre del departamento debe tener a menos 4 caracteres';
        }else{
            $sql='SELECT dept_id FROM '.DEPT_TABLE.' WHERE dept_name='.db_input($_POST['dept_name']);
            if($id)
                $sql.=' AND dept_id!='.db_input($id);
                
            if(db_num_rows(db_query($sql)))
                $errors['dept_name']='Este departamento ya existe';
        }

        if($_POST['ispublic'] && !$_POST['dept_signature'])
            $errors['dept_signature']='Firma Requerida';
            
        if(!$_POST['ispublic'] && ($_POST['dept_id']==$cfg->getDefaultDeptId()))
            $errors['ispublic']='El departamento por defecto no puede ser privado';

        if(!$errors){
        
            $sql=' SET updated=NOW() '.
                 ',ispublic='.db_input($_POST['ispublic']).
                 ',email_id='.db_input($_POST['email_id']).
                 ',tpl_id='.db_input($_POST['tpl_id']).
                 ',autoresp_email_id='.db_input($_POST['autoresp_email_id']).
                 ',manager_id='.db_input($_POST['manager_id']?$_POST['manager_id']:0).
                 ',dept_name='.db_input(Format::striptags($_POST['dept_name'])).
                 ',dept_signature='.db_input(Format::striptags($_POST['dept_signature'])).
                 ',ticket_auto_response='.db_input($_POST['ticket_auto_response']).
                 ',message_auto_response='.db_input($_POST['message_auto_response']).
                 ',can_append_signature='.db_input(isset($_POST['can_append_signature'])?1:0);

            if($id) {
                $sql='UPDATE '.DEPT_TABLE.' '.$sql.' WHERE dept_id='.db_input($id);
                if(!db_query($sql) || !db_affected_rows())
                    $errors['err']='No se puede actualizar '.Format::input($_POST['dept_name']).' Se produjo un error en el departamento';
            }else{
                $sql='INSERT INTO '.DEPT_TABLE.' '.$sql.',created=NOW()';
                if(db_query($sql) && ($deptID=db_insert_id()))
                    return $deptID;

                $errors['err']='No se puede crear el departamento. Error interno';
            }
        }

        return $errors?false:true;
    }
}
?>