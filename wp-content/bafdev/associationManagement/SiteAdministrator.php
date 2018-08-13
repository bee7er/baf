<?php

include_once realpath(__DIR__ . "/Administrator.php");
include_once realpath(__DIR__ . "/SocietyAdministrator.php");
include_once realpath(__DIR__ . "/../utils/PageUtils.php");

class SiteAdministrator extends Administrator
{
    /**
     * Validate form details
     *
     * @param $data
     * @return array
     */
    public function getValidationErrors($data, $requiredFields = [])
    {
        $requiredFields = ['site-name','site-map','site-location','lettings-name','letting-email'];

        return parent::getValidationErrors($data, $requiredFields);
    }

    /**
     * Get the allotment from db
     */
    public static function getAllotmentList()
    {
        global $wpdb;

        $allotments = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `site_list`
              WHERE `lettings-name` IS NOT NULL
              AND `lettings-name` != ''
              AND `letting-email` IS NOT NULL
              AND `letting-email` != ''", []), ARRAY_A
        );

        return $allotments;
    }

    /**
     * Get the allotment from db for a given society
     */
    public static function getAllotmentListBySociety($societyId)
    {
        global $wpdb;

        $allotments = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `site_list`
              WHERE `society-id` = $societyId ORDER BY `site-name`", []), ARRAY_A
        );

        return $allotments;
    }

    /**
     * Get the allotment from db for a given admin
     * Used to generate custom menu options for the logged in
     * site administrator
     */
    public static function getAllotmentListByAdmin($assocAdminUserName, $orderBy = '`site-name` ASC')
    {
        global $wpdb;

        $allotments = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `site_list`
              WHERE `assoc-admin-username` = '$assocAdminUserName' ORDER BY $orderBy", []), ARRAY_A
        );

        return $allotments;
    }

    /**
     * Get the current image of the site from db
     */
    public static function getCurrentObject($allotmentId)
    {
        return self::getAllotment($allotmentId);
    }

    /**
     * Get the allotment from db
     */
    public static function getAllotment($allotmentId)
    {
        global $wpdb;

        $allotment = null;

        if ($allotmentId) {
            $allotments = $wpdb->get_results(
                $wpdb->prepare("select * from `site_list` where id='%d'", $allotmentId), ARRAY_A
            );
            if (is_array($allotments) && count($allotments) > 0) {
                $allotment = $allotments[0];
            }
        }

        return $allotment;
    }

    /**
     * Checks whether the current user is the allotment admin
     */
    public static function isAllotmentAdministrator($currentUser, $allotment)
    {
        if ($currentUser
            && self::isAdministrator($currentUser)
        ) {
            if ($allotment['assoc-admin-username'] == $currentUser->user_login) {
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
            if (in_array('association_admin', $currentUser->roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update the site_list details
     *
     * @param array $data
     * @return false|int
     */
    public function update(array $data, $currentUser)
    {
        global $wpdb;

        // Add sewerage and toilet-type if toilet is no
        // because is not returned by the form when disabled
        if ($data['toilet'] == 'No') {
            $data['sewerage'] = 'N/A';
            $data['toilet-type'] = 'N/A';
        }

        $sql = "UPDATE `site_list` SET ";
        $sep = '';
        $id = null;
        foreach ($data as $field => $value) {
            if ($field == 'allotmentId') {
                // Grab the id and continue
                $id = $value;
            } else {
                $sql .= ($sep . "`$field`='" . $value . "'");
                $sep = ',';
            }
        }
        $sql .= " WHERE `id`=%d";

        if (!$id) {
            die('Could not find allotment id in post data');
        }

        // Retrieve the current state
        $before = $this->getAllotment($id);
        // Update the site list table
        $wpdb->query(
            $wpdb->prepare($sql, $id)
        );
        // Write an audit entry
        return self::insertAudit($id, $data, $before, $currentUser);
    }

    /**
     * Build and return the allotment list table
     * @return string
     */
    public function getListTable()
    {
        global $wpdb;

        $html = '';

        $html .= '<table border="0" cellpadding="1" cellspacing="1" style="width:100%;margin:30px 0;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<td style="height:17px; width:25%"><strong>Site name</strong></td>';
        $html .= '<td style="height:17px; width:25%"><strong>Location</strong></td>';
        $html .= '<td style="width:50%"><strong>Managed by</strong></td>';
        $html .= '<td style="width:50%"><strong>Plots available</strong></td>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $allotments = $wpdb->get_results($wpdb->prepare("select * from `site_list` where 1 order by `site-name`", []), ARRAY_A);
        if (count($allotments) <= 0) {

            $html .= '<tr><td colspan="4" style="height:17px;">No allotment sites found</td></tr>';

        } else {

            foreach ($allotments as $allotment) {
                $managedBy = 'None';
                if ($allotment['society-id']) {
                    $society = SocietyAdministrator::getSociety($allotment['society-id']);
                    $managedBy = $society['society-name'];
                }

                $html .= '<tr>';
                $html .= ('<td>' .
                    '<a href="' . APP_DIR . '/allotment-finder-details/?allotment_id=' . $allotment['id'] . '">'
                        . $allotment['site-name'] . '</a></td>');
                $html .= ('<td>' . $allotment['site-location'] . '</td>');
                $html .= ('<td>' . $managedBy . '</td>');
                $html .= ('<td style="text-align: center;">' . $allotment['plots-available'] . '</td>');
                $html .= '</tr>';
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Builds and returns the allotment view table
     */
    public static function getAllotmentViewTable($allotmentId, $systemEmails)
    {
        $html = '';

        $allotment = self::getAllotment($allotmentId);
        if (!$allotment) {

            $html .= "<p>Sorry, we could not find the allotment site for id '" . $allotmentId . "'</p>";

        } else {

            // Check if the current user is logged in and admin capable
            $user = wp_get_current_user();
            if (strpos($systemEmails, $user->user_email) !== false) {
                $isAllotmentAssociationAdmin = true;
            } else {
                $isAllotmentAssociationAdmin = self::isAllotmentAdministrator($user, $allotment);
            }

            $societyLink = 'None';
            $society = '';
            if ($allotment['society-id']) {
                $society = SocietyAdministrator::getSociety($allotment['society-id']);
                if ($society) {
                    $hasViewAuthority = SocietyAdministrator::isSocietyCommitteeAdministrator(
                        $user,
                        SocietyAdministrator::VIEW_PERMISSION
                    );

                    if ($hasViewAuthority) {
                        $societyLink = '<a href="' . APP_DIR .
                            '/allotment-society-finder-details/?society_id='
                            . $society['id'] . '">' . $society['society-name'] . '</a>';
                    } else {
                        $societyLink = $society['society-name'];
                    }
                }
            }

            $html .= '<div style="float:left;width:60%;border: 0px solid red;">';

            $html .= "<h3>" . $allotment['site-name'] . "</h3>";

            if ($isAllotmentAssociationAdmin) {
                $html .= '<a href="' . APP_DIR . '/allotment-update-details?allotment_id=' . $allotmentId . '">Edit allotment site details</a>';
            }

            $html .= '<table border="0" cellpadding="1" cellspacing="1" style="height:90px;width:600px;margin:30px 0;">';
            $html .= "<tbody>";

            $html .= '<tr><td style="height:17px; width:260px">Managed by</td><td style="width:260px">' . $societyLink . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Address</td><td style="width:260px">' . $allotment['site-map'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Post code</td><td style="width:260px">' . $allotment['site-location'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Website</td><td style="width:260px">' .
                PageUtils::makeClickable($allotment['website']) . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Telephone</td><td style="width:260px">' . $allotment['telephone'] . '</td></tr>';

            if ($society) {
                // NB Secretary details come from the Society table
                $html .= '<tr><td style="height:17px; width:260px">Secretary</td><td style="width:260px">' . $society['secretary'] . '</td></tr>';
                $html .= '<tr><td style="height:17px; width:260px">Secretary email</td><td style="width:260px">' . PageUtils::makeClickable($society['secretary-email']) . '</td></tr>';
                $html .= '<tr><td style="height:17px; width:260px">Secretary telephone</td><td style="width:260px">' . $society['secretary-telephone'] . '</td></tr>';
            }

//            $html .= '<tr><td style="height:17px; width:260px">Secretary</td><td style="width:260px">' . $allotment['secretary-name'] . '</td></tr>';
//            $html .= '<tr><td style="height:17px; width:260px">Secretary email</td><td style="width:260px">' .
//                PageUtils::makeClickable($allotment['secretary-email']) . '</td></tr>';
//            $html .= '<tr><td style="height:17px; width:260px">Secretary telephone</td><td style="width:260px">' . $allotment['secretary-tel'] . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:260px">Lettings officer</td><td style="width:260px">' . $allotment['lettings-name'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Lettings email</td><td style="width:260px">' .
                PageUtils::makeClickable($allotment['letting-email']) . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Lettings telephone</td><td style="width:260px">' . $allotment['lettings-tel'] . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:260px">Plots available</td><td style="width:260px">' . $allotment['plots-available'] . '</td></tr>';
            $html .= '<tr><td style="height:17px; width:260px">Number on waiting list</td><td style="width:260px">' . $allotment['waiting-list'] . '</td></tr>';

            $html .= "</tbody>";
            $html .= "</table>";

            $html .= "<h3>Facilities available</h3>";

            $html .= '<table border="0" cellpadding="1" cellspacing="1" style="height:90px;width:600px;margin:30px 0;">';
            $html .= "<tbody>";

            $html .= '<tr><td style="height:17px; width:130px">Water</td><td style="width:130px">' . $allotment['water'] . '</td>';
            $html .= '<td style="height:17px; width:130px">Trading hut</td><td style="width:130px">' . $allotment['trading-hut'] . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:130px">Electricity</td><td style="width:130px">' . $allotment['electricity'] . '</td>';
            $html .= '<td>Mains gas</td><td>' . $allotment['mains-gas'] . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:130px">Toilet</td><td style="width:130px">' . $allotment['toilet'] . '</td>';
            $html .= '<td style="height:17px; width:130px">Sewerage</td><td style="width:130px">' . $allotment['sewerage'] . '</td></tr>';

            $html .= '<tr><td colspan="1">Toilet type</td><td colspan="3">' . $allotment['toilet-type'] . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:130px">Disabled access</td><td style="width:130px">' . $allotment['disabled-access'] . '</td>';
            $html .= '<td style="height:17px; width:130px">Communal area</td><td style="width:130px">' . $allotment['communal-area'] . '</td></tr>';

            $html .= '<tr><td style="height:17px; width:130px">On-site parking</td><td style="width:130px">' . $allotment['on-site_parking'] . '</td>';
            $html .= '<td style="height:17px; width:130px">Social space</td><td style="width:130px">' . $allotment['social-space'] . '</td></tr>';

            $html .= "</tbody>";
            $html .= "</table>";

            if ($allotment['alpha']) {
                $html .= "<h3>A little about us:</h3>";

                $html .= '<p class="">' . $allotment['alpha'] . '</p>';
            }

            $html .= '</div>';

            $html .= '<div style="float:right;width:40%;padding:0 10px;border: 0px solid green;">';
            $html .= '<div>Please click the map for a larger interactive version</div>';
            $html .= '<a target="_blank" href="http://maps.google.com/maps?q=';
            $html .= $allotment['lat'];
            $html .= ',';
            $html .= $allotment['lng'];
            $html .= '+(My+Point)&amp;t=k&amp;z=16=';
            $html .= $allotment['lat'];
            $html .= ',';
            $html .= $allotment['lng'];
            $html .= '">';
            $html .= '<img src="http://maps.google.com/maps/api/staticmap?center=';
            $html .= $allotment['lat'];
            $html .= ',';
            $html .= $allotment['lng'];
            $html .= '&amp;zoom=16&amp;size=400x300&amp;sensor=false&amp;maptype=satellite" style="width: 100%; height: 400px;" target="_parent" "=""></a>';
            $html .= '</div>';
        }

        $html .= '<div class="clear"></div>';

        return $html;
    }

    /**
     * Builds and returns the allotment update table
     *
     * @param $allotment
     * @return string
     */
    public static function getAllotmentUpdateTable($allotment)
    {
        $society = '';
        if ($allotment['society-id']) {
            $society = SocietyAdministrator::getSociety($allotment['society-id']);
        }

        $html = '';

        $html .= '<table border="0" cellpadding="1" cellspacing="1" style="height:90px;width:600px;margin:30px 0;" border="1">';
        $html .= "<tbody>";

        $html .= '<tr><td style="height:17px; width:160px">Site name</td><td style="width:360px">' . PageUtils::text('site-name', $allotment['site-name']) . '</td></tr>';
        $html .= '<tr><td>Managed by</td><td>' . PageUtils::societyListbox($allotment['society-id']) .
        '</td></tr>';
        $html .= '<tr><td>Address</td><td>' . PageUtils::text('site-map', $allotment['site-map']) . '</td></tr>';
        $html .= '<tr><td>Post code</td><td>' . PageUtils::text('site-location', $allotment['site-location']) . '</td></tr>';
        $html .= '<tr><td>Website</td><td>' . PageUtils::text('website', $allotment['website']) . '</td></tr>';
        $html .= '<tr><td>Telephone</td><td>' . PageUtils::text('telephone', $allotment['telephone']) . '</td></tr>';

        if ($society) {
            // NB Secretary details come from the Society table
            $html .= '<tr><td colspan="2" nowrap style="background-color: #c4c4c4">Secretary details can only be edited on the society page: ' . $society['society-name'] . '</td></tr>';
            $html .= '<tr><td>Secretary</td><td>' . $society['secretary'] . '</td></tr>';
            $html .= '<tr><td>Secretary email</td><td>' . PageUtils::makeClickable($society['secretary-email']) . '</td></tr>';
            $html .= '<tr><td style="border-bottom: solid #c4c4c4 1px;">Secretary telephone</td><td
            style="border-bottom: solid #c4c4c4 1px;">' . $society['secretary-telephone'] . '</td></tr>';

//            $html .= '<tr><td>Secretary</td><td>' . PageUtils::text('secretary-name', $allotment['secretary-name']) . '</td></tr>';
//            $html .= '<tr><td>Secretary email</td><td>' . PageUtils::text('secretary-email', $allotment['secretary-email']) . '</td></tr>';
//            $html .= '<tr><td>Secretary telephone</td><td>' . PageUtils::text('secretary-tel', $allotment['secretary-tel']) . '</td></tr>';
        }

        $html .= '<tr><td>Lettings officer</td><td>' . PageUtils::text('lettings-name', $allotment['lettings-name']) . '</td></tr>';
        $html .= '<tr><td>Lettings email</td><td>' . PageUtils::text('letting-email', $allotment['letting-email']) . '</td></tr>';
        $html .= '<tr><td>Lettings telephone</td><td>' . PageUtils::text('lettings-tel', $allotment['lettings-tel']) . '</td></tr>';
        $html .= '<tr><td>Plots available</td><td>' . PageUtils::yesNo('plots-available', $allotment['plots-available']) . '</td></tr>';
        $html .= '<tr><td>Approx nbr on waiting list</td><td>' . PageUtils::text('waiting-list', $allotment['waiting-list']) . '</td></tr>';

        $html .= "</tbody>";
        $html .= "</table>";

        $html .= "<h3>Facilities available</h3>";

        $html .= '<table border="0" cellpadding="1" cellspacing="1" style="height:90px;width:600px;margin:30px 0;">';
        $html .= "<tbody>";

        $html .= '<tr><td style="height:17px;width:300px;">Water</td><td>' . PageUtils::yesNo('water', $allotment['water']) . '</td>';
        $html .= '<td style="height:17px;width:300px;">Trading hut</td><td>' . PageUtils::yesNo('trading-hut', $allotment['trading-hut']) . '</td></tr>';

        $html .= '<tr><td>Electricity</td><td>' . PageUtils::yesNo('electricity', $allotment['electricity']) . '</td>';
        $html .= '<td>Mains gas</td><td>' . PageUtils::yesNo('mains-gas', $allotment['mains-gas']) . '</td></tr>';

        $html .= '<tr><td>Toilet</td><td>' . PageUtils::yesNo('toilet', $allotment['toilet'], 'onChangeToilet();') . '</td>';
        $html .= '<td>Sewerage</td><td>' . PageUtils::yesNo('sewerage', $allotment['sewerage'], '', true) . '</td></tr>';

        $html .= '<tr><td style="height:17px; width:300px" colspan="1">Toilet type </td><td colspan="3">' . PageUtils::text('toilet-type', $allotment['toilet-type']) . '</td></tr>';

        $html .= '<tr><td>Disabled access</td><td>' . PageUtils::yesNo('disabled-access', $allotment['disabled-access']) . '</td>';
        $html .= '<td>Communal area</td><td>' . PageUtils::yesNo('communal-area', $allotment['communal-area']) . '</td></tr>';

        $html .= '<tr><td>On-site parking</td><td>' . PageUtils::yesNo('on-site_parking', $allotment['on-site_parking']) . '</td>';
        $html .= '<td>Social space</td><td>' . PageUtils::yesNo('social-space', $allotment['social-space']) . '</td></tr>';

        $html .= "</tbody>";
        $html .= "</table>";

        $html .= '<table border="0" cellpadding="1" cellspacing="1" style="height:90px;width:600px;margin:30px 0;">';
        $html .= "<tbody>";

        $html .= '<tr><td style="height:17px;" colspan="2">About site</td><td>' . PageUtils::textarea('alpha', $allotment['alpha']) . '</td></tr>';

        $html .= "</tbody>";
        $html .= "</table>";

        return $html;
    }
}
