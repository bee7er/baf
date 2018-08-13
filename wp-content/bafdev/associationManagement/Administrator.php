<?php

abstract class Administrator
{
    /**
     * Get the current image of the site from db
     */
    abstract public static function getCurrentObject($id);

    /**
     * Checks whether the current user is a system admin
     *
     * @return bool
     */
    public static function isSuperUser()
    {
        $user = wp_get_current_user();
        // If the current user email address is a System admin
        if (!(strpos(SYSTEM_EMAIL_ADDRESSES, $user->user_email) !== false)) {
            return true;
        }

        return false;
    }

    /**
     * Validate form details
     *
     * @param $data
     * @param $requiredFields
     * @return array
     */
    public function getValidationErrors($data, $requiredFields = [])
    {
        $errors = [];
        foreach ($data as $field => $value) {
            if (in_array($field, $requiredFields)) {
                if (!$value) {
                    $errors[$field] = 'Please enter your ' . PageUtils::formatFieldName($field);
                }
            }
        }
        return $errors;
    }

    /**
     * Add an audit entry showing before image and after is the data that changed
     *
     * @param $id
     * @param array $data
     * @param array $before
     * @param $currentUser
     */
    public static function insertAudit($id, $data, array $before, $currentUser)
    {
        global $wpdb;

        // Get the current new state
        // NB using static to obtain late static binding on method from derived class
        $after = static::getCurrentObject($id);
        $changedFields = array();
        // Work out what has changed
        foreach ($data as $field => $value) {
            if ($field == 'allotmentId') {
                // Ignore the id and continue
            } else {
                if ($before[$field] != $after[$field]) {
                    // Field has changed
                    $changedFields[$field] = $after[$field];
                }
            }
        }

        $beforeJson = json_encode($before);
        $changedFieldsJson = json_encode($changedFields);

        $sql = "INSERT INTO `site_list_audit`
              (`site-list-id`,`before`,`after`,`user-id`,`created`)
              VALUES('%d','%s','%s','%d','%s')";

        $sql = $wpdb->prepare($sql, [$id, $beforeJson, $changedFieldsJson, $currentUser->ID, date('Y-m-d H:i:s')]);
        $wpdb->query($sql);

        return array($before, $changedFields);
    }
}
