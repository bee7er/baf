<?php

include_once realpath(__DIR__ . "/../config/config.php");
include_once realpath(__DIR__ . "/../associationManagement/Administrator.php");
include_once realpath(__DIR__ . "/../associationManagement/SiteAdministrator.php");
include_once realpath(__DIR__ . "/ApplicantEmailAbstract.php");
include_once realpath(__DIR__ . "/ApplicantEmailManager.php");
include_once realpath(__DIR__ . "/LettingsOfficerEmailManager.php");
include_once realpath(__DIR__ . "/BafSecretaryEmailManager.php");
include_once realpath(__DIR__ . "/../utils/PageUtils.php");
include_once realpath(__DIR__ . "/../utils/dbUtils.php");
include_once realpath(__DIR__ . "/../utils/EmailUtils.php");

/**
 * Created by PhpStorm.
 * User: brianetheridge
 * Date: 04/10/2017
 * Time: 21:40
 */
class SiteApplication
{
    /**

    CREATE TABLE IF NOT EXISTS `site_application` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(32) NOT NULL,
    `first-name` varchar(64) DEFAULT NULL,
    `surname` varchar(64) DEFAULT NULL,
    `address` varchar(255) DEFAULT NULL,
    `borough` varchar(64) DEFAULT NULL,
    `home-phone` varchar(64) DEFAULT NULL,
    `work-phone` varchar(64) DEFAULT NULL,
    `mobile` varchar(64) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `preferred-allotment` varchar(64) DEFAULT NULL,
    `first-alternate-allotment` varchar(64) DEFAULT NULL,
    `second-alternate-allotment` varchar(64) DEFAULT NULL,
    `previous-experience` text DEFAULT NULL,
    `reasons` text DEFAULT NULL,
    `how-hear` varchar(64) DEFAULT NULL,
    `other-details` text DEFAULT NULL,
    `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

     */
    public function insert(array $data)
    {
        global $wpdb;

        // To add a new column:
        //    add column name
        //    add column data type in the right place
        //    add data in the right place
        $sql = "INSERT INTO `site_application`
              (`title`,`first-name`,`surname`,`address`,`borough`,`home-phone`,`work-phone`,
              `mobile`,`email`,`preferred-allotment`,`first-alternate-allotment`,`second-alternate-allotment`,
              `previous-experience`,`reasons`,`how-hear`,`other-details`,`created`)
              VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')";
        $sql = $wpdb->prepare($sql,
            [
                $data['title'],
                $data['first_name'],
                $data['surname'],
                $data['address'],
                $data['borough'],
                $data['home_phone'],
                $data['work_phone'],
                $data['mobile'],
                $data['email'],
                $data['preferred_allotment'],
                $data['first_alternate_allotment'],
                $data['second_alternate_allotment'],
                $data['previous_experience'],
                $data['reasons'],
                $data['how_hear'],
                $data['other_details'],
                date('Y-m-d H:i:s')
            ]);
        $wpdb->query($sql);
    }

    /**
     * Validate the application form data
     *
     * @param $data
     * @return array
     */
    private static function getValidationErrors($data) {
        $requiredFields = ['first_name','surname','address','home_phone','email'];
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
     * Build and return the application form table
     *
     * @return string
     */
    public static function updateApplicationFormTable($postData)
    {
        $emailSent = false;
        // Validate form
        $errors = self::getValidationErrors($postData);
        if (!$errors) {

            // Save the application details
            $siteApplication = new SiteApplication();
            $siteApplication->insert($postData);

            // Build and send email to the applicant
            $sendEmails = array();
            $applicantEmailManager = new ApplicantEmailManager(APPLICANT_EMAIL_POST_ID, $postData);
            if (($emails = $applicantEmailManager->buildApplicantEmail())) {
                // Accumulate emails, which we send at the end
                $sendEmails = array_merge($sendEmails, $emails);
                // Email the lettings officers
                $lettingsOfficerEmailManager = new LettingsOfficerEmailManager(LETTINGS_OFFICER_EMAIL_POST_ID, $postData);
                if (($emails = $lettingsOfficerEmailManager->buildLettingsOfficerEmails())) {
                    $sendEmails = array_merge($sendEmails, $emails);
                    // Email the BAF secretary
                    $bafSecretaryEmailManager = new BafSecretaryEmailManager(BAF_SECRETARY_EMAIL_POST_ID, $postData);
                    if (($emails = $bafSecretaryEmailManager->buildBafSecretaryEmail())) {
                        $sendEmails = array_merge($sendEmails, $emails);
                        $error = false;
                        foreach ($sendEmails as $sendEmail) {
                            if (!sendEmail($sendEmail['toEmail'], $sendEmail['subject'], $sendEmail['content'], $sendEmail['headers'])) {
                                $error = true;
                            }
                        }

                        if ($error) {
                            $errors[] = 'An error was detected trying to email your details. Please contact support.';
                        } else {
                            // Tell ourselves that we have successfully sent the emails to the
                            // applicant, lettings officers and BAF secretary
                            $_SESSION[SESSION_EMAIL_SURNAME] = $postData['surname'];
                            $_SESSION[SESSION_EMAIL_FIRSTNAME] = $postData['first_name'];
                            $emailSent = true;
                        }
                    }
                }
            }
        }

        return [$emailSent, $errors];
    }

    /**
     * Build and return the application form table
     *
     * @return string
     */
    public static function getApplicationFormTable($postData, $errors)
    {
        $html = '';
        $allotments = SiteAdministrator::getAllotmentList();
        if (!is_array($allotments)) {
            $allotments = [];
        }

        $html .= '<div style="width:100%;border:0px solid green;">';
        $html .= '<p style="text-align:right;font-size:11px;color:#a4a4a4;">[Details sent to: ' . SYSTEM_EMAIL_ADDRESSES . "]</p>";
        $html .= '* = A required field';
        $html .= '<form role="form" class="form-group" method="POST" action="">';

        if ($errors) {
            $html .= ('<div class="error-container">');
            foreach ($errors as $error) {
                $html .= ('<span class="error">' . $error . '</span><br>');
            }
            $html .= ('</div>');
        }

        $html .= '<table style="table-layout:fixed;width:100%;border:0px solid red;">';
        $html .= '<tbody>';
        $html .= '<tr><td>Title</td><td>' . PageUtils::getTitleSelect($postData['title']) . '</td></tr>';
        $html .= '<tr><td>First name*</td><td>' . PageUtils::text('first_name', $postData['first_name']) . '</td></tr>';
        $html .= '<tr><td>Surname*</td><td>' . PageUtils::text('surname', $postData['surname']) . '</td></tr>';
        $html .= '<tr><td>Address*</td><td>' . PageUtils::textarea('address', $postData['address']) . '</td></tr>';
        $html .= '<tr><td>London borough</td><td>' . PageUtils::text('borough', $postData['borough']) . '</td></tr>';
        $html .= '<tr><td>Home telephone number*</td><td>' . PageUtils::number('home_phone', $postData['home_phone']) . '</td></tr>';
        $html .= '<tr><td>Work number</td><td>' . PageUtils::number('work_phone', $postData['work_phone']) . '</td></tr>';
        $html .= '<tr><td>Mobile number</td><td>' . PageUtils::number('mobile', $postData['mobile']) . '</td></tr>';
        $html .= '<tr><td>Email address</td><td>' . PageUtils::text('email', $postData['email']) . '</td></tr>';
        $html .= '<tr><td colspan="2"><h4><strong>You can apply for a plot on up to 3 sites in Barnet.</strong></h4><h4>Please indicate your choices below:</h4></td></tr>';

        $html .= '<tr><td>Preferred allotment (please select one)</td><td>' . PageUtils::getAllotmentSelect($allotments, 'preferred_allotment', $postData['preferred_allotment']) . '</td></tr>';
        $html .= '<tr><td>Alternative 1 (please select one)</td><td>' . PageUtils::getAllotmentSelect($allotments, 'first_alternate_allotment', $postData['first_alternate_allotment']) . '</td></tr>';
        $html .= '<tr><td>Alternative 2 (please select one)</td><td>' . PageUtils::getAllotmentSelect($allotments, 'second_alternate_allotment', $postData['second_alternate_allotment']) . '</td></tr>';

        $html .= '<tr><td>Do you have any previous experience of gardening or allotments? If so please provide some detail:</td>';
        $html .= '    <td>' . PageUtils::textarea('previous_experience', $postData['previous_experience']) . '</td></tr>';

        $html .= '<tr><td>Please outline your reasons for wanting an allotment:</td>';
        $html .= '    <td>' . PageUtils::textarea('reasons', $postData['reasons']) . '</td></tr>';

        $html .= '<tr><td>How did you hear about the Barnet Allotments Federation?:</td>';
        $html .= '    <td>' . PageUtils::getTheHowHearSelect($postData['how_hear']) . '</td></tr>';

        $html .= '<tr id="otherRow"><td>Other details</td><td>' . PageUtils::text('other_details', $postData['other_details']) . '</td></tr>';

        $html .= '<tr><td colspan="2"><input class="btn btn-success" type="submit"></td></tr>';
        $html .= '</tbody></table>';

        $html .= "
            <script type='application/javascript'>
                function checkOther() {
                    jQuery('#otherRow').hide();
                    if (jQuery('#how_hear').val() == 'Other') {
                        jQuery('#otherRow').show();
                    }
                 }
                 jQuery(document).ready(function() {
                    checkOther();
                 });
             </script>
             ";

        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }
}
