<?php

class Blacklist_scoring extends Core
{
    public function run_scoring($scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);
        $user = UsersORM::find($scoring->user_id);

        $fio = mb_strtoupper($user->lastname) . ' ' . mb_strtoupper($user->firstname) . ' ' . mb_strtoupper($user->patronymic);
        $birth = date('Y-m-d', strtotime($user->birth));

        $in_blacklist = BlacklistORM::where('fio', $fio)->where('birth', $birth)->first();

        if (!empty($in_blacklist)) {
            $update = array(
                'status' => 'completed',
                'body' => '',
                'success' => 0,
                'string_result' => 'Пользователь находится в черном списке'
            );
        } else {
            $update = array(
                'status' => 'completed',
                'body' => '',
                'success' => 1,
                'string_result' => 'Пользователь отсутствует в черном списке'
            );
        }

        $this->scorings->update_scoring($scoring_id, $update);
        return $update;
    }


}