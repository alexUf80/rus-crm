<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once( __DIR__ . '/../vendor/autoload.php');

class Efrsb_scoring extends Core
{
    public function run_scoring($scoring_id)
    {
        if ($scoring = $this->scorings->get_scoring($scoring_id)) 
        {
            if ($order = $this->orders->get_order((int)$scoring->order_id)) 
            {
                if (empty($order->inn))
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'Не найден ИНН'
                    );                    
                }
                else
                {
                        $response = $this->getting_html(
                            $order->inn
                        );
echo __LINE__.'<br />'.$response;
                        if (isset($response[0]) & isset($response[1])) {
                            $search = 'Вся информация';
                            $serch_url = 'person';
                            $searchString = 'Ничего не найдено';

                            if (preg_match("/{$searchString}/i", $response[0]) & !preg_match("/{$search}/i", $response[0])) {
                                $update = array(
                                    'status' => 'completed',
                                    'body' => $response[1],
                                    'success' => 1,
                                    'string_result' => 'банкротства не найдены'
                                );
                            } elseif (preg_match("/{$serch_url}/i", $response[1])) {
                                $update = array(
                                    'status' => 'completed',
                                    'body' => serialize([$response[1]]),
                                    'success' => 0,
                                    'string_result' => 'банкротства найдены'
                                );
                            } else {
                                $update = array(
                                    'status' => 'error',
                                    'body' => $response[0],
                                    'string_result' => 'неудачный парсинг'
                                );
                            }
                        } else {
                            $update = array(
                                'status' => 'error',
                                'string_result' => 'При запросе произошла ошибка'
                            );
                        }
                    
                }
                
            } 
            else 
            {
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

    public function getting_html($inn)
    {
        try {
            $host = 'http://' . $this->settings->selenoid . ':4444/wd/hub';

            $capabilities = DesiredCapabilities::chrome();

            $driver = RemoteWebDriver::create($host, $capabilities);

            $driver->get('https://bankrot.fedresurs.ru/bankrupts?searchString='.$inn);

            
            sleep(4);

            $html =  $driver->getPageSource();

            $search = 'Вся информация';

            if (preg_match("/{$search}/i", $html)) {
                $driver->findElement(
                    WebDriverBy::xpath("//span[contains(.,'Вся информация')]")
                )->click();

                sleep(4);  
            } 

            $HandleCount = $driver->getWindowHandles();

            if(isset($HandleCount[1])) {
                $driver->switchTo()->window($HandleCount[1]);
            }

            $url = $driver->getCurrentURL();
            $driver->quit();

            $response = [$html, $url];
        } catch (Exception $e) {
            $response =  [$e->getMessage()];
        }
        return $response;
    }
}