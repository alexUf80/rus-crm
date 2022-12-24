<?php

class PromocodesController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post')) {
            if ($this->request->post('action', 'string')) {
                $methodName = 'action_' . $this->request->post('action', 'string');
                if (method_exists($this, $methodName)) {
                    $this->$methodName();
                }
            }
        }

        $sort = $this->request->get('sort');

        if (empty($sort))
            $sort = 'id asc';

        $this->design->assign('sort', $sort);

        $promocodes = $this->promocodes->gets(['sort' => $sort]);
        $this->design->assign('promocodes', $promocodes);

        return $this->design->fetch('promocodes.tpl');
    }

    private function action_delete()
    {
        $code_id = $this->request->post('code_id');

        $this->promocodes->delete($code_id);
        exit;
    }

    private function action_add()
    {
        $code = $this->request->post('code');
        $term = $this->request->post('term');
        $is_active = $this->request->post('is_active');
        $discount = $this->request->post('discount');
        $comment = $this->request->post('comment');

        $promocode =
            [
                'code' => $code,
                'term' => $term,
                'is_active' => $is_active,
                'discount' => $discount,
                'comment' => $comment
            ];

        $this->promocodes->add($promocode);
        exit;
    }
}