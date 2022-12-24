<?php

class Visits extends Core
{
    public function add_visit($visit)
    {
        $query = $this->db->placehold("
            INSERT INTO __visits SET ?%
        ", (array)$visit);

        $this->db->query($query);

        $id = $this->db->insert_id();

        return $id;
    }

    public function search_visits($filter = array())
    {
        $date_filter = '';
        $utm_source_filter = '';
        $utm_medium_filter = '';
        $utm_campaign_filter = '';
        $utm_term_filter = '';
        $utm_content_filter = '';
        $date_group_by = '';

        if (!empty($filter['date_from']))
        {
            $date_filter = $this->db->placehold("AND created between ? and ?",
                date('Y-m-d 00:00:00', strtotime($filter['date_from'])),
                date('Y-m-d 23:59:59', strtotime($filter['date_to'])));
        }

        if (!empty($filter['utm_source_filter']))
            $utm_source_filter = $this->db->placehold("AND utm_source = ?", $filter['utm_source_filter']);

        if (!empty($filter['utm_medium_filter']))
            $utm_medium_filter = $this->db->placehold("AND utm_medium = ?", $filter['utm_medium_filter']);

        if (!empty($filter['utm_campaign_filter']))
            $utm_campaign_filter = $this->db->placehold("AND utm_campaign = ?", $filter['utm_campaign_filter']);

        if (!empty($filter['utm_term_filter']))
            $utm_term_filter = $this->db->placehold("AND utm_term = ?", $filter['utm_term_filter']);

        if (!empty($filter['utm_content_filter']))
            $utm_content_filter = $this->db->placehold("AND utm_content = ?", $filter['utm_content_filter']);

        switch ($filter['group_by']):
            case 'day':
                $date_group_by = $this->db->placehold("DATE_FORMAT (created,'%d.%m.%Y')");
                break;

            case 'week':
                $date_group_by = $this->db->placehold("WEEK(created)");
                break;

            case 'month':
                $date_group_by = $this->db->placehold("MONTH(created)");
                break;

            case 'year':
                $date_group_by = $this->db->placehold("YEAR(created)");
                break;

        endswitch;

        $query = $this->db->placehold("
        select $date_group_by AS group_date, YEAR(`created`) AS `year`, utm_source, COUNT(utm_source) as count_visit
        from __visits
        where 1
        $date_filter
        $utm_source_filter
        $utm_medium_filter
        $utm_campaign_filter
        $utm_term_filter
        $utm_content_filter
        GROUP BY group_date, utm_source
        ");

        $this->db->query($query);

        $results = $this->db->results();

        return $results;


    }
}