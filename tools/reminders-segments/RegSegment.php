<?php

class RegSegment extends SegmentsAbstract
{

    public static function processing($request)
    {
        $reminders = RemindersORM::where('segmentId', 1)->where('is_on', 1)->get();

        foreach ($reminders as $reminder) {
            $time = date('Y-m-d H:i:00', strtotime('-' . $reminder->countTime . ' ' . $reminder->timeType));


            $startTime = date('Y-m-d H:i:s', strtotime($time . '-5 minutes'));
            $endTime = date('Y-m-d H:i:s', strtotime($time . '+5 minutes'));

            $users = UsersORM::whereBetween('created', [$startTime, $endTime])->where('stage_card', 0)->get();

            if (!empty($users)) {
                foreach ($users as $user) {

                    $isSent = RemindersCronORM::where('userId', $user->id)->where('reminderId', $reminder->id)->first();

                    if (!empty($isSent))
                        continue;

                    $reminderLog =
                        [
                            'reminderId' => $reminder->id,
                            'userId' => $user->id,
                            'message' => $reminder->msgSms,
                            'phone' => $user->phone_mobile
                        ];

                    RemindersCronORM::insert($reminderLog);

                    $send =
                        [
                            'phone' => $user->phone_mobile,
                            'msg' => $reminder->msgSms
                        ];

                    self::send($send);
                }
            }
        }
    }
}