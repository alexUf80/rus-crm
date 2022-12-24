<?php

class Notify extends Core
{
    function email($to, $subject, $message, $from = '', $reply_to = '', $filenames = array())
    {
        $EOL = "\r\n";
        
        $subject = "=?utf-8?B?".base64_encode($subject)."?=";
        
        if (empty($from))
            $from = $this->settings->notify_from_email;
        
        if (empty($filenames))
        {
        	$headers = "MIME-Version: 1.0{$EOL}" ;
    	    $headers .= "Content-type: text/html; charset=utf-8; {$EOL}"; 
        	$headers .= "From: {$from}{$EOL}";
        	if(!empty($reply_to))
    	    	$headers .= "reply-to: $reply_to{$EOL}";
        	
        	@mail($to, $subject, $message, $headers);
        }
        else
        {
            $boundary = "--".md5(uniqid(time()));
        	$headers = "MIME-Version: 1.0{$EOL}" ;
    	    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"{$EOL}"; 
        	$headers .= "From: {$from}{$EOL}";
        	if(!empty($reply_to))
    	    	$headers .= "reply-to: $reply_to{$EOL}";
        	
            $multipart = "--{$boundary}{$EOL}"; 
            $multipart .= "Content-Type: text/html; charset=utf-8{$EOL}";
            $multipart .= "Content-Transfer-Encoding: base64{$EOL}";    
            $multipart .= "{$EOL}";
            $multipart .= chunk_split(base64_encode($message));
            
            foreach ($filenames as $filename)
            {
                $temp_filename = $this->config->root_dir.'files/mails/'.$boundary;
                
                if (copy($filename, $temp_filename) && ($fp = fopen($temp_filename,"r")))
                {
                    $fileinfo = pathinfo($filename);
                    
                    $multipart .= "{$EOL}--{$boundary}{$EOL}"; 
                    $multipart .= "Content-Type: application/octet-stream; name=\"{$fileinfo['basename']}\"{$EOL}";  
                    $multipart .= "Content-Transfer-Encoding: base64{$EOL}"; 
                    $multipart .= "Content-Disposition: attachment; filename=\"{$fileinfo['basename']}\"{$EOL}";
                    $multipart .= "{$EOL}";
                    
                    $file64 = fread($fp, filesize($temp_filename)); 
                    fclose($fp); 
                    
                    $multipart .= chunk_split(base64_encode($file64));
                    unlink($temp_filename);
                }
            }
            $multipart .= "{$EOL}--{$boundary}--{$EOL}"; 
                
            mail($to, $subject, $multipart, $headers);
        }
        
    }


	public function send_reject_reason($order_id)
	{
		if(!($order = $this->orders->get_order(intval($order_id))) || empty($order->email))
			return false;
		
        $this->design->assign('order', $order);
        
		$email_template = $this->design->fetch($this->config->root_dir.'theme/manager/email/reject_reason.tpl');
		$subject = $this->design->get_var('subject');
		// Отправляем письмо
		$this->email($order->email, $subject, $email_template, $this->settings->notify_from_email);	
	}

}