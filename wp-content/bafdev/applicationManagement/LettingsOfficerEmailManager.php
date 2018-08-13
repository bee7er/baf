<?php

/**
 * Merges data with a template and sends emails to the lettings officers
 * User: brianetheridge
 * Date: 01/10/2017
 * Time: 09:54
 */
class LettingsOfficerEmailManager extends ApplicantEmailAbstract
{
    /**
     * Extra init bits
     */
    protected function init()
    {
        parent::init();

        $lettingsOfficersPatterns = [
            '/!!SITE_NAME!!/',
            '/!!MANAGED_BY!!/',
            '/!!SITE_LOCATION!!/',
            '/!!LETTINGS_NAME!!/',
            '/!!LETTING_EMAIL!!/',
            '/!!LETTINGS_TEL!!/',
        ];
        $this->patterns = array_merge($this->patterns, $lettingsOfficersPatterns);
    }

    /**
     * Build and return lettings officers emails
     *
     * @param $systemEmailTo
     * @return array
     */
    public function buildLettingsOfficerEmails()
    {
        global $wpdb;

        $emails = array();

        $this->init();

        $preferredAllotments = $this->getPreferredAllotmentArray();
        foreach ($preferredAllotments as $preferredAllotment) {
            if ($preferredAllotment) {

                $allotment = $wpdb->get_results(
                    $wpdb->prepare("select * from `site_list` where `site-name`='%s'",
                        [$preferredAllotment]), ARRAY_A);
                if ($allotment) {
                    $allotment = $allotment[0];

                    // Add the replacements which match the additional patterns above
                    $lettingsOfficersReplacements = [
                        '/!!SITE_NAME!!/' => $allotment['site-name'],
                        '/!!MANAGED_BY!!/' => $allotment['managed-by'],
                        '/!!SITE_LOCATION!!/' => $allotment['site-location'],
                        '/!!LETTINGS_NAME!!/' => $allotment['lettings-name'],
                        '/!!LETTING_EMAIL!!/' => $allotment['letting-email'],
                        '/!!LETTINGS_TEL!!/' => $allotment['lettings-tel'],
                    ];
                    $this->replacements = array_merge($this->replacements, $lettingsOfficersReplacements);

                    if (ENV == 'dev') {
                        $toEmail = SYSTEM_EMAIL_ADDRESSES;
                    } else {
                        $toEmail = $allotment['letting-email'];
                    }

                    $content = $this->getMergedContent();
                    $emailSubject = ('Lettings Officer notification of allotment application at ' .
                        $allotment['site-name']);

                    $emails[] = array('toEmail'=>$toEmail, 'subject'=>$emailSubject, 'content'=>$content,
                        'headers'=>ApplicantEmailAbstract::getHeaders(BCC_EMAIL_ADDRESSES));
                } else {
                    die("Could not find $preferredAllotment");
                }
            }
        }

        return $emails;
    }
}
