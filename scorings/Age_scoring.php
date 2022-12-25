<?php

class Age_scoring extends Core
{
    private $order_id;


    public function run_scoring($scoring_id)
    {
        $update = array();

        $scoring_type = $this->scorings->get_type('age');

        if ($scoring = $this->scorings->get_scoring($scoring_id)) {
            if ($order = $this->orders->get_order((int)$scoring->order_id)) {
                if (empty($order->birth)) {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'в заявке не указана дата рождения'
                    );
                } else {
                    $user_date = new DateTime(date('Y-m-d', strtotime($order->birth)));
                    $now_date = new DateTime(date('Y-m-d'));

                    $user_age = date_diff($user_date, $now_date);

                    $user_age_year = $user_age->y;

                    $update = array(
                        'status' => 'completed',
                        'body' => '',
                    );

                    if ($user_age_year >= $this->settings->min_age  && $user_age_year <= $this->settings->max_age ) {
                        $update['success'] = 1;
                        $update['string_result'] = 'Допустимый возраст: ' . $user_age_year;
                    } else {
                        $update['success'] = 0;
                        $update['string_result'] = 'Недопустимый возраст: ' . $user_age_year;
                    }
                }

            } else {
                $update = array(
                    'status' => 'error',
                    'string_result' => 'не найдена заявка'
                );
            }

            if (!empty($update)) {
                $this->scorings->update_scoring($scoring_id, $update);
            }

            return $update;
        }
    }
}