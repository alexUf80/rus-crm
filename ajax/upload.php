<?php
header('Access-Control-Allow-Origin: https://nalic.eva-p.ru');

session_start();

chdir('..');
require('autoload.php');


class UploadApp extends Core
{
    private $response;
    private $user;
    
    private $max_file_size = 5242880;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->response = new StdClass();
        
        $this->run();
        
        $this->output();
    }
    
    public function run()
    {
        switch ($this->request->post('action', 'string')) :
            
            case 'add':
                $this->add();
            break;
            
            case 'remove':
                $this->remove();
            break;
            
            default:
                $this->response->error = 'undefined action';
            
        endswitch;
    }
    
    private function add()
    {
    	if ($file = $this->request->files('file'))
        {
            if ($type = $this->request->post('type', 'string'))
            {
                $user_id = $this->request->post('user_id', 'integer');
                if ($this->max_file_size > $file['size'])
                {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    do {
                        $new_filename = md5(microtime().rand()).'.'.$ext;
                    } while ($this->users->check_filename($new_filename));
                    
                    if($this->config->back_url == 'https://crm.rus-zaym.ru'){
                        $full_new_filename = '../../rus-client-prod/public_html/'.$this->config->user_files_dir.$new_filename;
                    }
                    else{
                        $full_new_filename = '../../rus-client/public_html/'.$this->config->user_files_dir.$new_filename;
                    }
                    // $full_new_filename = '../rus-client/'.$this->config->user_files_dir.$new_filename;
                    if (move_uploaded_file($file['tmp_name'], $full_new_filename))
                    {
                        $this->response->filename = $this->config->front_url.'/'.$this->config->user_files_dir.$new_filename;
                        
                        $this->response->id = $this->users->add_file(array(
                            'user_id' => $user_id,
                            'name' => $new_filename,
                            'type' => $type,
                            'status' => 0
                        ));

                        $this->response->success = 'added';
                    }
                    else
                    {
                        $this->response->error = 'error_uploading';
                    }
                }
                else
                {
                    $this->response->error = 'max_file_size';
                    $this->response->max_file_size = $this->max_file_size;
                }
            }
            else
            {
                $this->response->error = 'empty_type';
            }


        }
        else
        {
            $this->response->error = 'empty_file';
        }
    } 
    
    private function remove()
    {
        if ($id = $this->request->post('id', 'integer'))
        {
            $this->users->delete_file($id);
            
            $this->response->success = 'removed';
            
        }
        else
        {
            $this->response->error = 'empty_file_id';
        }
    } 
    
    private function output()
    {
   		header("Content-type: application/json; charset=UTF-8");
    	header("Cache-Control: must-revalidate");
    	header("Pragma: no-cache");
    	header("Expires: -1");		
        
        echo json_encode($this->response);
        exit();
    }
    
}


new UploadApp();

