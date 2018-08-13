<?php

include_once realpath(__DIR__ . "/Administrator.php");
include_once realpath(__DIR__ . "/SiteAdministrator.php");
include_once realpath(__DIR__ . "/../utils/PageUtils.php");

class SocietyAdministrator extends Administrator
{
    // View capability (via the corresponding role) enables all societies
    // to be viewed
    // Full capability allows all society details to be edited
    const VIEW_PERMISSION = 'baf_committee_view';
    const FULL_PERMISSION = 'baf_committee_full';

    /**
     * Validate form details
     *
     * @param $data
     * @return array
     */
    public function getValidationErrors($data, $requiredFields = [])
    {
        $requiredFields = ['society-name'];

        return parent::getValidationErrors($data, $requiredFields);
    }

    /**
     * Select an extract of data
     *
     * @param $selectStr
     * @return array|null|object
     */
    public static function getSocietyExtract($selectStr)
    {
        global $wpdb;

        $societies = $wpdb->get_results(
            $wpdb->prepare($selectStr, []), ARRAY_A
        );

        return $societies;
    }

    /**
     * Get the society list from db
     */
    public static function getSocietyList($activeOnly = true)
    {
        global $wpdb;

        $where = '(1=1) ';
        if ($activeOnly) {
            $where .= "AND `active`='Yes'";
        }
        $societies = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `society_list`
                  WHERE $where
                  ORDER BY `society-name`", []), ARRAY_A
        );

        return $societies;
    }

    /**
     * Get the society from db for a given admin
     * Used to generate custom menu options for the logged in
     * society administrator
     */
    public static function getSocietyListByAdmin($societyAdminUserName, $orderBy = '`society-name` ASC')
    {
        global $wpdb;

        $allotments = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `society_list`
              WHERE `society-admin-username` = '$societyAdminUserName' ORDER BY $orderBy", []), ARRAY_A
        );

        return $allotments;
    }

    /**
     * Get the current image of the society from db
     */
    public static function getCurrentObject($societyId)
    {
        return self::getSociety($societyId);
    }

    /**
     * Get the society from db
     */
    public static function getSociety($societyId)
    {
        global $wpdb;

        $society = null;

        if ($societyId) {
            $societies = $wpdb->get_results(
                $wpdb->prepare("select * from `society_list` where id='%d'", $societyId), ARRAY_A
            );
            if (is_array($societies) && count($societies) > 0) {
                $society = $societies[0];
            }
        }

        return $society;
    }

    /**
     * Checks whether the current user is the society admin
     */
    public static function isSocietyAdministrator($currentUser, $society)
    {
        if ($currentUser
            && self::isAdministrator($currentUser)
        ) {
            if ($society['society-admin-username'] == $currentUser->user_login) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the current user is an admin
     */
    public static function isAdministrator($currentUser)
    {
        if ($currentUser) {
            if (in_array('society_admin', $currentUser->roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the current user is a society committee admin
     *
     * @param $currentUser
     * @param $permissions
     * @param $society
     * @return bool
     */
    public static function isSocietyCommitteeAdministrator($currentUser, $permissions)
    {
        if (!$currentUser) {
            return false;
        }

        // Check if they have the specified role
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        if ($currentUser->roles) {
            foreach ($permissions as $permission) {
                if (in_array($permission, $currentUser->roles)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update the society_list details
     *
     * @param array $data
     * @return false|int
     */
    public function update(array $data, $currentUser)
    {
        global $wpdb;

        $sql = "UPDATE `society_list` SET ";
        $sep = '';
        $id = null;
        foreach ($data as $field => $value) {
            if ($field == 'societyId') {
                // Grab the id and continue
                $id = $value;
            } else {
                $sql .= ($sep . "`$field`='" . $value . "'");
                $sep = ',';
            }
        }
        $sql .= ", updated=now() WHERE `id`=%d";

        if (!$id) {
            die('Could not find society id in post data');
        }

        // Retrieve the current state
        $before = $this->getSociety($id);
        // Update the society list table
        $wpdb->query(
            $wpdb->prepare($sql, $id)
        );
        // Write an audit entry
        return self::insertAudit($id, $data, $before, $currentUser);
    }

    /**
     * Builds and returns the society list table
     */
    public static function getListTable()
    {
        $html = '';

        $html .= '<table border="0" cellpadding="1" cellspacing="1" style="width:100%;margin:30px 0;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<td style="height:17px; width:25%"><strong>Society name</strong></td>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $societies = self::getSocietyList();

        if (count($societies) <= 0) {

            $html .= '<tr><td colspan="4" style="height:17px;">No societies found</td></tr>';

        } else {

            foreach ($societies as $society) {
                $html .= '<tr>';
                $html .= ('<td>' .
                    '<a href="' . APP_DIR . '/allotment-society-finder-details/?society_id='
                        . $society['id'] . '">' . $society['society-name'] . '</a></td>');
                $html .= '</tr>';
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Builds and returns the society view table
     *
     * @param $societyId
     * @param $systemEmails
     * @return string
     */
    public static function getSocietyViewTable($societyId, $systemEmails)
    {
        $html = '';

        $society = SocietyAdministrator::getSociety($societyId);
        if (!$society) {

            $html .= "<p>Sorry, we could not find the society for id '" . $societyId . "'</p>";

        } else {
            // Check if the current user is logged in and admin capable
            $user = wp_get_current_user();
            $isSocietyCommitteeAdmin = SocietyAdministrator::isSocietyCommitteeAdministrator($user,
                self::FULL_PERMISSION);

            $isSocietyAdmin = self::isSocietyAdministrator($user, $society);

            $html .= "<h3>" . $society['society-name'] . "</h3>";

            if ($isSocietyAdmin || $isSocietyCommitteeAdmin) {
                $html .= '<a href="' . APP_DIR . '/allotment-society-update-details?society_id=' . $societyId . '">Edit society details</a>';
            }

            $html .= '<table border="0" cellpadding="1" cellspacing="1" style="height:90px;width:600px;margin:30px 0;">';
            $html .= "<tbody>";

            /*
            $html .= '<tr><td style="height:17px; width:260px">Society account</td><td style="width:260px">' . $society['website-account'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Society account user name</td><td style="width:260px">' . $society['website-username'] . '</td></tr>';
            */
            $html .= '<tr><td style="height:17px; width:260px">Society Website</td><td style="width:260px">' . PageUtils::makeClickable($society['society-website']) . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:260px">Chair</td><td style="width:260px">' . $society['chair'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Chair email</td><td style="width:260px">' . $society['chair-email'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Chair telephone</td><td style="width:260px">' . $society['chair-telephone'] . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:260px">Secretary</td><td style="width:260px">' . $society['secretary'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Secretary email</td><td style="width:260px">' . PageUtils::makeClickable($society['secretary-email']) . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Secretary telephone</td><td style="width:260px">' . $society['secretary-telephone'] . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:260px">Treasurer</td><td style="width:260px">' . $society['treasurer'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Treasurer email</td><td style="width:260px">' . PageUtils::makeClickable($society['treasurer-email']) . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Treasurer telephone</td><td style="width:260px">' . $society['treasurer-telephone'] . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:260px">Society rep</td><td style="width:260px">' . $society['society-rep'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Society rep email</td><td style="width:260px">' . $society['society-rep-email'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Society rep telephone</td><td style="width:260px">' . $society['society-rep-telephone'] . '</td></tr>';

            $html .= "</tbody>";
            $html .= "</table>";

            $html .= '<br>';
            $html .= '<h3>Manages Allotment Site(s)</h3>';
            $html .= '<table border="0" cellpadding="1" cellspacing="1" style="height:90px;width:600px;margin:30px 0;">';
            $html .= "<thead>";
            $html .= '<tr>';
            $html .= '<th style="height:17px; width:260px">Name</th>';
            $html .= '<th style="height:17px; width:260px">Address</th>';
            $html .= '<th style="height:17px; width:260px">Postcode</th>';
            $html .= '</tr>';
            $html .= "</thead>";
            $html .= "<tbody>";
            $allotments = SiteAdministrator::getAllotmentListBySociety($societyId);
            if ($allotments) {
                foreach ($allotments as $allotment) {
                    $siteLink = '&nbsp;&nbsp;<a href="' . APP_DIR . '/allotment-finder-details/?allotment_id='
                        . $allotment['id'] . '">' . $allotment['site-name'] . '</a>';

                    $html .= '<tr>';
                    $html .= '<td>' . $siteLink . '</td>';
                    $html .= '<td>' . $allotment['site-map'] . '</td>';
                    $html .= '<td>' . $allotment['site-location'] . '</td>';
                    $html .= '</tr>';
                }
            } else {
                $html .= '<tr>';
                $html .= '<td colspan="3">No allotment sites managed at this time</td>';
                $html .= '</tr>';
            }

            $html .= "</tbody>";
            $html .= "</table>";
        }

        return $html;
    }

    /**
     * Build and return the update form table
     *
     * @param $society
     * @return string
     */
    public static function getSocietyUpdateTable($society)
    {
        $html = '';
        $html .= '<table border="0" cellpadding="1" cellspacing="1" style="height:90px;width:600px;margin:30px 0;" border="1">';
        $html .= "<tbody>";

        /*
        if (self::isSuperUser()) {
            $html .= '<tr><td style="height:17px; width:160px">Society name</td><td style="width:360px">' . PageUtils::text('society-name', $society['society-name']) . '</td></tr>';
            $html .= '<tr><td>Society account</td><td>' . PageUtils::text('website-account', $society['website-account']) . '</td></tr>';
            $html .= '<tr><td>Society account user name</td><td>' . PageUtils::text('website-username', $society['website-username']) . '</td></tr>';
        } else {
            $html .= '<tr><td>Society name</td><td style="width:360px;background-color: #c4c4c4">' .
                $society['society-name'] . '</td></tr>';
            $html .= '<tr><td>Society account</td><td style="background-color: #c4c4c4">' . $society['website-account'] . '</td></tr>';
            $html .= '<tr><td>Society account user name</td><td style="background-color: #c4c4c4">' . $society['website-username'] . '</td></tr>'
                . '</td></tr>';
        }
        */

        $html .= '<tr><td>Society website</td><td>' . PageUtils::text('society-website', $society['society-website']) . '</td></tr>';
        $html .= '<tr><td>Chair</td><td>' . PageUtils::text('chair', $society['chair']) . '</td></tr>';
        $html .= '<tr><td>Chair email</td><td>' . PageUtils::text('chair-email', $society['chair-email']) . '</td></tr>';
        $html .= '<tr><td>Chair telephone</td><td>' . PageUtils::text('chair-telephone', $society['chair-telephone']) . '</td></tr>';
        $html .= '<tr><td>Secretary</td><td>' . PageUtils::text('secretary', $society['secretary']) . '</td></tr>';
        $html .= '<tr><td>Secretary email</td><td>' . PageUtils::text('secretary-email', $society['secretary-email']) . '</td></tr>';
        $html .= '<tr><td>Secretary telephone</td><td>' . PageUtils::text('secretary-telephone', $society['secretary-telephone']) . '</td></tr>';
        $html .= '<tr><td>Treasurer</td><td>' . PageUtils::text('treasurer', $society['treasurer']) . '</td></tr>';
        $html .= '<tr><td>Treasurer email</td><td>' . PageUtils::text('treasurer-email', $society['treasurer-email']) . '</td></tr>';
        $html .= '<tr><td>Treasurer telephone</td><td>' . PageUtils::text('treasurer-telephone', $society['treasurer-telephone']) . '</td></tr>';
        $html .= '<tr><td>Society rep</td><td>' . PageUtils::text('society-rep', $society['society-rep']) . '</td></tr>';
        $html .= '<tr><td>Society rep email</td><td>' . PageUtils::text('society-rep-email', $society['society-rep-email']) . '</td></tr>';
        $html .= '<tr><td>Society rep telephone</td><td>' . PageUtils::text('society-rep-telephone',
                $society['society-rep-telephone']) . '</td></tr>';

        $html .= "</tbody>";
        $html .= "</table>";

        return $html;
    }
}
