<?php

/**
 * Send an email taking into account test mode
 *
 * @param $emails
 * @param $emailSubject
 * @param $message
 * @param $headers
 * @return bool
 */
function sendEmail($emails, $emailSubject, $message, $headers)
{
    if (!function_exists( 'wp_mail')) {
        die('Could not find wp_mail funciton.  Please notify administrators.');
    }

    return wp_mail($emails, $emailSubject, $message, $headers);
}

function hasSentEmailToUser($postData) {
    $emailSent = false;
    $formResubmitted = false;
    if (ENV != 'dev'
        && isset($_SESSION[SESSION_EMAIL_SURNAME])
        && $_SESSION[SESSION_EMAIL_SURNAME]
        && $_SESSION[SESSION_EMAIL_SURNAME] == $postData['surname']
        && isset($_SESSION[SESSION_EMAIL_FIRSTNAME])
        && $_SESSION[SESSION_EMAIL_FIRSTNAME]
        && $_SESSION[SESSION_EMAIL_FIRSTNAME] == $postData['first_name']) {
        // We have already presented the form and sent the email for this person, using first name/surname
        $emailSent = true;
        $formResubmitted = true;
    }

    return [$emailSent, $formResubmitted];
}

/**
 * Examines before and after details and reports on it
 *
 * @param $entityName
 * @param $subjectName
 * @param $before
 * @param $changedFields
 * @return string
 */
function getUpdateEmailContent($entityName, $subjectName, $before, $changedFields)
{
    $content = ("$entityName details updated for: $subjectName\n\n<br><br>");

    $content .= ("Here are the original details:\n\n<br><br>");
    foreach ($before as $field => $value) {
        if ($value) {
            $content .= (PageUtils::formatFieldName($field, true) . ': ' . $value . "\n");
        }
    }
    $content .= ("\n\n<br><br>");
    if ($changedFields) {
        $content .= ("This is what changed:\n\n<br><br>");
        foreach ($changedFields as $field => $value) {
            if ($value) {
                $content .= (PageUtils::formatFieldName($field, true) . ': ' . $value . "\n");
            }
        }
    } else {
        $content .= ("No changes were detected.\n\n<br><br>");
    }

    return $content;
}

/**
 * Get standard email headers
 *
 * @return array
 */
function getHtmlHeaders($cc = null)
{
    /* https://developer.wordpress.org/reference/functions/wp_mail/
     * Other examples:
     * $headers[] = 'From: Me Myself <me@example.net>';
     * $headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
     * $headers[] = 'Cc: iluvwp@wordpress.org';
     */
    $headers = ['Content-Type: text/html','charset=UTF-8'];

    if ($cc) {
        if (!is_array($cc)) {
            $cc = explode(',', $cc);
        }

        foreach ($cc as $ccEmail) {
            // Using Bcc
            $headers[] = sprintf("Bcc: %s", $ccEmail);
        }
    }

    return $headers;
}
