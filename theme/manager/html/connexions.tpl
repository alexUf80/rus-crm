<table class="table  table-bordered">
    <tr>
        <th width="20%">Поиск</th>
        <th width="80%">Результат поиска</th>
    </tr>

    <tr>
        <td>
            <h4>Адрес проживания</h4>
            <h6>{$results['faktaddress']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['faktaddress']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['faktaddress']->found['regaddress'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Адрес регистрации
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['faktaddress']->found['faktaddress'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Адрес проживания
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>Адрес регистрации</h4>
            <h6>{$results['regaddress']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['regaddress']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['regaddress']->found['regaddress'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Адрес регистрации
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['faktaddress']->found['faktaddress'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Адрес проживания
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>Серия и номер паспорта</h4>
            <h6>{$results['passport']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['passport']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['passport']->found['passport'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Серия и номер паспорта
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>Снилс</h4>
            <h6>{$results['snils']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['snils']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['snils']->found['snils'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Снилс
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>Инн</h4>
            <h6>{$results['inn']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['inn']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['inn']->found['inn'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Инн
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>IP при регистрации</h4>
            <h6>{$results['reg_ip']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['reg_ip']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['reg_ip']->found['reg_ip'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                IP при регистрации
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>IP при последнем входе</h4>
            <h6>{$results['last_ip']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['last_ip']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['last_ip']->found['last_ip'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                IP при последнем входе
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>Номер карты</h4>
            <h6>***</h6>
        </td>
        <td class="p-0">
            {if empty($results['card_number']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['card_number']->found['card_number'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Номер карты
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>Мобильный телефон</h4>
            <h6>{$results['phone_mobile']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['phone_mobile']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['phone_mobile']->found['users'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Мобильный телефон
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['phone_mobile']->found['contactpersons'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Контактное лицо
                                <br />
                                {$item->cp_name}
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['phone_mobile']->found['workphone'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Рабочий телефон
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['phone_mobile']->found['chief_phone'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Телефон руководителя
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>Рабочий телефон</h4>
            <h6>{$results['workphone']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['workphone']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['workphone']->found['users'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Мобильный телефон
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['workphone']->found['contactpersons'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Контактное лицо
                                <br />
                                {$item->cp_name}
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['workphone']->found['workphone'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Рабочий телефон
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['workphone']->found['chief_phone'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Телефон руководителя
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>КЛ <small>{$results['contactperson1']->fio}</small></h4>
            <h6>{$results['contactperson1']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['contactperson1']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['contactperson1']->found['users'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Мобильный телефон
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['contactperson1']->found['contactpersons'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Контактное лицо
                                <br />
                                {$item->cp_name}
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['contactperson1']->found['workphone'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Рабочий телефон
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['contactperson1']->found['chief_phone'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Телефон руководителя
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>

    <tr>
        <td>
            <h4>КЛ <small>{$results['contactperson2']->fio}<small></small></h4>
            <h6>{$results['contactperson2']->search}</h6>
        </td>
        <td class="p-0">
            {if empty($results['contactperson2']->found)}
                <h6 class="text-danger p-5">Совпадения не найдены</h6>
            {else}
                <table class="table table-bordered table-hover mb-0">
                    <tr>
                        <td class="jsgrid-cell bg-info text-white" width="40%">Тип поля</td>
                        <td class="jsgrid-cell bg-info text-white" width="60%">Клиент</td>
                    </tr>
                    {foreach $results['contactperson2']->found['users'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Мобильный телефон
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['contactperson2']->found['contactpersons'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Контактное лицо
                                <br />
                                {$item->cp_name}
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['contactperson2']->found['workphone'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Рабочий телефон
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    {foreach $results['contactperson2']->found['chief_phone'] as $item}
                        <tr>
                            <td class="jsgrid-cell">
                                Телефон руководителя
                            </td>
                            <td class="jsgrid-cell">
                                <a href="client/{$item->id}" target="_blank">
                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </td>
    </tr>



</table>
