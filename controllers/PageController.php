<?php

class PageController extends Controller
{
    public function fetch()
    {
    	if ($this->request->method('post'))
        {
            $page = new StdClass();
            
            $page->id = $this->request->post('id', 'integer');
            $page->name = $this->request->post('name');
            $page->url = $this->request->post('url');
            $page->meta_title = $this->request->post('meta_title');
            $page->meta_description = $this->request->post('meta_description');
            $page->meta_keywords = $this->request->post('meta_keywords');
            $page->body = $this->request->post('body');
            
            $exist_page = $this->pages->get_page((string)$page->url);
            
            if (!empty($exist_url) && $exist_page->id != $page->id)
            {
                $this->design->assign('message_error', 'Страница с таким адресом уже существует');
            }
            elseif (empty($page->name))
            {
                $this->design->assign('message_error', 'Укажите заголовок страницы');                
            }
            else
            {
                if (empty($page->id))
                {
                    $page->id = $this->pages->add_page($page);
                    $this->design->assign('message_success', 'Страница добавлена');
                }
                else
                {
                    $this->pages->update_page($page->id, $page);
                    $this->design->assign('message_success', 'Страница обновлена');                    
                }
            }
        }
        else
        {
            if ($id = $this->request->get('id', 'integer'))
            {
                if (!($page = $this->pages->get_page($id)))
                    return false;
                
                if ($action = $this->request->get('action', 'string'))
                {
                    switch ($action):
                        
                        case 'delete':
                            
                            $this->pages->delete_page($id);
                            
                            header('Location: /pages');
                            exit;
                            
                        break;
                        
                    endswitch;
                }
                
            }
        }

        if (!empty($page))
            $this->design->assign('page', $page);
        
        return $this->design->fetch('page.tpl');
    }
    
}