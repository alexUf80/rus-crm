<?php

class LoanDoctor extends Core
{
    public function get_ld_all()
    {
    	$query = $this->db->placehold("
            SELECT u.* FROM 
            __orders o
            INNER JOIN __users u 
            ON o.user_id=u.id
            AND (
                o.date = 
                (
                    SELECT MAX(date) 
                    FROM __orders o1 
                    WHERE o1.user_id=u.id 
                    AND (
                        (o1.client_status != 'kd') 
                        OR (o1.client_status = 'kd' AND o1.status=5)
                    )
                ) 
                AND (o.status=8 OR o.status=3)
            )
        
        ");

        $this->db->query($query);
        
        return $this->db->results();
    }

    public function get_ld($filter = array())
    {

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

                case 'birth_desc':
                    $sort = 'birth DESC';
                    break;

                case 'birth_asc':
                    $sort = 'birth ASC';
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

    	$query = $this->db->placehold("
            SELECT u.*, o.status, o.id AS order_id FROM 
            __orders o
            INNER JOIN __users u 
            ON o.user_id=u.id
            AND (
                o.date = 
                (
                    SELECT MAX(date) 
                    FROM __orders o1 
                    WHERE o1.user_id=u.id 
                    AND (
                        (o1.client_status != 'kd') 
                        OR (o1.client_status = 'kd' AND o1.status=5)
                    )
                ) 
                AND (o.status=8 OR o.status=3)
            )
            $sql_limit
            ORDER BY $sort
        ");

        $this->db->query($query);
        
        return $this->db->results();
    }
    
}