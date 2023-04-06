<?php

class PendingSegment extends SegmentsAbstract
{

    public static function processing($reminder)
    {
        $reminders = RemindersORM::where('segmentId', 3)->where('is_on', 1)->get();

        foreach ($reminders as $reminder) {

            switch ($reminder->timeType) {
                case 'days':
                    self::daysWithoutSignReminder($reminder);
                    break;
            }
        }
    }

    private static function daysWithoutSignReminder($reminder)
    {
        $acceptDateFrom = date('Y-m-d 00:00:00', strtotime('-' . $reminder->countTime . ' days'));
        $acceptDateTo = date('Y-m-d 23:59:59', strtotime('-' . $reminder->countTime . ' days'));

        $contracts = ContractsORM::where('status', 0)->whereBetween('create_date', [$acceptDateFrom, $acceptDateTo])->get();

        foreach ($contracts as $contract) {
            $user = UsersORM::where('id', $contract->user_id)->first();

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