<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once( __DIR__ . '/../vendor/autoload.php');
//require_once( __DIR__ . '/../autoload.php');

class Fssp2_scoring extends Core
{
    private $captcha_dir = 'files/scorings/captcha/';
    
    private $session_id = null;

    public function __construct()
    {
        parent::__construct();
        
        $this->captcha_dir = $this->config->root_dir.$this->captcha_dir;

        $this->session_id = md5(rand().microtime());
    }

    public function run_scoring($scoring_id)
    {
        $scoring_type = $this->scorings->get_type('fssp2');
        
        if ($scoring = $this->scorings->get_scoring($scoring_id)) {
            $this->scoring_id = $scoring_id;

            if ($user = $this->users->get_user((int)$scoring->user_id)) {
                $result = $this->scoring([
                    'firstname' => $user->firstname,
                    'patronymic' => $user->patronymic,
                    'lastname' => $user->lastname,
                    'birth' => date('d.m.Y', strtotime($user->birth)),
                ]);

                if (!isset($result['result'])) {
                    $update = array(
                        'status' => 'error',
                        'body' => serialize($result),
                        'success' => 0,
                        'string_result' => 'Не удалось получить ответ'
                    );
            
                    $this->scorings->update_scoring($scoring_id, $update);
            
                    return $update;
                }

                if ($result['result'] == 'Ошибка парсинга') {
                    $update = array(
                        'status' => 'error',
                        'body' => serialize($result),
                        'success' => 0,
                        'string_result' => 'Ошибка парсинга'
                    );
            
                    $this->scorings->update_scoring($scoring_id, $update);
            
                    return $update;
                }

                if ($result['result'] == 'По вашему запросу ничего не найдено') {
                    $update = array(
                        'status' => 'completed',
                        'body' => serialize($result),
                        'success' => 1,
                        'string_result' => 'По вашему запросу ничего не найдено'
                    );
            
                    $this->scorings->update_scoring($scoring_id, $update);
            
                    return $update;
                }

                if (strpos($result['result'], 'Не удалось осуществить поиск') !== false) {
                    $update = array(
                        'status' => 'error',
                        'body' => serialize($result),
                        'success' => 0,
                        'string_result' => $result['result']
                    );
            
                    $this->scorings->update_scoring($scoring_id, $update);
            
                    return $update;
                }

                if (strpos($result['result'], 'Найдено записей') !== false) {
                    $re = '/(\d+.\d\d) руб/';
                    
                    preg_match_all($re, $result['html'], $matches, PREG_SET_ORDER, 0);
                    
                    $result['sum'] = array_reduce($matches, function($sum, $item) {
                        return $sum + $item[1];
                    }, 0);
                    
                    $string_result = 'Найденная сумма долга: '.$result['sum'];

                    if ($result['sum'] >= $scoring_type->params['amount']) {
                        $success = 0;
                    } else {
                        $success = 1;
                    }
                    
                    $update = array(
                        'status' => 'completed',
                        'body' => serialize($result),
                        'success' => $success,
                        'string_result' => $string_result,//$result['result']
                    );
            
                    $this->scorings->update_scoring($scoring_id, $update);
            
                    return $update;
                }

                $update = array(
                    'status' => 'error',
                    'body' => serialize($result),
                    'success' => 0,
                    'string_result' => 'Что-то пошло не так'
                );
        
                $this->scorings->update_scoring($scoring_id, $update);
        
                return $update;
            } else {
                $update = [
                    'status' => 'error',
                    'string_result' => 'не найден пользователь'
                ];
                $this->scorings->update_scoring($scoring_id, $update);
                return $update;
            }
        }
    }

    public function scoring($data)
    {
        $options = new ChromeOptions();

        $host = 'http://' . $this->settings->selenoid . ':4444/wd/hub';

        $capabilities = new DesiredCapabilities(
           [
            WebDriverCapabilityType::BROWSER_NAME => 'chrome',
           ]
        );

        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        $driver = RemoteWebDriver::create(
            $host, 
            $capabilities,
            35 * 1000, // Connection timeout in miliseconds
            35 * 1000  // Request timeout in miliseconds
            //60 * 1000, // Connection timeout in miliseconds
            //60 * 1000  // Request timeout in miliseconds
        );
        $driver->get('https://fssp.gov.ru/iss/ip');

        sleep(1);

        /*
            self.driver.find_element(By.ID, "region_id_chosen").click()
            self.driver.find_element(By.CSS_SELECTOR, ".active-result:nth-child(85)").click()
            self.driver.find_element(By.ID, "input01").click()
            self.driver.find_element(By.ID, "input01").send_keys("Иванов")
            self.driver.find_element(By.ID, "input02").send_keys("Иван")
            self.driver.find_element(By.ID, "input05").send_keys("Иванович")
            self.driver.find_element(By.ID, "input06").click()
            self.driver.find_element(By.ID, "input06").send_keys("19.12.1999")
            self.driver.find_element(By.ID, "btn-sbm").click()
        */
        //#capchaVisual
        //Хайдаров Хусан Ибрахимович 17.05.1984
        $driver->findElement(
            WebDriverBy::id("region_id_chosen")
        )->click();

        $driver->findElement(
            WebDriverBy::cssSelector(".active-result:nth-child(85)")
        )->click();

        $driver->findElement(
            WebDriverBy::id("input01")
        )->sendKeys($data['lastname']);

        $driver->findElement(
            WebDriverBy::id("input02")
        )->sendKeys($data['firstname']);

        $driver->findElement(
            WebDriverBy::id("input05")
        )->sendKeys($data['patronymic']);

        $driver->findElement(
            WebDriverBy::id("input06")
        )->click();

        $driver->findElement(
            WebDriverBy::id("input06")
        )->sendKeys($data['birth']);

        $driver->findElement(
            WebDriverBy::id("btn-sbm")
        )->click();

        sleep(11);

        $captchaCollection = $driver->findElements(
            WebDriverBy::id("capchaVisual")
        );

        //if (WebDriverExpectedCondition::elementTextContains(WebDriverBy::cssSelector("h2"), "ВВЕДИТЕ КОД С КАРТИНКИ:")) {
        if (count($captchaCollection) > 0) {
            //$html =  $driver->getPageSource();
            //$driver->takeScreenshot('../logs/image123.png');

            $captcha = $driver->findElement(
                WebDriverBy::id("capchaVisual")
            )->getAttribute('src');

            $img = str_replace('data:image/jpeg;base64,', '', $captcha);
            //$img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            //$this->captcha_dir.$this->session_id.'.jpeg'
            $file = $this->captcha_dir.$this->session_id.'.jpeg';
            $success = file_put_contents($file, $data);

            echo '<img src="'.$captcha.'">';

            $code = $this->get_captcha_code($file);
            
            /*
                driver.findElement(By.id("captcha-popup-code")).sendKeys("дж9с8");
                driver.findElement(By.id("ncapcha-submit")).click();
                assertThat(driver.findElement(By.cssSelector(".b-search-message__text > h4")).getText(), is("По вашему запросу ничего не найдено"));
            */

            $driver->findElement(
                WebDriverBy::id("captcha-popup-code")
            )->sendKeys($code);

            $driver->findElement(
                WebDriverBy::id("ncapcha-submit")
            )->click();

            sleep(35);

            //$driver->wait(20, 500)
            //->until(WebDriverExpectedCondition::invisibilityOfElementLocated(
            //    //css=.iss:nth-child(8) > .b-full-layer > .b-center-loader
            //    //By.cssSelector(".iss:nth-child(8) > .b-full-layer > .b-center-loader")
            //    WebDriverBy::cssSelector(".iss:nth-child(8) > .b-full-layer > .b-center-loader")
            //));
        }
        //$driver->takeScreenshot('../logs/image456.png');

        $element = $driver->findElements(
            WebDriverBy::cssSelector(".b-search-message__text > h4")
        );

        if (count($element) > 0) {
            $text = $driver->findElement(
                WebDriverBy::cssSelector(".b-search-message__text > h4")
            )->getText();

            return [
                'result' => $text,
                'html' => $driver->getPageSource()
            ];
        }

        $element2 = $driver->findElements(
            WebDriverBy::cssSelector(".search-found-total-inner")
        );

        if (count($element2) > 0) {
            $element = $driver->findElement(
                WebDriverBy::cssSelector(".search-found-total-inner")
            )->getText();

            $results = $driver->findElements(
                WebDriverBy::cssSelector(".results")
            );

            if (count($results) > 0) {
                $result = $driver->findElement(
                    WebDriverBy::cssSelector(".results")
                );

                $outerHTML = $driver->executeScript('return document.querySelector(".b-wrapper > .b-detail > .iss > .iss > .results").outerHTML');

                return [
                    'result' => $element,
                    'html' => $driver->getPageSource(),
                    'outerHTML' => $outerHTML
                ];
            }

            return [
                'result' => $element,
                'html' => $driver->getPageSource()
            ];
        }

        //$driver->takeScreenshot('../logs/image456.png');

        return [
            'result' => 'Ошибка парсинга',
            'html' => ''
        ];

        //".search-found-total-inner"

        //ничего не найдено
        //#content > div > div > div.iss > div > div > div > h4

        //найдено
        //#content > div > div > div.iss > div > div > div.context > div > div.search-found-total-inner

        //$html =  $driver->getPageSource();

        //var_dump($html);
    }

    public function get_captcha_code($file)
    {
        $task_id = $this->anticaptcha->create_task($file);
        echo $task_id;
        do {
            sleep(1);
            $task_result = $this->anticaptcha->get_task_result($task_id);
        } while (!empty($task_result) && $task_result->status != 'ready' && $task_result->errorId == 0);

        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($task_result);
        echo '</pre><hr />';

        if (empty($task_result->errorId)) {
            $captcha_code = $task_result->solution->text;

            return $captcha_code;          
        }
    }
}

//Колчина Вероника Сергеевна 14.03.1992
//Хайдаров Хусан Ибрахимович 17.05.1984

//echo (new Fssp2_scoring)->scoring([
//    'firstname' => 'Хусан',
//    'patronymic' => 'Ибрахимович',
//    'lastname' => 'Хайдаров',
//    'birth' => '17.05.1985',
//])['result'];
