<?php

class Controller extends Core
{
	public $manager;
	public $page;
	
	private static $models;
	
	public function __construct()
	{
		parent::__construct();
		
		if(self::$models)
		{
			$this->manager      = &self::$models->manager;
			$this->page         = &self::$models->page;	
		}
		else
		{
			self::$models = $this;

if ($this->is_developer)
{
//    $_SESSION['manager_id'] = 2;
}

			if(isset($_SESSION['manager_id']))
			{
				if ($_SESSION['manager_ip'] == $_SERVER['REMOTE_ADDR'])
                {
                    $manager = $this->managers->get_manager(intval($_SESSION['manager_id']));
    
                    $manager->permissions = $this->managers->get_permissions($manager->role);
                    $this->manager = $manager;
    //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($manager->permissions);echo '</pre><hr />';
                    $this->managers->update_manager($manager->id, array('last_ip' => $_SERVER['REMOTE_ADDR'], 'last_visit' => date('Y-m-d H:i:s')));
                }
                else
                {
                    unset($_SESSION['manager_id']);
                }
			}
            elseif (isset($_COOKIE['ah'], $_COOKIE['mid']))
            {
                $manager = $this->managers->get_manager((int)$_COOKIE['mid']);
                
                if ($manager && $_COOKIE['ah'] == md5(sha1($_SERVER['REMOTE_ADDR'].$manager->id).$manager->salt))
                {
                    $manager->permissions = $this->managers->get_permissions($manager->role);
                    $this->manager = $manager;
                    $_SESSION['manager_id'] = $manager->id;
                }
                else
                {
                    setcookie('ah', null, time() - 1, '/', $this->config->root_url);
                    setcookie('mid', null, time() - 1, '/', $this->config->root_url);
                }
            }

			if (!empty($this->manager->blocked))
            {
                header('Location:'.$this->config->root_url.'/login?blocked=1');
                setcookie('ah', null, time() - 1, '/');
                setcookie('mid', null, time() - 1, '/');
                $_SESSION['manager_id'] = null;
                exit;
            }
            
            // Текущая страница (если есть)
			$subdir = substr(dirname(dirname(__FILE__)), strlen($_SERVER['DOCUMENT_ROOT']));
			$page_url = trim(substr($_SERVER['REQUEST_URI'], strlen($subdir)),"/");
			if(strpos($page_url, '?') !== false)
				$page_url = substr($page_url, 0, strpos($page_url, '?'));
			$this->page = $this->pages->get_page((string)$page_url);
			$this->design->assign('page', $this->page);		
			
			// Передаем в дизайн то, что может понадобиться в нем
			if (!empty($_SESSION['offline_point_id']))
                $this->manager->offline_point_id = $_SESSION['offline_point_id'];
            
            $offline_points = array();
            foreach ($this->offline->get_points() as $p)
                $offline_points[$p->id] = $p;
            $this->design->assign('offline_points', $offline_points);

            
            $this->design->assign('manager',	$this->manager);
			
			$this->design->assign('config',		$this->config);
			$this->design->assign('settings',	$this->settings);
            
            $managers = array();
            foreach ($this->managers->get_managers() as $m)
                $managers[$m->id] = $m;
            $this->design->assign('managers', $managers);
		      
            $this->design->assign('is_developer', $this->is_developer);
        }
	}
		
	public function fetch()
	{
		return false;
	}
	
    protected function json_output($data)
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");		

        echo json_encode($data);
        exit;
    }
}
