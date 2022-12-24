<?php

ini_set('max_execution_time', 40);

class ToolsController extends Controller
{
    public function fetch()
    {
        if (in_array('analitics', $this->manager->permissions)) {
            switch ($this->request->get('action', 'string')):
                case 'integrations':
                    return $this->action_integrations();
                    break;

                case 'main':
                    return $this->action_main();
                    break;

                case 'short_link':
                    return $this->action_short_link();
                    break;

            endswitch;
        }
    }

    private function action_main()
    {
        return $this->design->fetch('tools/main.tpl');
    }

    private function action_integrations()
    {
        if ($this->request->method('post')) {
            switch ($this->request->post('action', 'string')):

                case 'add_integration':
                    $this->action_add_integration();
                    break;

                case 'delete_integration':
                    $this->action_delete_integration();
                    break;

                case 'get_integration':
                    $this->action_get_integration();
                    break;

                case 'update_integration':
                    $this->action_update_integration();
                    break;

            endswitch;
        }

        $integrations = $this->Integrations->get_integrations();
        $this->design->assign('integrations', $integrations);


        return $this->design->fetch('tools/integrations.tpl');
    }

    private function action_add_integration()
    {
        $utm_source = $this->request->post('utm_source', 'string');
        $utm_medium = $this->request->post('utm_medium', 'string');
        $utm_campaign = $this->request->post('utm_campaign', 'string');
        $utm_term = $this->request->post('utm_term', 'string');
        $utm_content = $this->request->post('utm_content', 'string');
        $utm_source_name = $this->request->post('utm_source_name', 'string');
        $utm_medium_name = $this->request->post('utm_medium_name', 'string');
        $utm_campaign_name = $this->request->post('utm_campaign_name', 'string');
        $utm_term_name = $this->request->post('utm_term_name', 'string');
        $utm_content_name = $this->request->post('utm_content_name', 'string');

        $integration =
            [
                'name' => $utm_source,
                'utm_source' => $utm_source,
                'utm_source_name' => $utm_source_name,
                'utm_medium' => ($utm_medium) ? $utm_medium : ' ',
                'utm_campaign' => ($utm_campaign) ? $utm_campaign : ' ',
                'utm_term' => ($utm_term) ? $utm_term : ' ',
                'utm_content' => ($utm_content) ? $utm_content : ' ',
                'utm_medium_name' => ($utm_medium_name) ? $utm_medium_name : ' ',
                'utm_campaign_name' => ($utm_campaign_name) ? $utm_campaign_name : ' ',
                'utm_term_name' => ($utm_term_name) ? $utm_term_name : ' ',
                'utm_content_name' => ($utm_content_name) ? $utm_content_name : ' '
            ];

        $result = $this->Integrations->add_integration($integration);

        if ($result != 0) {
            echo json_encode(['resp' => 'success']);
        } else {
            echo json_encode(['resp' => 'error']);
        }

        exit;
    }

    private function action_update_integration()
    {
        $integration_id = $this->request->post('integration_id');

        $utm_source = $this->request->post('utm_source', 'string');
        $utm_medium = $this->request->post('utm_medium', 'string');
        $utm_campaign = $this->request->post('utm_campaign', 'string');
        $utm_term = $this->request->post('utm_term', 'string');
        $utm_content = $this->request->post('utm_content', 'string');
        $utm_source_name = $this->request->post('utm_source_name', 'string');
        $utm_medium_name = $this->request->post('utm_medium_name', 'string');
        $utm_campaign_name = $this->request->post('utm_campaign_name', 'string');
        $utm_term_name = $this->request->post('utm_term_name', 'string');
        $utm_content_name = $this->request->post('utm_content_name', 'string');

        $integration =
            [
                'name' => $utm_source,
                'utm_source' => $utm_source,
                'utm_source_name' => $utm_source_name,
                'utm_medium' => ($utm_medium) ? $utm_medium : ' ',
                'utm_campaign' => ($utm_campaign) ? $utm_campaign : ' ',
                'utm_term' => ($utm_term) ? $utm_term : ' ',
                'utm_content' => ($utm_content) ? $utm_content : ' ',
                'utm_medium_name' => ($utm_medium_name) ? $utm_medium_name : ' ',
                'utm_campaign_name' => ($utm_campaign_name) ? $utm_campaign_name : ' ',
                'utm_term_name' => ($utm_term_name) ? $utm_term_name : ' ',
                'utm_content_name' => ($utm_content_name) ? $utm_content_name : ' '
            ];

        $result = $this->Integrations->update_integration($integration_id, $integration);

        if ($result != 0) {
            echo json_encode(['resp' => 'success']);
        } else {
            echo json_encode(['resp' => 'error']);
        }

        exit;
    }


    private function action_delete_integration()
    {

        $integration_id = $this->request->post('integration_id');

        $this->Integrations->delete_integration($integration_id);

        echo json_encode(['resp' => 'success']);

        exit;
    }

    private function action_get_integration()
    {
        $integration_id = $this->request->post('integration_id');

        $integration = $this->Integrations->get_integration($integration_id);

        echo json_encode($integration);

        exit;
    }

    private function action_short_link()
    {

        if ($this->request->method('post')) {


            if ($this->request->post('action', 'string') == 'change_link') {
                $this->change_link();
            } elseif ($this->request->post('action', 'string') == 'del_link') {
                $this->del_link();
            } else {
                $page = new StdClass();

                $page->url = $this->request->post('url');
                $page->link = $this->request->post('link');

                $exist_page = $this->shortlink->get_link($page->url);

                if (!empty($exist_page)) {
                    $this->design->assign('message_error', 'Данное сокращение уже используется');
                } elseif (empty($page->url)) {
                    $this->design->assign('message_error', 'Укажите сокращение');
                } elseif (empty($page->link)) {
                    $this->design->assign('message_error', 'Укажите ссылку');
                } else {

                    $page->id = $this->shortlink->add_link($page);
                    $this->design->assign('message_success', 'Ссылка сохранена');

                }
            }


        } else {

        }

        $pages = $this->shortlink->get_links();
        $this->design->assign('pages', $pages);

        return $this->design->fetch('tools/short_link.tpl');
    }

    private function change_link()
    {
        $id = $this->request->post('idlink');
        $url = $this->request->post('url', 'string');
        $link = $this->request->post('link');


        $data =
            [
                'url' => $url,
                'link' => $link
            ];

        $result = $this->shortlink->update_link($id, $data);

        if ($result != 0) {
            echo json_encode(['resp' => 'success', 'test' => $data]);
        } else {
            echo json_encode(['resp' => 'error']);
        }

        exit;
    }

    private function del_link()
    {
        $id = $this->request->post('id_link');

        $this->shortlink->del_link($id);
    }
}