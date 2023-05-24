<?php

class Works extends Core
{
	public function get_work($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __works
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }

    public function get_work_by_user_id($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __works
            WHERE user_id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
    public function add_work($work)
    {
		$query = $this->db->placehold("
            INSERT INTO __works SET ?%
        ", (array)$work);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_work($id, $work)
    {
		$query = $this->db->placehold("
            UPDATE __works SET ?% WHERE id = ?
        ", (array)$work, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_work($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __works WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}