<?php
// Placeholder for PHPMailerAutoload
// Please replace with the official PHPMailer library from https://github.com/PHPMailer/PHPMailer
class PHPMailer {
    public $ErrorInfo = '';
    public function setFrom($a,$b){}
    public function addAddress($a,$b=''){}
    public function addStringAttachment($data,$name){}
    public function send(){ return true; }
    public $Subject;
    public $Body;
}
?>
