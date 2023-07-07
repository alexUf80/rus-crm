<?php

class Partners extends Core
{
	public function get_partner($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __partners
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	
}