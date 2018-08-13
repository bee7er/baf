<?php

/**
 * Base class for application emails
 * User: brianetheridge
 * Date: 01/10/2017
 * Time: 09:54
 */
abstract class ApplicantEmailAbstract
{
    protected $templatePostName;
    protected $postData;
    protected $patterns;
    protected $replacements;
    protected $hasVacancies = false;
    protected $applicantEmail;

    /**
     * Prepares the object for the merging of patterns, replacement data and the
     * resulting merged content
     *
     * ApplicantEmailAbstract constructor.
     * @param $templatePostName
     * @param array $postData
     */
    public function __construct($templatePostName, array $postData)
    {
        $this->templatePostName = $templatePostName;
        $this->postData = $postData;
    }

    /**
     * Initialize patters and replacements
     */
    protected function init()
    {
        $this->patterns = [
            '/!!BAF_SECRETARY!!/',
            '/!!TITLE!!/',
            '/!!FIRST_NAME!!/',
            '/!!SURNAME!!/',
            '/!!ADDRESS!!/',
            '/!!BOROUGH!!/',
            '/!!HOME_PHONE!!/',
            '/!!WORK_PHONE!!/',
            '/!!MOBILE!!/',
            '/!!EMAIL!!/',
            '/!!PREFERRED_ALLOTMENT!!/',
            '/!!FIRST_ALTERNATE_ALLOTMENT!!/',
            '/!!SECOND_ALTERNATE_ALLOTMENT!!/',
            '/!!PREVIOUS_EXPERIENCE!!/',
            '/!!REASONS!!/',
            '/!!HOW_HEAR!!/',
            '/!!OTHER_DETAILS!!/',
            '/!!DATE!!/',
        ];

        $this->replacements = [
            '/!!BAF_SECRETARY!!/' => DbUtils::getOptionValue('!!BAF_SECRETARY!!'),
            '/!!TITLE!!/' => (isset($this->postData['title']) ? $this->postData['title']: ''),
            '/!!FIRST_NAME!!/' => (isset($this->postData['first_name']) ? $this->postData['first_name']: ''),
            '/!!SURNAME!!/' => (isset($this->postData['surname']) ? $this->postData['surname']: ''),
            '/!!ADDRESS!!/' => (isset($this->postData['address']) ? $this->postData['address']: ''),
            '/!!BOROUGH!!/' => (isset($this->postData['borough']) ? $this->postData['borough']: ''),
            '/!!HOME_PHONE!!/' => (isset($this->postData['home_phone']) ? $this->postData['home_phone']: ''),
            '/!!WORK_PHONE!!/' => (isset($this->postData['work_phone']) ? $this->postData['work_phone']: ''),
            '/!!MOBILE!!/' => (isset($this->postData['mobile']) ? $this->postData['mobile']: ''),
            '/!!EMAIL!!/' => (isset($this->postData['email']) ? $this->postData['email']: ''),
            '/!!PREFERRED_ALLOTMENT!!/' => (isset($this->postData['preferred_allotment']) ?
        $this->postData['preferred_allotment']: ''),
            '/!!FIRST_ALTERNATE_ALLOTMENT!!/' => (isset($this->postData['first_alternate_allotment']) ? $this->postData['first_alternate_allotment']: ''),
            '/!!SECOND_ALTERNATE_ALLOTMENT!!/' => (isset($this->postData['second_alternate_allotment']) ? $this->postData['second_alternate_allotment']: ''),
            '/!!PREVIOUS_EXPERIENCE!!/' => (isset($this->postData['previous_experience']) ?
        $this->postData['previous_experience']: ''),
            '/!!REASONS!!/' => (isset($this->postData['reasons']) ? $this->postData['reasons']: ''),
            '/!!HOW_HEAR!!/' => (isset($this->postData['how_hear']) ? $this->postData['how_hear']: ''),
            '/!!OTHER_DETAILS!!/' => (isset($this->postData['other_details']) ? $this->postData['other_details']: ''),
            '/!!DATE!!/' => date('jS F Y'),
        ];

        $this->applicantEmail = $this->postData['email'];
    }

    /**
     * Merge data with a template content
     *
     * @return mixed|string
     */
    protected function getMergedContent()
    {
        global $wpdb;

        $content = "Sorry, something went wrong retrieving email template '$this->templatePostName'";
        // Get the template
        $post = $wpdb->get_results(
            $wpdb->prepare(
                "select * from `wp_posts`
                  where post_name='%s'
                  order by `ID` desc limit 1", [$this->templatePostName]
            ),
            ARRAY_A);

        if ($post) {
            $post = $post[0];

            $content = $post['post_content'];

            $content = preg_replace($this->patterns, $this->replacements, $content);
        }

        return $content;
    }

    /**
     * Get preferred allotment array
     *
     * @return array
     */
    protected function getPreferredAllotmentArray()
    {
        $preferredAllotments = [];
        if (isset($this->postData['preferred_allotment']) && $this->postData['preferred_allotment']) {
            $preferredAllotments[$this->postData['preferred_allotment']] = $this->postData['preferred_allotment'];
        }
        if (isset($this->postData['first_alternate_allotment']) && $this->postData['first_alternate_allotment']) {
            $preferredAllotments[$this->postData['first_alternate_allotment']] = $this->postData['first_alternate_allotment'];
        }
        if (isset($this->postData['second_alternate_allotment']) && $this->postData['second_alternate_allotment']) {
            $preferredAllotments[$this->postData['second_alternate_allotment']] = $this->postData['second_alternate_allotment'];
        }

        return $preferredAllotments;
    }

    /**
     * Get preferred allotment table
     *
     * @return array
     */
    protected function getPreferredAllotmentTable()
    {
        global $wpdb;

        $content = "Sorry, something went wrong retrieving preferred allotment table";
        // Get the preferred allotment table
        $preferredAllotments = $this->getPreferredAllotmentArray();
        if ($preferredAllotments) {
            $content = '<table cellpadding="3" cellspacing="0" style="border-collapse: collapse;border: 1px solid black">';
            foreach ($preferredAllotments as $preferredAllotment) {
                $allotment = $wpdb->get_results(
                    $wpdb->prepare(
                        "select * from `site_list`
                          where `site-name`='%s'", [$preferredAllotment]
                    ), ARRAY_A);
                if ($allotment) {
                    $allotment = $allotment[0];
                    $content .= '<tr style="border: 1px solid black">';
                    $content .= "<td style=\"border: 1px solid black\">{$allotment['site-name']}<td>";
                    $content .= "<td style=\"border: 1px solid black\">{$allotment['site-location']}<td>";
                    $content .= "<td style=\"border: 1px solid black\">{$allotment['lettings-name']}<td>";
                    $content .= "<td style=\"border: 1px solid black\">Lettings officer<td>";
                    $content .= "<td style=\"border: 1px solid black\">{$allotment['lettings-tel']}<td>";
                    $content .= "<td style=\"border: 1px solid black\">{$allotment['letting-email']}<td>";
                    $content .= '</tr>';
                }
            }
            $content .= '</table>';
        }

        return $content;
    }

    /**
     * Get vacancy allotment table
     *
     * @return array
     */
    protected function getVacancyAllotmentTable()
    {
        global $wpdb;

        // Get the vacancy allotment table, ignoring the preferred allotments
        $preferredAllotments = $this->getPreferredAllotmentArray();
        $ignorePreferreds = '';
        if ($preferredAllotments) {
            $ignorePreferreds = " AND (`site-name` NOT IN (";
            $sep = '';
            foreach ($preferredAllotments as $preferredAllotment) {
                $ignorePreferreds .= ("$sep'%s'");
                $sep = ',';
            }
            $ignorePreferreds .= "))";
        }

        $sql = $wpdb->prepare("SELECT * FROM `site_list`
              WHERE `plots-available`='yes' or `plots-available`>0 $ignorePreferreds", $preferredAllotments);

        $allotments = $wpdb->get_results($sql, ARRAY_A);

        $content = '<table cellpadding="3" cellspacing="0" style="border-collapse: collapse;border: 1px solid black">';
        if ($allotments) {

            $this->hasVacancies = true;

            foreach ($allotments as $vacancyAllotment) {
                $content .= '<tr style="border: 1px solid black">';
                $content .= "<td style=\"border: 1px solid black\">{$vacancyAllotment['site-name']}<td>";
                $content .= "<td style=\"border: 1px solid black\">{$vacancyAllotment['site-location']}<td>";
                $content .= "<td style=\"border: 1px solid black\">{$vacancyAllotment['lettings-name']}<td>";
                $content .= "<td style=\"border: 1px solid black\">Lettings officer<td>";
                $content .= "<td style=\"border: 1px solid black\">{$vacancyAllotment['lettings-tel']}<td>";
                $content .= "<td style=\"border: 1px solid black\">{$vacancyAllotment['letting-email']}<td>";
                $content .= '</tr>';
            }
        } else {
            $content .= '<td><td>There are no allotments with vacancies at this time</td></tr>';
        }
        $content .= '</table>';

        return $content;
    }

    /**
     * Get standard email headers
     * NB Duplicate of the EmailUtils function getHtmlHeaders
     *
     * @return array
     */
    function getHeaders($cc = null)
    {
        /* https://developer.wordpress.org/reference/functions/wp_mail/
         * Other examples:
         * $headers[] = 'From: Me Myself <me@example.net>';
         * $headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
         * $headers[] = 'Cc: iluvwp@wordpress.org';
         * $headers[] = 'Bcc: oiuoiu@wordpress.org';
         */
        $headers = ['Content-Type: text/html','charset=UTF-8'];

        if ($cc) {
            if (!is_array($cc)) {
                $cc = explode(',', $cc);
            }

            foreach ($cc as $ccEmail) {
                $headers[] = sprintf("Bcc: %s", $ccEmail);
            }
        }

        return $headers;
    }
}
