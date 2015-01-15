<?php
    /**
     * Created by PhpStorm.
     * User: Seta
     * Date: 2015/1/13
     * Time: 下午 12:59
     */

    namespace Seta0909\LaravelTwStreetname;
    class LaravelTwStreetname
    {
        private static $instance;
        private static $originData;
        private static $citys;
        private static $countrys;
        private static $streets;
        private static $zipCode;
        private static $cache;

        private static function getInstance()
        {
            if (!isset(self::$instance)) {
                $class          = __CLASS__;
                self::$instance = new $class();
                //先載載入快取

                session_start();
                self::$cache = 'session';
                self::$originData = $_SESSION['LaravelTwStreetnameOrigin'];
                self::$citys      = $_SESSION['LaravelTwStreetnameCitys'];
                self::$countrys   = $_SESSION['LaravelTwStreetnameCountrys'];
                self::$streets    = $_SESSION['LaravelTwStreetnameStreets'];
                self::$zipCode    = $_SESSION['LaravelTwStreetnameZipCode'];
                
                
                //載入街道Json資料
                if (!is_array(self::$originData)) {
                    $streetString     = file_get_contents(dirname(__FILE__) . "/address_data.json");
                    self::$originData = json_decode($streetString, true);
                    $_SESSION['LaravelTwStreetnameOrigin'] = self::$originData;
                }
                //載入郵遞區號Json資料
                if (!is_array(self::$zipCode)) {
                    $streetString  = file_get_contents(dirname(__FILE__) . "/mailcode.json");
                    self::$zipCode = json_decode($streetString, true);
                    $temp          = [];
                    foreach (self::$zipCode as $key => $val) {
                        $temp[$val['country']] = $val['mailcode'];
                    }
                    self::$zipCode = $temp;
                    $_SESSION['LaravelTwStreetnameZipCode'] = self::$zipCode;
                    }
                }

                //初始化資料
                //載入城市
                if (!is_array(self::$citys)) {
                    foreach (self::$originData as $key => $val) {
                        if ($val['link'] == 0) {
                            self::$citys[] = $val;
                        }
                    }
                    $_SESSION['LaravelTwStreetnameCitys'] = self::$citys;
                }
                //載入鄉鎮區
                if (!is_array(self::$countrys)) {
                    foreach (self::$citys as $key => $val) {
                        self::$countrys[$val['uid']] = self::searchLink($val['uid']);
                    }
                    $_SESSION['LaravelTwStreetnameCountrys'] = self::$countrys;
                }
                //載入街道
                if (!is_array(self::$streets)) {
                    foreach (self::$countrys as $key => $country) {
                        foreach ($country as $val) {
                            if(isset($val['uid']))
                            {
                                self::$streets[$val['uid']] = self::searchLink($val['uid']);
                            }
                        }
                    }
                    $_SESSION['LaravelTwStreetnameStreets'] = self::$streets;
                }
            }
        }

        private static function getCityId($name)
        {
            foreach (self::$citys as $key => $val) {
                if (isset($val['name']) && $val['name'] == $name) {
                    return $val['uid'];
                }
            }
        }

        private static function getCountryId($name)
        {
            foreach (self::$countrys as $country) {
                foreach ($country as $key => $val) {
                    if (isset($val['name']) && $val['name'] == $name) {
                        return $val['uid'];
                    }
                }
            }
        }

        private static function searchLink($link)
        {
            $result = [];
            foreach (self::$originData as $key => $val) {
                if ($val['link'] == $link) {
                    $result[] = $val;
                }
            }
            return $result;
        }

        public static function getCity()
        {
            self::getInstance();
            return self::$citys;
        }

        public static function getCountry($city)
        {
            self::getInstance();
            if (is_string($city)) {
                return (isset(self::$countrys[self::getCityId($city)])) ? self::$countrys[self::getCityId($city)] : '';
            } else if (is_integer($city)) {
                return (isset(self::$countrys[$city])) ? self::$countrys[$city] : '';
            } else {
                return [];
            }
        }

        public static function getStreet($country)
        {
            self::getInstance();
            if (is_string($country)) {
                return (isset(self::$streets[self::getCountryId($country)])) ? self::$streets[self::getCountryId($country)] : '';
            } else if (is_integer($country)) {
                return (isset(self::$streets[$country])) ? self::$streets[$country] : '';
            } else {
                return [];
            }
        }

        public static function getCode($country)
        {
            self::getInstance();
            return (isset(self::$zipCode[$country])) ? self::$zipCode[$country] : '';
        }
    }
