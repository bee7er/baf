<?php

/**
 * Created by PhpStorm.
 * User: brianetheridge
 * Date: 21/10/2017
 * Time: 12:40
 */
class DbUtils
{
    /**
     * Get a wp option value
     *
     * @return mixed|string
     */
    public static function getOptionValue($optionName)
    {
        global $wpdb;

        $value = "Sorry, something went wrong retrieving wp data";
        // Get the option value
        $option = $wpdb->get_results(
            $wpdb->prepare(
                "select * from `wp_options`
                  where option_name='%s'
                  order by `option_id` asc limit 1", [$optionName]
            ),
            ARRAY_A);

        if ($option) {
            $option = $option[0];

            $value = $option['option_value'];
        }

        return $value;
    }

    /**
     * Retrieve all active society details
     *
     * @return string
     */
    public static function getSocieties()
    {
        global $wpdb;

        // Get the option value
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "select * from `society_list`
                  where active='%s'
                  order by `society-name` asc", ['Yes']
            ),
            ARRAY_A);

        return $results;
    }

    /**
     * Adds slashes recursively to array and array elements
     *
     * @param $arr
     * @return string
     */
    public static function quote(&$arr)
    {
        if (is_array($arr)) {
            foreach ($arr as &$val) {
                is_array($val) ?
                    self::quote($val) : $val = addslashes($val);
            }

            unset($val);
        } else {
            $arr = addslashes($arr);
        }

        return $arr;
    }
}

/**
 * Returns the user id of the currently logged in user
 * @return int
 */
function getCurrentUserId() {
    if ( ! function_exists( 'wp_get_current_user' )) {
        return 0;
    }

    $user = wp_get_current_user();

    return ( isset( $user->ID ) ? (int) $user->ID : 0 );
}
