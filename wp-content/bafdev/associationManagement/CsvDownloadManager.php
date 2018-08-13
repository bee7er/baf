<?php

include_once realpath(__DIR__ . "/SocietyAdministrator.php");
include_once realpath(__DIR__ . "/../utils/PageUtils.php");

class CsvDownloadManager
{
    /**
     * Validate form details
     *
     * @param $data
     * @return array
     */
    public function getValidationErrors($data, $requiredFields = [])
    {
        $requiredFields = ['society-name'];

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
     * Validate form details
     *
     * @param $data
     * @return array
     */
    public function c($field)
    {
        return "IF ($field IS NULL OR $field = '', 'null.account@barnetallotments.org.uk', $field)";
    }

    /**
     * Extract and download society data
     *
     * @param $postData
     * @return array
     */
    public function extractSocietyData($postData)
    {
        $selectFieldSet = [
            'includeChair' => ['`chair`',$this->c('`chair-email`') . " as 'chair-email'"],
            'includeSecretary' => ['`secretary`',$this->c('`secretary-email`') . " as 'secretary-email'"],
            'includeTreasurer' => ['`treasurer`',$this->c('`treasurer-email`') . " as 'treasurer-email'"],
            'includeSocietyRep' => ['`society-rep`',$this->c('`society-rep-email`') . " as 'society-rep-email'"],
        ];

        $headerFieldSet = [
            'includeChair' => ['chair', "chair email"],
            'includeSecretary' => ['secretary', "secretary email"],
            'includeTreasurer' => ['treasurer', "treasurer email"],
            'includeSocietyRep' => ['society-rep', "society rep email"],
        ];

        $postedFields = array_keys($postData);

        // Build headers and select field set
        $select = '`society-name`,';
        $headers = ['society'];
        $sep = '';
        foreach ($selectFieldSet as $field => $selectFields) {
            if (in_array($field, $postedFields)) {
                $headers = array_merge($headers, $headerFieldSet[$field]);
                $select .= ($sep . implode(',', $selectFields));
                $sep = ',';
                // Remove the element from the original posted data so we can implode what's left
                unset($postedFields[array_search($field, $postedFields)]);
            }
        }

        $result['success'] = false;
        $where = implode(',', $postedFields);
        $sql = "select $select from society_list
                  where id in ($where)
                  order by `society-name` asc";

        $csvData = SocietyAdministrator::getSocietyExtract($sql);

        if ($csvData) {
            // Insert the headers row
            array_unshift($csvData, $headers);
            // Output the data to the file system
            $filePath = realpath(CSV_EXPORT_SAVE_DIR);
            if ($filePath != false) {
                date_default_timezone_set('Europe/London');

                $fileName = ('baf_society_' . date('Y-m-d_His') . '.csv');
                $fullName = ($filePath . DIRECTORY_SEPARATOR . $fileName);
                $fp = fopen($fullName, 'w');
                foreach ($csvData as $line) {
                    fputcsv($fp, $line);
                }

                fclose($fp);

                $result['success'] = true;
                $result['fileName'] = $fileName;
                $result['url'] = (CSV_EXPORT_URL_DIR . $fileName);
                $result['message'] = 'Society data extracted successfully';
            } else {
                $result['message'] = 'Could not find CSV output folder';
            }
        } else {
            $result['message'] = 'There was no data to match the selection criteria<br>Please select at least one society';
        }

        return $result;
    }

    /**
     * Get the society list from db
     *
     * @param $postData
     * @return string
     */
    public static function getCsvFilterTable($postData)
    {
        $default = true;
        if (isset($postData['reload'])) {
            // On reload, if field is not present it is because the user unchecked the box
            $default = false;
        }

        $includeChair = ($postData['includeChair'] ? : $default);
        $includeSecretary = ($postData['includeSecretary'] ? : $default);
        $includeTreasurer = ($postData['includeTreasurer'] ? : $default);
        $includeSocietyRep = ($postData['includeSocietyRep'] ? : $default);

        $html = '';

        $html .= '<p style="margin:5px;font-size:14px;border:0px solid red;">';
        $html .= 'Enter selection criteria and choose some or all societies to include in the extract.';
        $html .= '</p>';

        $html .= '<table border="0" cellpadding="1" cellspacing="1" style="width:100%;">';
        $html .= '<thead></thead>';
        $html .= '<tbody>';
        $html .= '<tr style="background-color: #d4d4d4;">';
        $html .= '<td>Include chair:<br>Include secretary:</td>';
        $html .= '<td>' . PageUtils::checkbox('includeChair', $includeChair)
            . '<br>' . PageUtils::checkbox('includeSecretary', $includeSecretary)
            . '</td>';
        $html .= '<td>Include treasurer:<br>Include society rep:</td>';
        $html .= '<td>' . PageUtils::checkbox('includeTreasurer', $includeTreasurer)
            . '<br>' . PageUtils::checkbox('includeSocietyRep', $includeSocietyRep)
            . '</td>';
        $html .= '</tr>';
        $html .= '</tbody>';
        $html .= '</table>';

        $html .= '<div style="width:100%;height:400px;overflow:scroll;border:1px solid #c4c4c4;margin:0 0 30px 0;">';
        $html .= '<table border="0" cellpadding="1" cellspacing="1" style="width:100%;border:0px solid #c4c4c4;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #d4d4d4">';
        $html .= '<th style="height:17px; width:5%;text-align: center;">';
        $html .= '<strong>Select</strong><br>' . PageUtils::checkboxHandler();
        $html .= '</th>';
        $html .= '<th style="height:17px; width:25%"><strong>Society name</strong></th>';
        $html .= '<th style="height:17px; width:25%"><strong>Status</strong></th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $societies = SocietyAdministrator::getSocietyList();

        if (count($societies) <= 0) {

            $html .= '<tr><td colspan="3" style="height:17px;">No societies found</td></tr>';

        } else {

            foreach ($societies as $society) {
                $html .= '<tr>';
                $html .= ('<td style="text-align: center;">' . PageUtils::checkbox($society['id']) . '</td>');
                $html .= ('<td colspan="2">' . $society['society-name'] . '</td>');
                $html .= '</tr>';
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}
