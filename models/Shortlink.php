<?php

class ShortLink extends Core
{

	public function get_link($url)
	{
		
		$where = $this->db->placehold(' WHERE url=? ', $url);
		
		
		$query = "SELECT id, url, link
		          FROM __short_link $where LIMIT 1";

		$this->db->query($query);
		return $this->db->result();
	}

	public function get_links()
	{
        $pages = array();

	    $query = "SELECT id, url, link
		          FROM __short_link";
	
		$this->db->query($query);
		
		foreach($this->db->results() as $page)
			$pages[$page->id] = $page;
			
		return $pages;
	}

	public function add_link($page)
	{	
		$query = $this->db->placehold('INSERT INTO __short_link SET ?%', $page);
		if(!$this->db->query($query))
			return false;

		/*$id = $this->db->insert_id();
		$this->db->query("UPDATE __pages SET position=id WHERE id=?", $id);	
		return $id;*/
	}

	public function del_link($id)
	{	
		$query = $this->db->placehold("
            DELETE FROM __short_link WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
	}

	public function update_link($id, $data)
	{	
		$query = $this->db->placehold("
            UPDATE __short_link SET ?% WHERE id = ?
        ", (array)$data, (int)$id);
        $this->db->query($query);
        
        return $id;
	}
	
}
