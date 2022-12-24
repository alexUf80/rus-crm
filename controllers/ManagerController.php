<?php

class ManagerController extends Controller
{
    public function fetch()
    {
    	if ($this->request->method('post'))
        {
            $user = new StdClass();
            $user_id = $this->request->post('id', 'integer');
            
            $user->role = $this->request->post('role');
            $user->name = $this->request->post('name');
            $user->email = $this->request->post('email');
            $user->phone = $this->request->post('phone');
            $user->login = $this->request->post('login');
            $user->mango_number = $this->request->post('mango_number');

            $user->role = $this->ManagersRoles->get($user->role);
            $user->role = $user->role->name;
            
            if ($this->request->post('password'))
                $user->password = $this->request->post('password');
            
            $errors = array();
            
            if (empty($user->role))
                $errors[] = 'empty_role';
            if (empty($user->name))
                $errors[] = 'empty_name';
            if (empty($user->login))
                $errors[] = 'empty_login';
            
            if (empty($user_id) && empty($user->password))
                $errors[] = 'empty_password';

            $this->design->assign('errors', $errors);

            if (empty($errors))
            {
                if (empty($user_id))
                {
                    $user->id = $this->managers->add_manager($user);
                    $this->design->assign('message_success', 'added');
                }
                else
                {
                    $user->id = $this->managers->update_manager($user_id, $user);
                    $this->design->assign('message_success', 'updated');
                }
                $user = $this->managers->get_manager($user->id);
            }
        }
        else
        {
            if ($this->request->get('action') == 'blocked')
            {
                $manager_id = $this->request->get('manager_id', 'integer');
                $block = $this->request->get('block', 'integer');
            
                $this->managers->update_manager($manager_id, array('blocked' => $block));
                
                if ($contracts = $this->contracts->get_contracts(array('collection_manager_id'=>$manager_id)))
                {
                    foreach ($contracts as $c)
                        $this->contracts->update_contract($c->id, array('collection_manager_id'=> 0));
                }
                
                exit;
            }
            
            if ($id = $this->request->get('id', 'integer'))
            {
                $user = $this->managers->get_manager($id);
            }
            
        }
        
        if (!empty($user))
        {
            
            $meta_title = 'Профиль '.$user->name;
            $this->design->assign('user', $user);
        }
        else
        {
            $meta_title = 'Создать новый профиль';
        }
        
        $roles = $this->ManagersRoles->gets();
        $this->design->assign('roles', $roles);
        
        $this->design->assign('meta_title', $meta_title);
        
        return $this->design->fetch('manager.tpl');
    }
    
}