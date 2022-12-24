<?php

class Users extends Core
{

    public function get_looker_link($user_id)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $date = date('Ymd');
        $salt = $this->settings->looker_salt;

        $sha1 = sha1(md5($ip . $date . $user_id . $salt) . $salt);

        $link = $this->config->front_url . '/looker?id=' . $user_id . '&hash=' . $sha1;

        return $link;
    }


    /**
     * Users::save_loan_history()
     * Сохраняем кредитную историю полученную из 1с
     *
     * @param integer $user_id
     * @param array $credits_history
     * @return void
     */
    public function save_loan_history($user_id, $credits_history)
    {
        $loan_history = array();
        if (!empty($credits_history)) {
            foreach ($credits_history as $credits_history_item) {
                $loan_history_item = new StdClass();

                $loan_history_item->date = $credits_history_item->ДатаЗайма;
                $loan_history_item->close_date = $credits_history_item->ДатаЗакрытия;
                $loan_history_item->number = $credits_history_item->НомерЗайма;
                $loan_history_item->amount = $credits_history_item->СуммаЗайма;
                $loan_history_item->loan_body_summ = $credits_history_item->ОстатокОД;
                $loan_history_item->loan_percents_summ = $credits_history_item->ОстатокПроцентов;
                $loan_history_item->sold = $credits_history_item->Продан;
                $loan_history_item->total_paid = $credits_history_item->ОплатаПроцентов;

                $loan_history[] = $loan_history_item;

                if (!empty($loan_history_item->close_date)) {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($loan_history_item);echo '</pre><hr />';
                    /*
                                        if ($current_contract = $this->contracts->get_number_contract($loan_history_item->number))
                                        {
                                            if ($current_contract->type == 'onec' && empty($current_contract->sud))
                                            {
                                                $this->contracts->update_contract($current_contract->id, array(
                                                    'status' => 3,
                                                    'close_date' => date('Y-m-d H:i:s', strtotime($loan_history_item->close_date))
                                                ));
                                                $this->orders->update_order($current_contract->order_id, array(
                                                    'status' => 7
                                                ));
                                            }
                                        }
                    */
                }
            }
        }
        $this->users->update_user($user_id, array('loan_history' => json_encode($loan_history)));

    }

    public function get_user($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __users
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        if ($result = $this->db->result()) {
            $result->loan_history = empty($result->loan_history) ? array() : json_decode($result->loan_history);
        }

        return $result;
    }

    public function get_users($filter = array())
    {
        $id_filter = '';
        $keyword_filter = '';
        $search_filter = '';
        $limit = 1000;
        $page = 1;
        $sort = 'id DESC';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('
                    AND (
                        firstname LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                        OR lastname LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                        OR patronymic LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                        OR phone_mobile LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                        OR email LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                    )
                ');
        }

        if (!empty($filter['search'])) {
            if (!empty($filter['search']['user_id']))
                $search_filter .= $this->db->placehold(' AND id = ?', (int)$filter['search']['user_id']);
            if (!empty($filter['search']['created']))
                $search_filter .= $this->db->placehold(' AND DATE(created) = ?', date('Y-m-d', strtotime($filter['search']['created'])));
            if (!empty($filter['search']['fio'])) {
                $fio_filter = array();
                $expls = array_map('trim', explode(' ', $filter['search']['fio']));
                $search_filter .= $this->db->placehold(' AND (');
                foreach ($expls as $expl) {
                    $expl = $this->db->escape($expl);
                    $fio_filter[] = $this->db->placehold("(lastname LIKE '%" . $expl . "%' OR firstname LIKE '%" . $expl . "%' OR patronymic LIKE '%" . $expl . "%')");
                }
                $search_filter .= implode(' AND ', $fio_filter);
                $search_filter .= $this->db->placehold(')');
            }
            if (!empty($filter['search']['phone']))
                $search_filter .= $this->db->placehold(" AND phone_mobile LIKE '%" . $this->db->escape(str_replace(array(' ', '-', '(', ')', '+'), '', $filter['search']['phone'])) . "%'");
            if (!empty($filter['search']['email']))
                $search_filter .= $this->db->placehold(" AND email LIKE '%" . $this->db->escape($filter['search']['email']) . "%'");
        }

        if (!empty($filter['sort'])) {
            switch ($filter['sort']):

                case 'id_desc':
                    $sort = 'id DESC';
                    break;

                case 'id_asc':
                    $sort = 'id ASC';
                    break;

                case 'date_desc':
                    $sort = 'created DESC';
                    break;

                case 'date_asc':
                    $sort = 'created ASC';
                    break;

                case 'fio_desc':
                    $sort = 'lastname DESC, firstname DESC, patronymic DESC';
                    break;

                case 'fio_asc':
                    $sort = 'lastname ASC, firstname ASC, patronymic ASC';
                    break;

                case 'email_desc':
                    $sort = 'email DESC';
                    break;

                case 'email_asc':
                    $sort = 'email ASC';
                    break;

                case 'phone_desc':
                    $sort = 'phone_mobile DESC';
                    break;

                case 'phone_asc':
                    $sort = 'phone_mobile ASC';
                    break;

            endswitch;
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __users
            WHERE 1
                $id_filter
                $search_filter
                $keyword_filter
            ORDER BY $sort
            $sql_limit
        ");
        $this->db->query($query);

        if ($results = $this->db->results()) {
            foreach ($results as $result)
                $result->loan_history = empty($result->loan_history) ? array() : json_decode($result->loan_history);
        }

        return $results;
    }

    public function count_users($filter = array())
    {
        $id_filter = '';
        $keyword_filter = '';
        $search_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('
                    AND (
                        firstname LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                        OR lastname LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                        OR patronymic LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                        OR phone_mobile LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                        OR email LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                    )
                ');
        }

        if (!empty($filter['search'])) {
            if (!empty($filter['search']['user_id']))
                $search_filter .= $this->db->placehold(' AND id = ?', (int)$filter['search']['user_id']);
            if (!empty($filter['search']['created']))
                $search_filter .= $this->db->placehold(' AND DATE(created) = ?', date('Y-m-d', strtotime($filter['search']['created'])));
            if (!empty($filter['search']['fio'])) {
                $fio_filter = array();
                $expls = array_map('trim', explode(' ', $filter['search']['fio']));
                $search_filter .= $this->db->placehold(' AND (');
                foreach ($expls as $expl) {
                    $expl = $this->db->escape($expl);
                    $fio_filter[] = $this->db->placehold("(lastname LIKE '%" . $expl . "%' OR firstname LIKE '%" . $expl . "%' OR patronymic LIKE '%" . $expl . "%')");
                }
                $search_filter .= implode(' AND ', $fio_filter);
                $search_filter .= $this->db->placehold(')');
            }
            if (!empty($filter['search']['phone']))
                $search_filter .= $this->db->placehold(" AND phone_mobile LIKE '%" . $this->db->escape(str_replace(array(' ', '-', '(', ')', '+'), '', $filter['search']['phone'])) . "%'");
            if (!empty($filter['search']['email']))
                $search_filter .= $this->db->placehold(" AND email LIKE '%" . $this->db->escape($filter['search']['email']) . "%'");
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __users
            WHERE 1
                $id_filter
                $search_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    public function add_user($user)
    {
        $query = $this->db->placehold("
            INSERT INTO __users SET ?%
        ", (array)$user);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_user($id, $user)
    {
        $query = $this->db->placehold("
            UPDATE __users SET ?% WHERE id = ?
        ", (array)$user, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_user($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __users WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    public function get_file($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __files
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_files($filter = array())
    {
        $id_filter = '';
        $user_id_filter = '';
        $status_filter = '';
        $sent_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);

        if (isset($filter['status']))
            $status_filter = $this->db->placehold("AND status = ?", (int)$filter['status']);

        if (isset($filter['sent']))
            $sent_filter = $this->db->placehold("AND sent_1c = ?", (int)$filter['sent']);

        $query = $this->db->placehold("
            SELECT * 
            FROM __files
            WHERE 1
                $id_filter
                $user_id_filter
                $status_filter
                $sent_filter
            ORDER BY id ASC 
        ");
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function add_file($file)
    {
        $query = $this->db->placehold("
            INSERT INTO __files SET ?%, created = NOW()
        ", (array)$file);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_file($id, $file)
    {
        $query = $this->db->placehold("
            UPDATE __files SET ?% WHERE id = ?
        ", (array)$file, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_file($id)
    {
        if ($file = $this->get_file($id)) {
            if (file_exists($this->config->root_dir . $this->config->users_files_dir . $file->name))
                unlink($this->config->root_dir . $this->config->users_files_dir . $file->name);

            if (file_exists($this->config->root_dir . $this->config->original_images_dir . $file->name))
                unlink($this->config->root_dir . $this->config->original_images_dir . $file->name);

            // Удалить все ресайзы
            $filename = pathinfo($file->name, PATHINFO_FILENAME);
            $ext = pathinfo($file->name, PATHINFO_EXTENSION);

            $rezised_images = glob($this->config->root_dir . $this->config->resized_images_dir . $filename . ".*x*." . $ext);
            if (is_array($rezised_images)) {
                foreach (glob($this->config->root_dir . $this->config->resized_images_dir . $filename . ".*x*." . $ext) as $f)
                    @unlink($f);
            }

            $query = $this->db->placehold("
                DELETE FROM __files WHERE id = ?
            ", (int)$id);
            $this->db->query($query);

        }
    }

    public function check_filename($filename)
    {
        $this->db->query("SELECT id FROM __files WHERE name = ?");
        return $this->db->result('id');
    }

    public function search_user($fio)
    {
        $lastname = '';
        $firstname = '';
        $patronymic = '';

        if(isset($fio[0]))
        {
            $lastname = $fio[0];
            $lastname = $this->db->placehold("WHERE lastname LIKE '$lastname%'");
        }

        if(isset($fio[1]))
        {
            $firstname = $fio[1];
            $firstname = $this->db->placehold("AND firstname LIKE '$firstname%'");
        }

        if(isset($fio[2]))
        {
            $patronymic = $fio[2];
            $patronymic = $this->db->placehold("AND patronymic LIKE '$patronymic%'");
        }



        $query = $this->db->placehold("
            SELECT id, firstname, lastname, patronymic
            FROM s_users
            $lastname
            $firstname  
            $patronymic 
        ");
        $this->db->query($query);
        $user = $this->db->results();

        return $user;
    }

}