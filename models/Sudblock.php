<?php

class Sudblock extends Core
{
	public function get_contract($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __sudblock_contracts
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_contracts($filter = array())
	{
		$id_filter = '';
        $manager_id_filter = '';
        $keyword_filter = '';
        $search_filter = '';
        $limit = 1000;
		$page = 1;
        $sort = '';
        $sort_workout = '';
        
        if (!empty($filter['sort_workout']))
            $sort_workout = "workout ASC, ";
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id IN (?@)", array_map('intval', (array)$filter['manager_id']));
		
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        if (!empty($filter['sort']))
        {
            switch ($filter['sort']):
                
                case 'manager_id_desc':
                    $sort = 'manager_id DESC';
                break;
                
                case 'manager_id_asc':
                    $sort = 'manager_id ASC';
                break;
                
                case 'status_asc':
                    $sort = 'status ASC';
                break;
                
                case 'status_desc':
                    $sort = 'status DESC';                
                break;
                
                case 'number_asc':
                    $sort = 'number ASC';
                break;
                
                case 'number_desc':
                    $sort = 'number DESC';                
                break;
                
                case 'first_number_asc':
                    $sort = 'first_number ASC';
                break;
                
                case 'first_number_desc':
                    $sort = 'first_number DESC';                
                break;
                
                case 'fio_asc':
                    $sort = 'lastname ASC';
                break;
                
                case 'fio_desc':
                    $sort = 'lastname DESC';                
                break;
                
                case 'provider_asc':
                    $sort = 'provider ASC';
                break;
                
                case 'provider_desc':
                    $sort = 'provider DESC';                
                break;
                
                case 'created_asc':
                    $sort = 'created ASC';
                break;
                
                case 'created_desc':
                    $sort = 'created DESC';                
                break;
                
                case 'last_date_asc':
                    $sort = ' ASC';
                break;
                
                case 'last_date_desc':
                    $sort = ' DESC';                
                break;
                
                case 'body_asc':
                    $sort = 'loan_summ ASC';
                break;
                
                case 'body_desc':
                    $sort = 'loan_summ DESC';                
                break;
                
                case 'total_asc':
                    $sort = 'total_summ ASC';
                break;
                
                case 'total_desc':
                    $sort = 'total_summ DESC';                
                break;

                case 'region_asc':
                    $sort = 'region_summ ASC';
                break;
                
                case 'region_desc':
                    $sort = 'region_summ DESC';                
                break;
                
            endswitch;
        }
        
        if (!empty($filter['search']))
        {
            if (!empty($filter['search']['created']))
            {
                
            }
            
            if (!empty($filter['search']['fio']))
            {
                $fio_filter = array();
                $expls = array_map('trim', explode(' ', $filter['search']['fio']));
                $search_filter .= $this->db->placehold(' AND (');
                foreach ($expls as $expl)
                {
                    $expl = $this->db->escape($expl);
                    $fio_filter[] = $this->db->placehold("(lastname LIKE '%".$expl."%' OR firstname LIKE '%".$expl."%' OR patronymic LIKE '%".$expl."%')");
                }
                $search_filter .= implode(' AND ', $fio_filter);
                $search_filter .= $this->db->placehold(')');
            }

            if (!empty($filter['search']['first_number']))
                $search_filter .= $this->db->placehold(" AND (first_number LIKE '%".$this->db->escape($filter['search']['first_number'])."%')");
            if (!empty($filter['search']['provider']))
                $search_filter .= $this->db->placehold(" AND (provider LIKE '%".$this->db->escape($filter['search']['provider'])."%')");
            if (!empty($filter['search']['region']))
                $search_filter .= $this->db->placehold(" AND (region LIKE '%".$this->db->escape($filter['search']['region'])."%')");
            if (!empty($filter['search']['body_summ']))
                $search_filter .= $this->db->placehold(" AND (body_summ LIKE '%".$this->db->escape($filter['search']['body_summ'])."%')");
            if (!empty($filter['search']['total_summ']))
                $search_filter .= $this->db->placehold(" AND (total_summ LIKE '%".$this->db->escape($filter['search']['total_summ'])."%')");
            
            if (!empty($filter['search']['tag_id']))
            {
                $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';                    
                $search_filter .= $this->db->placehold(" AND u.contact_status = ?", $filter['search']['tag_id']);
            }
            
            if (!empty($filter['search']['manager_id']))
            {
                if ($filter['search']['manager_id'] == 'none')
                    $search_filter .= $this->db->placehold(" AND (c.collection_manager_id = 0 OR c.collection_manager_id IS NULL)");                
                else
                    $search_filter .= $this->db->placehold(" AND c.collection_manager_id = ?", (int)$filter['search']['manager_id']);
            }
            
            if (!empty($filter['search']['delay_from']))
            {
                $delay_from_date = date('Y-m-d', time() - $filter['search']['delay_from']*86400);
                $search_filter .= $this->db->placehold(" AND DATE(c.return_date) <= ?", $delay_from_date);
            }
            if (!empty($filter['search']['delay_to']))
            {
                $delay_to_date = date('Y-m-d', time() - $filter['search']['delay_to']*86400);
                $search_filter .= $this->db->placehold(" AND DATE(c.return_date) >= ?", $delay_to_date);
            }
        }
        

        $query = $this->db->placehold("
            SELECT * 
            FROM __sudblock_contracts
            WHERE 1
                $id_filter
				$manager_id_filter
                $keyword_filter
                $search_filter
            ORDER BY $sort_workout $sort 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_contracts($filter = array())
	{
        $id_filter = '';
        $manager_id_filter = '';
        $keyword_filter = '';
        $search_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id IN (?@)", array_map('intval', (array)$filter['manager_id']));
		
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __sudblock_contracts
            WHERE 1
                $id_filter
                $manager_id_filter
                $keyword_filter
                $search_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_contract($sudblock_contract)
    {
		$query = $this->db->placehold("
            INSERT INTO __sudblock_contracts SET ?%
        ", (array)$sudblock_contract);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_contract($id, $sudblock_contract)
    {
		$query = $this->db->placehold("
            UPDATE __sudblock_contracts SET ?% WHERE id = ?
        ", (array)$sudblock_contract, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_contract($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __sudblock_contracts WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }



	public function get_status($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __sudblock_statuses
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_statuses($filter = array())
	{
		$id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __sudblock_statuses
            WHERE 1
                $id_filter
				$keyword_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
        
    public function add_status($status)
    {
		$query = $this->db->placehold("
            INSERT INTO __sudblock_statuses SET ?%
        ", (array)$status);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_status($id, $status)
    {
		$query = $this->db->placehold("
            UPDATE __sudblock_statuses SET ?% WHERE id = ?
        ", (array)$status, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_status($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __sudblock_statuses WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }



	public function get_document($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __sudblock_documents
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_documents($filter = array())
	{
		$id_filter = '';
		$base_filter = '';
		$block_filter = '';
        $sudblock_contract_id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
		if (isset($filter['base']))
            $base_filter = $this->db->placehold("AND base = ?", (int)$filter['base']);
        
		if (!empty($filter['block']))
            $block_filter = $this->db->placehold("AND block = ?", (string)$filter['block']);
        
        if (!empty($filter['sudblock_contract_id']))
            $sudblock_contract_id_filter = $this->db->placehold("AND sudblock_contract_id = ?", (int)$filter['sudblock_contract_id']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __sudblock_documents
            WHERE 1
                $id_filter
				$base_filter
                $block_filter
                $sudblock_contract_id_filter 
                $keyword_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
        
    public function add_document($document)
    {
		$query = $this->db->placehold("
            INSERT INTO __sudblock_documents SET ?%
        ", (array)$document);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_document($id, $document)
    {
		$query = $this->db->placehold("
            UPDATE __sudblock_documents SET ?% WHERE id = ?
        ", (array)$document, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_document($id)
    {
		$doc = $this->get_document($id);
        
        if (!empty($doc->filename))
        {
            if (empty($doc->sudblock_contract_id))
                unlink($this->config->root_dir.'files/sudblock/'.$doc->filename);
            else
                unlink($this->config->root_dir.'files/sudblock/'.$doc->sudblock_contract_id.'/'.$doc->filename);
        }
        
        $query = $this->db->placehold("
            DELETE FROM __sudblock_documents WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}