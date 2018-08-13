<?php

/**
 * Merges data with a template and sends it ot the BAF Secretary
 * User: brianetheridge
 * Date: 01/10/2017
 * Time: 09:54
 */
class BafSecretaryEmailManager extends ApplicantEmailAbstract
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
    public function buildBafSecretaryEmail()
    {
        $this->init();

        $toEmail = SYSTEM_EMAIL_ADDRESSES;
        $content = $this->getMergedContent();
        $emailSubject = ('BAF Secretary notification of allotment application');

        return array(array('toEmail'=>$toEmail, 'subject'=>$emailSubject, 'content'=>$content,
            'headers'=>ApplicantEmailAbstract::getHeaders()));
    }
}
