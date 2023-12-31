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

                case 'reminders':
                    return $this->action_reminders();
                    break;

                // case 'distributior_collectors':
                //     return $this->action_distributior_collectors();
                //     break;

                // case 'distributior_collectors_doc':
                //     return $this->action_distributior_collectors_doc();
                //     break;

            endswitch;
        }

        if (in_array('collection_statistics', $this->manager->permissions)) {
            switch ($this->request->get('action', 'string')):
                case 'distributior_collectors':
                    return $this->action_distributior_collectors();
                    break;

                case 'distributior_collectors_doc':
                    return $this->action_distributior_collectors_doc();
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

    private function action_reminders()
    {
        if ($this->request->method('post')) {
            switch ($this->request->post('action', 'string')):

                case 'addReminder':
                    $this->action_addReminder();
                    break;

                case 'deleteReminder':
                    $this->action_deleteReminder();
                    break;

                case 'getReminder':
                    $this->action_getReminder();
                    break;

                case 'updateReminder':
                    $this->action_updateReminder();
                    break;

                case 'switchReminder':
                    $this->action_switchReminder();
                    break;

            endswitch;
        }

        $reminders = RemindersORM::get();
        $this->design->assign('reminders', $reminders);

        $remindersEvents = RemindersEventsORM::get();
        $this->design->assign('remindersEvents', $remindersEvents);

        $remindersSegments = RemindersSegmentsORM::get();
        $this->design->assign('remindersSegments', $remindersSegments);

        return $this->design->fetch('tools/reminders.tpl');
    }

    private function action_addReminder()
    {
        $eventId = $this->request->post('event');
        $segmentId = $this->request->post('segment');
        $typeTime = $this->request->post('typeTime');
        $count = $this->request->post('count');
        $msgSms = $this->request->post('msgSms');
        $msgZvon = $this->request->post('msgZvon');
        $timeToSend = $this->request->post('timeToSend');

        $insert =
            [
                'eventId' => $eventId,
                'segmentId' => $segmentId,
                'timeType' => $typeTime,
                'countTime' => $count,
                'msgSms' => $msgSms,
                'msgZvon' => $msgZvon,
                'timeToSend' => $timeToSend
            ];

        RemindersORM::insert($insert);
        exit;
    }

    private function action_switchReminder()
    {
        $id = $this->request->post('id');
        $value = $this->request->post('value');

        RemindersORM::where('id', $id)->update(['is_on' => $value]);
        exit;
    }

    private function action_getReminder()
    {
        $id = $this->request->post('id');
        $reminder = RemindersORM::find($id);

        echo json_encode($reminder);
        exit;
    }

    private function action_updateReminder()
    {
        $id = $this->request->post('id');
        $eventId = $this->request->post('event');
        $segmentId = $this->request->post('segment');
        $typeTime = $this->request->post('typeTime');
        $count = $this->request->post('count');
        $msgSms = $this->request->post('msgSms');
        $msgZvon = $this->request->post('msgZvon');
        $timeToSend = $this->request->post('timeToSend');

        $update =
            [
                'eventId' => $eventId,
                'segmentId' => $segmentId,
                'timeType' => $typeTime,
                'countTime' => $count,
                'msgSms' => $msgSms,
                'msgZvon' => $msgZvon,
                'timeToSend' => $timeToSend
            ];

        RemindersORM::where('id', $id)->update($update);
        exit;
    }

    private function action_deleteReminder()
    {
        $id = $this->request->post('id');
        RemindersORM::destroy($id);
        exit;
    }

    private function action_distributior_collectors()
    {

        
        if ($this->request->method('post')) {
            switch ($this->request->post('action', 'string')):
                
                case 'cancel_distributions':
                    $this->action_cancel_distributions();
                    break;

            endswitch;
        }

        $movings_groups = $this->collections->get_movings_groups();

        foreach ($movings_groups as $movings_group) {
            $movings_group->initiator_name = 'АвтоРаспределение';
            if($movings_group->initiator_id > 0 ){
                $manager = $this->managers->get_manager($movings_group->initiator_id);
                $movings_group->initiator_name = $manager->name;
            }
        }

        $this->design->assign('movings_groups', $movings_groups);
             


        return $this->design->fetch('tools/distributior_collectors.tpl');
    }

    private function action_distributior_collectors_doc()
    {


        $timestamp = $this->request->get('ts');
        $timestamp = str_replace("_", " ", $timestamp);
        $this->design->assign('timestamp', $timestamp);

        $movings_groups_items = $this->collections->get_movings_group_items($timestamp);
        foreach ($movings_groups_items as $movings_group) {
            $movings_group->initiator_name = 'АвтоРаспределение';
            if($movings_group->initiator_id > 0 ){
                $initiator = $this->managers->get_manager($movings_group->initiator_id);
                $movings_group->initiator_name = $initiator->name;
            }

            $manager = $this->managers->get_manager($movings_group->manager_id);
            $movings_group->manager_name = $manager->name;
            $movings_group->manager_collection_status_id = $manager->collection_status_id;

            $from_manager = $this->managers->get_manager($movings_group->from_manager_id);
            $movings_group->from_manager_name = 'Первое распределение';
            if ($from_manager != null) {
                $movings_group->from_manager_name = $from_manager->name;
                $movings_group->from_manager_collection_status_id = $from_manager->collection_status_id;
            }

            $movings_group->contract = $this->contracts->get_contract($movings_group->contract_id);
            $movings_group->user = $this->users->get_user($movings_group->contract->user_id);
        }

        $this->design->assign('movings_groups_items', $movings_groups_items);

        $collection_statuses = $this->contracts->get_collection_statuses();
        $this->design->assign('collection_statuses', $collection_statuses);

        return $this->design->fetch('tools/distributior_collectors_doc.tpl');
    }

    private function action_cancel_distributions()
    {

        $timestamp = $this->request->post('ts');

        $movings_groups_items = $this->collections->get_movings_group_items($timestamp);
        foreach ($movings_groups_items as $movings_groups_item) {

            $this->contracts->update_contract($movings_groups_item->contract_id, array(
                'collection_manager_id' => $movings_groups_item->from_manager_id,
            ));
            $this->collections->delete_moving($movings_groups_item->id);
        }

        exit;
    }
}