<?php
namespace CB;

if (!defined("ABSPATH")) exit;

class API
{
    private static $email = "";
    private static $apikey = "";

    public static $responses = array();

    public function getResponse()
    {
        return self::$responses;
    }

    public static function post($url, $data = array())
    {
        self::$email = get_option('cb_cb_username');
        self::$apikey = get_option('cb_cb_api');

        /*if (is_array($url)) {
            $uris = array();
            foreach($url as $u) {
                $uris[] = self::site_url($u);
            }
        } else {
            $url = self::site_url($url);
        }*/

        $url = self::site_url($url);

        if (!self::$email || !self::$apikey) {
            $url = self::site_url('no');
        }

        /*$curl = new CURL();

        $opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => self::$email . ":" . self::$apikey,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => 1
        );

        if (isset($_COOKIE[CB_COOKIE_NAME])) {
            $opts[CURLOPT_COOKIE] = CB_COOKIE_NAME . '=' . $_COOKIE[CB_COOKIE_NAME];
        }

        if (isset($uris) && count($uris) > 0) {
            foreach ($uris as $uri) {
                $curl->addSession($uri, $opts);
            }
        } else {
            $curl->addSession( $url, $opts );
        }

        $response = $curl->exec();

        if (is_array($response))
            self::$responses = $response;

        $curl->clear();*/

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, self::$email . ":" . self::$apikey);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (isset($_COOKIE[CB_COOKIE_NAME])) {
            curl_setopt($ch, CURLOPT_COOKIE, CB_COOKIE_NAME . '=' . $_COOKIE[CB_COOKIE_NAME]);
        }

        $response = curl_exec($ch);

        curl_close($ch);
        self::$responses = $response;

        return new self;
    }

    public function jsonDecode()
    {
        $responses = self::$responses;

        /*if (is_array(self::$responses)) {
            self::$responses = array();
            foreach($responses as $response) {
                self::$responses[] = json_decode($response, true);
            }
        } else {
            self::$responses = json_decode($responses, true);
        }
        */
        self::$responses = json_decode($responses, true);

        return $this;
    }

    public static function site_url($param = "")
    {
        return 'http://dev.classbyte.net/api/' . ltrim($param, '/');
    }

    public function insertCourseClasses()
    {
        if (!self::$responses || isset(self::$responses['code'])) return;

        foreach (self::$responses as $course) {
            foreach ($course['classes'] as $class) {
                $title = $class['coursetypename'] . ' ' . date("F-d-Y", strtotime($class['coursedate'])) . ' ' . $class['location'] . ' Class ' . $class['scheduledcoursesid'];

                $my_post = array(
                    'post_title' => $title,
                    'post_name' => sanitize_title($title),
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => Posttypes::$post_type,
                    'comment_status' => 'closed'
                );

                $cur_post_id = wp_insert_post($my_post);

                if ($cur_post_id) {
                    update_post_meta($cur_post_id, 'cb_zip', $class['locationzip']);

                    update_post_meta($cur_post_id, 'cb_course_schedule_id', $class['scheduledcoursesid']);

                    update_post_meta($cur_post_id, 'cb_course_id', $class['scheduledcoursesid']);

                    update_post_meta($cur_post_id, 'cb_agency', $course['classes'][0]['agency']);

                    update_post_meta($cur_post_id, 'cb_course_location', $class['location']);

                    update_post_meta($cur_post_id, 'cb_course_date_time', date("l, F d, Y", strtotime($class['coursedate'])) . ' at ' . date("g:i a", strtotime($class['coursetime'])));

                    update_post_meta($cur_post_id, 'cb_course_full_object', $class);

                    $cat = \wp_insert_term($course['course']['course_name'], Posttypes::$taxonomy);

                    if (is_wp_error($cat) && array_key_exists('term_exists', $cat->errors))
                        $cat_ID = absint($cat->error_data['term_exists']);
                    else
                        $cat_ID = $cat['term_id'];

                    wp_set_post_terms($cur_post_id, $cat_ID, Posttypes::$taxonomy);
                }
            }
        }
    }
}