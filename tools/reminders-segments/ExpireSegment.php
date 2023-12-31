<?php

class ExpireSegment extends SegmentsAbstract
{

    public static function processing($reminder)
    {
        $reminders = RemindersORM::where('segmentId', 5)->where('is_on', 1)->get();
        
        foreach ($reminders as $reminder) {
            self::expiredDaysReminder($reminder);
        }
    }
    
    private static function expiredDaysReminder($reminder)
    {
        $thisDayFrom = date('Y-m-d 00:00:00');
        $thisDayTo = date('Y-m-d 23:59:59');

        $thisWeekFrom = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $thisWeekTo = date('Y-m-d 23:59:59', strtotime('sunday this week'));

        $thisMonthFrom = date('Y-m-01 00:00:00', strtotime('monday this week'));
        $thisMonthTo = date('Y-m-t 23:59:59', strtotime('monday this week'));

        $settings = new Settings();
        $limitCommunications = $settings->limit_communications;

        $contracts = ContractsORM::where('status', 4)->where('return_date', '>=', date('Y-m-d 00:00:00', strtotime('2023-02-19')))->get()       ;

        foreach ($contracts as $contract) {

            $now = new DateTime();
            $now_date = date('Y-m-d');
            $returnDate = new DateTime(date('Y-m-d', strtotime($contract->return_date)));

            $notifications = NotificationsORM::where('notification_date', $now_date)->where('collection_contract_id', $contract->id)->get();

            if(!count($notifications)){
                if(date_diff($now, $returnDate)->days != $reminder->countTime)
                    continue;
    
                if($now < $returnDate)
                    continue;
            }
            else{
                if($reminder->countTime != 1000)
                    continue;
            }


            $limitDays = 0;
            $limitWeek = 0;
            $limitMonth = 0;

            $canSend = 1;

            $communications = RemindersCronORM::where('userId', $contract->user_id)->get();

            if (!empty($communications)) {
                foreach ($communications as $communication) {
                    $created = date('Y-m-d H:i:s', strtotime($communication->created));

                    if ($created >= $thisDayFrom && $created <= $thisDayTo)
                        $limitDays++;

                    if ($created >= $thisWeekFrom && $created <= $thisWeekTo)
                        $limitWeek++;

                    if ($created >= $thisMonthFrom && $created <= $thisMonthTo)
                        $limitMonth++;
                }
            }

            if (
                $limitDays >= $limitCommunications['day']
                || $limitWeek >= $limitCommunications['week']
                || $limitMonth >= $limitCommunications['month']
            ) {
                $canSend = 0;
            }

            $user = UsersORM::where('id', $contract->user_id)->first();

            $reminder->msgSms = str_replace("%Имя%", $user->firstname, $reminder->msgSms);
            $reminder->msgSms = str_replace("%Отчество%", $user->patronymic, $reminder->msgSms);
            $reminder->msgSms = str_replace("%НомерДоговора%", $contract->number, $reminder->msgSms);
            $reminder->msgSms = str_replace("%ОстатокЗадолженностиПолн%", ($contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_peni_summ), $reminder->msgSms);
            $reminder->msgSms = str_replace("%ОрганизацияПоВыдачеСокр%", "ООО МКК «Русзаймсервис»", $reminder->msgSms);
            $reminder->msgSms = str_replace("%ТелефонЦОК%", "89190303610", $reminder->msgSms);
            
            $short_link = self::short_link($contract);
            $reminder->msgSms = str_replace("%СсылкаНаОплату_ОстатокЗадолженностиПолн%", $short_link, $reminder->msgSms);
            foreach ($notifications as $notification) {
                $reminder->msgSms = str_replace("%СуммаОбещания%", $notification->amount, $reminder->msgSms);
                break;
            }

            if ($canSend == 1) {
                $reminderLog =
                    [
                        'reminderId' => $reminder->id,
                        'userId' => $user->id,
                        'message' => $reminder->msgSms,
                        'phone' => $user->phone_mobile,
                        'orderId' => $contract->order_id
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