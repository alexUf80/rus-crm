<?php

class LoginController extends Controller
{

    function fetch()
    {
        // Выход
        if ($this->request->get('action') == 'logout') {
            unset($_SESSION['manager_id']);
            unset($_SESSION['offline_point_id']);
            setcookie('mid', null, time() - 1, '/', $this->config->root_url);
            setcookie('ah', null, time() - 1, '/', $this->config->root_url);
            header('Location: ' . $this->config->root_url);
            exit();
        } // Вход
        elseif ($this->request->method('post') && $this->request->post('login')) {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($_POST);echo '</pre><hr />';
            $login = $this->request->post('login');
            $password = $this->request->post('password');

            $this->design->assign('login', $login);

            if ($manager_id = $this->managers->check_password($login, $password)) {
                $update = array();

                $update['last_ip'] = $_SERVER['REMOTE_ADDR'];

                $this->managers->update_manager($manager_id, $update);

                $manager = $this->managers->get_manager($manager_id);
                $_SESSION['manager_id'] = $manager->id;
                $_SESSION['manager_ip'] = $_SERVER['REMOTE_ADDR'];

                $back = $this->request->get('back');
                header('Location: ' . $this->config->root_url . $back);
                exit;
            } else {
                $this->design->assign('error', 'login_incorrect');
            }
        }


        return $this->design->fetch('login.tpl');
    }
}
