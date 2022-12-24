<?php

class Nbkiscore_scoring extends Core
{
    public function run_scoring($scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);

        $this->db->query("
        SELECT *
        FROM s_scorings
        WHERE order_id = ?
        and `type` = 'nbki'
        and `status` = 'completed'
        ", $scoring->order_id);

        $nbki = $this->db->result();

        $error = 0;

        if (empty($nbki)) {
            $error = 1;
        } else {
            $nbki = unserialize($nbki->body);

            if ($nbki == false)
                $error = 1;
        }

        if ($error == 1) {
            $update = [
                'status' => 'completed',
                'body' => 'Скоринг НБКИ пуст',
                'success' => 1,
                'string_result' => 'Скоринг НБКИ пуст'
            ];

            $this->scorings->update_scoring($scoring_id, $update);
            return $update;
        }

        if (isset($nbki['json']['AccountReply']['paymtPat'])) {
            $rezerv = $nbki['json']['AccountReply'];
            unset($nbki['json']['AccountReply']);
            $nbki['json']['AccountReply'][0] = $rezerv;
        }

        $order = OrdersORM::find($scoring->order_id);

        if (in_array($order->client_status, ['nk', 'rep']))
            return $this->newClient($nbki, $scoring);
        else
            return $this->oldClient($nbki, $scoring);
    }

    private function newClient($nbki, $scoring)
    {
        $nbki_score = 193;
        $pdl_overdue_count = 0;
        $pdlCreditLimit = 0;
        $npl90CreditLimit = 0;
        $nplCreditLimit = 0;
        $pdl_npl_limit_share = 0;
        $pdl_npl_90_limit_share = 0;
        $pdl_current_limit_max = 0;
        $pdl_last_3m_limit = 0;
        $pdl_last_good_max_limit = 0;
        $Last_npl_opened = null;
        $pdl_good_limit = 0;
        $pdl_prolong_3m_limit = 0;
        $consum_current_limit_max = 0;
        $consum_good_limit = 0;

        $now = new DateTime(date('Y-m-d'));

        foreach ($nbki['json']['AccountReply'] as $scor) {

            if (in_array($scor['acctType'], [16, 9, 7]) && $scor['creditLimit'] <= 30000) {

                $pdlCreditLimit += $scor['creditLimit'];

                if ($scor['amtPastDue'] > 0)
                    $pdl_overdue_count++;

                $scor['paymtPat'] = preg_replace('/[^0-9]/', '', $scor['paymtPat']);

                if (!empty($scor['paymtPat'])) {
                    $scor['paymtPat'] = str_split($scor['paymtPat']);

                    foreach ($scor['paymtPat'] as $value) {
                        if ($value >= 2 || $scor['amtPastDue'] > 0) {
                            $nplCreditLimit += $scor['creditLimit'];
                            if (isset($openedDt) && $openedDt < new DateTime(date('Y-m-d', strtotime($scor['openedDt']))))
                                $Last_npl_opened = $scor['openedDt'];

                            break;
                        }
                    }
                }

                if (!empty($scor['accountRating'])) {
                    if ($scor['accountRating'] != 13 && $scor['creditLimit'] > $pdl_current_limit_max)
                        $pdl_current_limit_max = $scor['creditLimit'];
                }

                $openedDt = new DateTime(date('Y-m-d', strtotime($scor['openedDt'])));

                if (date_diff($now, $openedDt)->days <= 90)
                    $pdl_last_3m_limit += $scor['creditLimit'];
            }
        }

        foreach ($nbki['json']['AccountReply'] as $scor) {

            if (in_array($scor['acctType'], [16, 9, 7]) && $scor['creditLimit'] <= 30000) {

                $scor['paymtPat'] = preg_replace('/[^0-9]/', '', $scor['paymtPat']);

                if (!empty($scor['paymtPat'])) {
                    $scor['paymtPat'] = str_split($scor['paymtPat']);

                    foreach ($scor['paymtPat'] as $value) {
                        if ($value >= 4) {
                            $npl90CreditLimit += $scor['creditLimit'];
                            break;
                        }
                    }
                }
            }
        }

        foreach ($nbki['json']['AccountReply'] as $scor) {

            if (in_array($scor['acctType'], [16, 9, 7]) && $scor['creditLimit'] <= 30000) {

                $scor['paymtPat'] = preg_replace('/[^0-9]/', '', $scor['paymtPat']);

                if (!empty($scor['paymtPat'])) {
                    $scor['paymtPat'] = str_split($scor['paymtPat']);

                    foreach ($scor['paymtPat'] as $value) {
                        if ($value >= 2 || $scor['amtPastDue'] > 0) {
                            $openedDt = new DateTime(date('Y-m-d', strtotime($scor['openedDt'])));
                            if ($openedDt > $Last_npl_opened && $scor['creditLimit'] > $pdl_last_good_max_limit) {
                                $pdl_last_good_max_limit = $scor['creditLimit'];
                                break;
                            }
                        }
                    }
                }
            }
        }

        foreach ($nbki['json']['AccountReply'] as $scor) {

            if (in_array($scor['acctType'], [16, 9, 7]) && $scor['creditLimit'] <= 30000) {

                $scor['paymtPat'] = preg_replace('/[^0-9]/', '', $scor['paymtPat']);

                if (!empty($scor['paymtPat'])) {
                    $scor['paymtPat'] = str_split($scor['paymtPat']);

                    foreach ($scor['paymtPat'] as $value) {
                        if ($value >= 2 || $scor['amtPastDue'] != 0) {
                            continue;
                        } else {
                            $pdl_good_limit += $scor['creditLimit'];
                            break;
                        }
                    }
                }
            }
        }

        foreach ($nbki['json']['AccountReply'] as $scor) {

            if (in_array($scor['acctType'], [16]) && $scor['creditLimit'] <= 30000 && $scor['fact_term_m'] >= 3) {

                $scor['paymtPat'] = preg_replace('/[^0-9]/', '', $scor['paymtPat']);

                if (!empty($scor['paymtPat'])) {
                    $scor['paymtPat'] = str_split($scor['paymtPat']);

                    foreach ($scor['paymtPat'] as $value) {
                        if ($value >= 2 || $scor['amtPastDue'] != 0) {
                            continue;
                        } else {
                            $pdl_prolong_3m_limit += $scor['creditLimit'];
                            break;
                        }
                    }
                }
            }
        }

        foreach ($nbki['json']['AccountReply'] as $scor) {

            if (in_array($scor['acctType'], [16, 9, 7]) && $scor['creditLimit'] > 30000) {

                if ($scor['amtPastDue'] != 13 && $scor['amtPastDue'] == 0 && $scor['creditLimit'] > $consum_current_limit_max) {
                    $consum_current_limit_max = $scor['creditLimit'];
                    break;
                }
            }
        }

        foreach ($nbki['json']['AccountReply'] as $scor) {

            if (in_array($scor['acctType'], [16, 9, 7]) && $scor['creditLimit'] > 30000) {

                $scor['paymtPat'] = preg_replace('/[^0-9]/', '', $scor['paymtPat']);

                if (!empty($scor['paymtPat'])) {
                    $scor['paymtPat'] = str_split($scor['paymtPat']);

                    foreach ($scor['paymtPat'] as $value) {
                        if ($value >= 2 || $scor['amtPastDue'] != 0) {
                            continue;
                        } else {
                            $consum_good_limit += $scor['creditLimit'];
                            break;
                        }
                    }
                }
            }
        }

        if ($pdl_overdue_count < 1)
            $nbki_score += 100;
        elseif ($pdl_overdue_count == 1)
            $nbki_score -= 19;
        elseif ($pdl_overdue_count == 2)
            $nbki_score -= 97;
        elseif ($pdl_overdue_count == 3)
            $nbki_score -= 203;
        elseif ($pdl_overdue_count >= 4)
            $nbki_score -= 497;

        if ($pdlCreditLimit != 0) {
            $pdl_npl_limit_share = $nplCreditLimit / $pdlCreditLimit;
            $pdl_npl_90_limit_share = $npl90CreditLimit / $pdlCreditLimit;
        }

        if ($pdl_npl_limit_share < 10)
            $nbki_score += 30;
        elseif ($pdl_npl_limit_share >= 10 && $pdl_npl_limit_share < 20)
            $nbki_score += 20;
        elseif ($pdl_npl_limit_share >= 20 && $pdl_npl_limit_share < 30)
            $nbki_score -= 9;
        elseif ($pdl_npl_limit_share >= 30 && $pdl_npl_limit_share < 50)
            $nbki_score -= 42;
        elseif ($pdl_npl_limit_share >= 50)
            $nbki_score -= 128;

        if ($pdl_npl_90_limit_share < 10)
            $nbki_score += 57;
        elseif ($pdl_npl_90_limit_share >= 10 && $pdl_npl_90_limit_share < 20)
            $nbki_score += 1;
        elseif ($pdl_npl_90_limit_share >= 20 && $pdl_npl_90_limit_share < 30)
            $nbki_score -= 66;
        elseif ($pdl_npl_90_limit_share >= 30 && $pdl_npl_90_limit_share < 50)
            $nbki_score -= 137;
        elseif ($pdl_npl_90_limit_share >= 50)
            $nbki_score -= 291;

        if ($pdl_current_limit_max < 2500)
            $nbki_score -= 170;
        elseif ($pdl_current_limit_max >= 2500 && $pdl_current_limit_max < 5000)
            $nbki_score -= 75;
        elseif ($pdl_current_limit_max >= 5000 && $pdl_current_limit_max < 10000)
            $nbki_score -= 36;
        elseif ($pdl_current_limit_max >= 10000 && $pdl_current_limit_max < 20000)
            $nbki_score += 38;
        elseif ($pdl_current_limit_max >= 20000)
            $nbki_score += 72;

        if ($pdl_last_3m_limit < 10000)
            $nbki_score -= 355;
        elseif ($pdl_last_3m_limit >= 10000 && $pdl_last_3m_limit < 20000)
            $nbki_score -= 97;
        elseif ($pdl_last_3m_limit >= 20000 && $pdl_last_3m_limit < 50000)
            $nbki_score += 25;
        elseif ($pdl_last_3m_limit >= 50000 && $pdl_last_3m_limit < 100000)
            $nbki_score += 132;
        elseif ($pdl_last_3m_limit >= 100000)
            $nbki_score += 183;

        if ($pdl_last_good_max_limit < 3000)
            $nbki_score -= 86;
        elseif ($pdl_last_good_max_limit >= 3000 && $pdl_last_good_max_limit < 6000)
            $nbki_score -= 35;
        elseif ($pdl_last_good_max_limit >= 6000 && $pdl_last_good_max_limit < 10000)
            $nbki_score -= 12;
        elseif ($pdl_last_good_max_limit >= 10000 && $pdl_last_good_max_limit < 20000)
            $nbki_score += 3;
        elseif ($pdl_last_good_max_limit >= 20000)
            $nbki_score += 15;

        if ($pdl_good_limit < 20000)
            $nbki_score -= 143;
        elseif ($pdl_good_limit >= 20000 && $pdl_good_limit < 40000)
            $nbki_score -= 45;
        elseif ($pdl_good_limit >= 40000 && $pdl_good_limit < 80000)
            $nbki_score -= 7;
        elseif ($pdl_good_limit >= 80000 && $pdl_good_limit < 150000)
            $nbki_score += 21;
        elseif ($pdl_good_limit >= 150000 && $pdl_good_limit < 300000)
            $nbki_score += 38;
        elseif ($pdl_good_limit >= 300000)
            $nbki_score += 51;

        if ($pdl_prolong_3m_limit < 5000)
            $nbki_score -= 89;
        elseif ($pdl_prolong_3m_limit >= 5000 && $pdl_prolong_3m_limit < 10000)
            $nbki_score -= 24;
        elseif ($pdl_prolong_3m_limit >= 10000 && $pdl_prolong_3m_limit < 20000)
            $nbki_score += 23;
        elseif ($pdl_prolong_3m_limit >= 20000 && $pdl_prolong_3m_limit < 40000)
            $nbki_score += 51;
        elseif ($pdl_prolong_3m_limit >= 40000 && $pdl_prolong_3m_limit < 80000)
            $nbki_score += 72;
        elseif ($pdl_prolong_3m_limit >= 80000)
            $nbki_score += 99;

        if ($consum_current_limit_max < 10000)
            $nbki_score -= 66;
        elseif ($consum_current_limit_max >= 10000 && $consum_current_limit_max < 100000)
            $nbki_score += 38;
        elseif ($consum_current_limit_max >= 100000 && $consum_current_limit_max < 300000)
            $nbki_score += 56;
        elseif ($consum_current_limit_max >= 300000)
            $nbki_score += 77;

        if ($consum_good_limit < 1)
            $nbki_score -= 28;
        elseif ($consum_good_limit >= 1 && $consum_good_limit < 100000)
            $nbki_score += 45;
        elseif ($consum_good_limit >= 100000 && $consum_good_limit < 400000)
            $nbki_score += 61;
        elseif ($consum_good_limit >= 400000)
            $nbki_score += 88;


        if ($nbki_score < 200)
            $limit = 0;
        elseif ($nbki_score >= 200 && $nbki_score < 799)
            $limit = 3000;
        elseif ($nbki_score >= 800 && $nbki_score < 899)
            $limit = 5000;
        elseif ($nbki_score >= 900)
            $limit = 7000;

        if ($nbki_score < 200)
            $update = [
                'status' => 'completed',
                'body' => 'Проверка не пройдена',
                'success' => 0,
                'string_result' => 'Отказ'
            ];
        else
            $update = [
                'status' => 'completed',
                'body' => 'Проверка пройдена',
                'success' => 1,
                'string_result' => 'Лимит: ' . $limit
            ];

        $variables =
            [
                'pdl_overdue_count' => $pdl_overdue_count,
                'pdl_npl_limit_share' => $pdl_npl_limit_share,
                'pdl_npl_90_limit_share' => $pdl_npl_90_limit_share,
                'pdl_current_limit_max' => $pdl_current_limit_max,
                'pdl_last_3m_limit' => $pdl_last_3m_limit,
                'pdl_last_good_max_limit' => $pdl_last_good_max_limit,
                'pdl_good_limit' => $pdl_good_limit,
                'pdl_prolong_3m_limit' => $pdl_prolong_3m_limit,
                'consum_current_limit_max' => $consum_current_limit_max,
                'consum_good_limit' => $consum_good_limit,
                'limit' => (isset($limit)) ? $limit : 0
            ];

        $nbkiScoreBalls =
            [
                'order_id' => $scoring->order_id,
                'score_id' => $scoring->id,
                'ball' => $nbki_score,
                'variables' => json_encode($variables)
            ];

        $this->NbkiScoreballs->add($nbkiScoreBalls);

        $this->scorings->update_scoring($scoring->id, $update);
        return $update;
    }

    private function oldClient($nbki, $scoring)
    {
        $nbki_score = 456;
        $prev_3000_500_paid_count_wo_del = 0;
        $sumPayedPercents = 0;
        $sumPayedPercents3000 = 0;
        $prev_max_delay = 0;
        $current_overdue_sum = 0;
        $closed_to_total_credits_count_share = 0;
        $sumAccountRate13 = 0;
        $sumAccountRate = 0;
        $pdl_overdue_count = 0;
        $pdl_npl_90_limit_share = 0;
        $sumAllPdl = 0;
        $sumPdl90 = 0;

        $lastContract = ContractsORM::where('user_id', $scoring->user_id)->orderBy('id', 'desc')->first();
        $allContracts = ContractsORM::where('user_id', $scoring->user_id)->get();

        $now = new DateTime(date('Y-m-d'));
        $returnDateLastContract = new DateTime(date('Y-m-d', strtotime($lastContract->return_date)));
        $days_from_last_closed = date_diff($now, $returnDateLastContract)->days;
        $last_credit_delay = $lastContract->count_expired_days;

        foreach ($allContracts as $contract) {
            $operations = OperationsORM::where('order_id', $contract->order_id)->where('type', 'PAY')->get();

            foreach ($operations as $operation) {
                $transaction = TransactionsORM::find($operation->transaction_id);

                $sumPayedPercents += $transaction->loan_percents_summ;

                if ($contract->amount >= 3000)
                    $sumPayedPercents3000 += $transaction->loan_percents_summ;
            }

            if ($sumPayedPercents3000 >= 500 && $contract->count_expired_days == 0)
                $prev_3000_500_paid_count_wo_del++;

            if ($contract->count_expired_days > $prev_max_delay)
                $prev_max_delay = $contract->count_expired_days;
        }

        foreach ($nbki['json']['AccountReply'] as $scor) {
            $current_overdue_sum += $scor['amtPastDue'];
            $sumAccountRate += $scor['creditLimit'];

            if (!empty($scor['accountRating'])) {
                if ($scor['accountRating'] == 13)
                    $sumAccountRate13 += $scor['creditLimit'];
            }

            if (in_array($scor['acctType'], [16, 9, 7]) && $scor['creditLimit'] <= 30000) {

                $sumAllPdl += $scor['creditLimit'];

                if($scor['amtPastDue'] > 0)
                    $pdl_overdue_count ++;

                $scor['paymtPat'] = preg_replace('/[^0-9]/', '', $scor['paymtPat']);

                if (!empty($scor['paymtPat'])) {
                    $scor['paymtPat'] = str_split($scor['paymtPat']);

                    foreach ($scor['paymtPat'] as $value) {
                        if ($value >= 4) {
                            $sumPdl90 += $scor['creditLimit'];
                        }
                    }
                }
            }
        }

        if($sumAllPdl != 0)
            $pdl_npl_90_limit_share = $sumAllPdl / $sumPdl90;

        if($sumAccountRate != 0)
            $closed_to_total_credits_count_share = $sumAccountRate / $sumAccountRate13;


        if ($days_from_last_closed < 1)
            $nbki_score += 7;
        elseif ($days_from_last_closed >= 1 && $days_from_last_closed < 2)
            $nbki_score += 31;
        elseif ($days_from_last_closed >= 2 && $days_from_last_closed < 15)
            $nbki_score += 44;
        elseif ($days_from_last_closed >= 15 && $days_from_last_closed < 30)
            $nbki_score += 33;
        elseif ($days_from_last_closed >= 30 && $days_from_last_closed < 60)
            $nbki_score += 2;
        elseif ($days_from_last_closed >= 60 && $days_from_last_closed < 90)
            $nbki_score -= 20;
        elseif ($days_from_last_closed >= 90)
            $nbki_score -= 51;

        if ($prev_3000_500_paid_count_wo_del < 2)
            $nbki_score -= 21;
        elseif ($prev_3000_500_paid_count_wo_del >= 2 && $prev_3000_500_paid_count_wo_del < 4)
            $nbki_score += 31;
        elseif ($prev_3000_500_paid_count_wo_del >= 4 && $prev_3000_500_paid_count_wo_del < 6)
            $nbki_score += 69;
        elseif ($prev_3000_500_paid_count_wo_del >= 6 && $prev_3000_500_paid_count_wo_del < 8)
            $nbki_score += 101;
        elseif ($prev_3000_500_paid_count_wo_del >= 8)
            $nbki_score += 154;

        if ($sumPayedPercents < 2000)
            $nbki_score -= 14;
        elseif ($sumPayedPercents >= 2000 && $sumPayedPercents < 4000)
            $nbki_score += 11;
        elseif ($sumPayedPercents >= 4000 && $sumPayedPercents < 8000)
            $nbki_score += 38;
        elseif ($sumPayedPercents >= 8000 && $sumPayedPercents < 20000)
            $nbki_score += 73;
        elseif ($sumPayedPercents >= 20000 && $sumPayedPercents < 40000)
            $nbki_score += 107;
        elseif ($sumPayedPercents >= 40000)
            $nbki_score += 141;

        if ($prev_max_delay < 30)
            $nbki_score -= 7;
        elseif ($prev_max_delay >= 30 && $prev_max_delay < 60)
            $nbki_score -= 36;
        elseif ($prev_max_delay >= 60)
            $nbki_score -= 98;

        if ($last_credit_delay < 10)
            $nbki_score += 45;
        elseif ($last_credit_delay >= 10 && $last_credit_delay < 20)
            $nbki_score += 25;
        elseif ($last_credit_delay >= 20 && $last_credit_delay < 30)
            $nbki_score -= 12;
        elseif ($last_credit_delay >= 30 && $last_credit_delay < 60)
            $nbki_score -= 93;
        elseif ($last_credit_delay >= 60)
            $nbki_score -= 264;

        if ($current_overdue_sum < 10000)
            $nbki_score += 26;
        elseif ($current_overdue_sum >= 10000 && $current_overdue_sum < 50000)
            $nbki_score += 17;
        elseif ($current_overdue_sum >= 50000 && $current_overdue_sum < 100000)
            $nbki_score -= 2;
        elseif ($current_overdue_sum >= 100000 && $current_overdue_sum < 200000)
            $nbki_score -= 25;
        elseif ($current_overdue_sum >= 200000)
            $nbki_score -= 55;

        if ($closed_to_total_credits_count_share < 0.7)
            $nbki_score -= 58;
        elseif ($closed_to_total_credits_count_share >= 0.7 && $closed_to_total_credits_count_share < 0.8)
            $nbki_score -= 28;
        elseif ($closed_to_total_credits_count_share >= 0.8 && $closed_to_total_credits_count_share < 0.85)
            $nbki_score -= 4;
        elseif ($closed_to_total_credits_count_share >= 0.85 && $closed_to_total_credits_count_share < 0.9)
            $nbki_score += 32;
        elseif ($closed_to_total_credits_count_share >= 0.9 && $closed_to_total_credits_count_share < 0.95)
            $nbki_score += 74;
        elseif ($closed_to_total_credits_count_share >= 0.95)
            $nbki_score += 133;

        if ($pdl_overdue_count < 3)
            $nbki_score += 15;
        elseif ($pdl_overdue_count >= 3 && $pdl_overdue_count < 5)
            $nbki_score -= 15;
        elseif ($pdl_overdue_count >= 5 && $pdl_overdue_count < 7)
            $nbki_score -= 37;
        elseif ($pdl_overdue_count >= 7 && $pdl_overdue_count < 10)
            $nbki_score -= 73;
        elseif ($pdl_overdue_count >= 10)
            $nbki_score -= 122;

        if ($pdl_npl_90_limit_share < 1)
            $nbki_score += 22;
        elseif ($pdl_npl_90_limit_share >= 1 && $pdl_npl_90_limit_share < 5)
            $nbki_score += 13;
        elseif ($pdl_npl_90_limit_share >= 5 && $pdl_npl_90_limit_share < 10)
            $nbki_score -= 4;
        elseif ($pdl_npl_90_limit_share >= 10 && $pdl_npl_90_limit_share < 15)
            $nbki_score -= 19;
        elseif ($pdl_npl_90_limit_share >= 15 && $pdl_npl_90_limit_share < 20)
            $nbki_score -= 31;
        elseif ($pdl_npl_90_limit_share >= 20)
            $nbki_score -= 47;

        $limit = 0;

        if ($nbki_score >= 0 && $nbki_score <= 299)
            $limit = 3000;
        elseif ($nbki_score >= 300 && $nbki_score <= 499)
            $limit = 5000;
        elseif ($nbki_score >= 500 && $nbki_score <= 549)
            $limit = 7000;
        elseif ($nbki_score >= 550 && $nbki_score <= 599)
            $limit = 10000;
        elseif ($nbki_score >= 600 && $nbki_score <= 699)
            $limit = 15000;
        elseif ($nbki_score >= 700)
            $limit = 20000;

        if ($nbki_score < 0)
            $update = [
                'status' => 'completed',
                'body' => 'Проверка не пройдена',
                'success' => 0,
                'string_result' => 'Отказ'
            ];
        else
            $update = [
                'status' => 'completed',
                'body' => 'Проверка пройдена',
                'success' => 1,
                'string_result' => 'Лимит: ' . $limit
            ];

        $variables =
            [
                'days_from_last_closed' => $days_from_last_closed,
                'prev_3000_500_paid_count_wo_del' => $prev_3000_500_paid_count_wo_del,
                'sumPayedPercents' => $sumPayedPercents,
                'prev_max_delay' => $prev_max_delay,
                'last_credit_delay' => $last_credit_delay,
                'current_overdue_sum' => $current_overdue_sum,
                'closed_to_total_credits_count_share' => $closed_to_total_credits_count_share,
                'pdl_overdue_count' => $pdl_overdue_count,
                'pdl_npl_90_limit_share' => $pdl_npl_90_limit_share,
                'limit' => $limit
            ];

        $nbkiScoreBalls =
            [
                'order_id' => $scoring->order_id,
                'score_id' => $scoring->id,
                'ball' => $nbki_score,
                'variables' => json_encode($variables)
            ];

        $this->NbkiScoreballs->add($nbkiScoreBalls);

        $this->scorings->update_scoring($scoring->id, $update);
        return $update;
    }
}