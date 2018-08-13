<?php

/**
 * Handles the construction and sending of the applicant email
 * User: brianetheridge
 * Date: 01/10/2017
 * Time: 09:54
 */
class ApplicantEmailManager extends ApplicantEmailAbstract
{
    /**
     * Extra init bits
     */
    protected function init()
    {
        parent::init();

        $additionalPatterns = [
            '/!!PREFERRED_ALLOTMENT_DETAILS!!/',
            '/!!VACANCY_ALLOTMENT_DETAILS!!/',
        ];
        $this->patterns = array_merge($this->patterns, $additionalPatterns);

        $additionalReplacements = [
            '/!!PREFERRED_ALLOTMENT_DETAILS!!/' => ($this->getPreferredAllotmentTable()),
            '/!!VACANCY_ALLOTMENT_DETAILS!!/' => ($this->getVacancyAllotmentTable()),
        ];
        $this->replacements = array_merge($this->replacements, $additionalReplacements);
    }

    /**
     * Build and return applicant email
     *
     * @param $systemEmailTo
     * @return array
     */
    public function buildApplicantEmail()
    {
        $this->init();

        if (ENV == 'dev') {
            $toEmail = SYSTEM_EMAIL_ADDRESSES;
        } else {
            $toEmail = $this->applicantEmail;
        }

        $content = $this->getMergedContent();
        $emailSubject = ('With reference to your application to take over an allotment');

        return array(array('toEmail'=>$toEmail, 'subject'=>$emailSubject, 'content'=>$content,
            'headers'=>ApplicantEmailAbstract::getHeaders(BCC_EMAIL_ADDRESSES)));
    }
}
