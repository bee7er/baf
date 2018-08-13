<?php

include_once 'dbUtils.php';

class PageUtils
{
    /**
     * Display errors
     *
     * @param $errors
     * @return string
     */
    public static function handleDisplayErrors($errors)
    {
        $html = '';
        if ($errors) {
            $html .=  ('<div class="error-container">');
            foreach ($errors as $error) {
                $html .=  ('<span class="error">' . $error . '</span><br>');
            }

            $html .=  ('</div>');
        }

        return $html;
    }

    /**
     * Turns a url into a link
     *
     * @param $url
     * @return string
     */
    public static function makeClickable($url)
    {
        $href = trim($url);
        if (!$href) {
            return '';
        }
        if (strpos($href, '@') !== false) {
            // An email address
            if (strpos($href, 'mailto') === false) {
                $href = ('mailto:' . $href);
            }
            return ('<a href="' . $href . '">' . $url . '</a>');
        }
        // A website address
        if (strpos($href, 'http') === false) {
            $href = ('http://' . $href);
        }
        return ('<a href="' . $href . '" target="_blank">' . $url . '</a>');
    }

    /**
     * Format field name from column name
     *
     * @param $fieldName
     * @param bool $ucFirst
     * @return mixed
     */
    public static function formatFieldName($fieldName, $ucFirst = false)
    {
        if ($ucFirst) {
            $fieldName = ucfirst($fieldName);
        }
        $fieldName = str_replace('_', ' ', $fieldName);

        return str_replace('-', ' ', $fieldName);
    }

    /**
     * Builds and returns a listbox of personal titles
     *
     * @param $selectedTitle
     * @return string
     */
    public static function getTitleSelect($selectedTitle)
    {
        $titles = ['Mr','Ms','Mrs','Dr','Rev'];
        $html = '<select name="title" class="form-control">';
        foreach ($titles as $title) {
            $selected = '';
            if ($title == $selectedTitle) {
                $selected = ' selected';
            }
            $html .= ('<option' . $selected . '>' . $title . '</option>');
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * Return a select control of allotments
     *
     * @param $allotments
     * @param $controlName
     * @param $selectedAllotment
     * @return string
     */
    public static function getAllotmentSelect($allotments, $controlName, $selectedAllotment)
    {
        $html = '<select name="' . $controlName . '" class="form-control">';
        foreach ($allotments as $allotment) {
            $selected = '';
            if ($allotment['site-name'] == $selectedAllotment) {
                $selected = ' selected';
            }
            $html .= ('<option' . $selected . '>' . $allotment['site-name'] . '</option>');
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * Build and return the how hear select listbox
     *
     * @param $selectedHowHear
     * @return string
     */
    public static function getTheHowHearSelect($selectedHowHear)
    {
        $howHears = array(
            "Please select"=>"",
            "Know an allotment holder"=>"Know an allotment holder",
            "Search engine"=>"Search engine",
            "Website"=>"Website",
            "Word of mouth"=>"Word of mouth",
            "Other"=>"Other"
        );

        $onChange =  'onchange="if (this.value==\'Other\') { checkOther(); jQuery(\'#other_details\').focus(); }"';
        $html = '<select name="how_hear" id="how_hear" class="form-control" '.$onChange.'>';
        foreach ($howHears as $text => $value) {
            $selected = '';
            if ($value == $selectedHowHear) {
                $selected = ' selected';
            }
            $html .= ('<option' . $selected . ' value="'.$value.'">' . $text . '</option>');
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Generate an input hidden field
     *
     * @param $id
     * @param $value
     * @return string
     */
    public static function hidden($id, $value)
    {
        return ('<input type="hidden" id="' . $id . '" name="' . $id . '" value="' . $value . '">');
    }

    /**
     * Generate an output text field
     *
     * @param $value
     * @return string
     */
    public static function string($value)
    {
        return ('<span>' . $value . '</span>');
    }

    /**
     * Generate an input text field
     *
     * @param $id
     * @param $value
     * @return string
     */
    public static function text($id, $value)
    {
        return ('<input type="text" id="' . $id . '" name="' . $id
            . '" value="' . $value . '" class="form-control">');
    }

    /**
     * Generate an input number field
     *
     * @param $id
     * @param $value
     * @return string
     */
    public static function number($id, $value)
    {
        return ('<input type="number" id="' . $id . '" name="' . $id
            . '" value="' . $value . '" class="form-control" >');
    }

    /**
     * Generate a checkbox field
     *
     * @param $id
     * @return string
     */
    public static function checkbox($id, $checked = false)
    {
        $checkedStr = '';
        if ($checked) {
            $checkedStr = ' checked';
        }
        return ('<input type="checkbox" id="' . $id . '" name="' . $id . '" value="1" class="cbox" class="form-control"' . $checkedStr . '>');
    }

    /**
     * Generate a checkbox handler
     *
     * @return string
     */
    public static function checkboxHandler()
    {
        $html = '<input type="checkbox" id="cboxHandler" class="form-control" onclick="toggleCheckboxes()">';
        $html .= "
<script>
function toggleCheckboxes() {
    var isChecked = jQuery('#cboxHandler').is(':checked');
    jQuery('.cbox').each(function(i) {
        jQuery(this).attr('checked', isChecked);
    });
}
</script>";

        return $html;
    }

    /**
     * Generate an input textarea field
     *
     * @param $id
     * @param $value
     * @return string
     */
    public static function textarea($id, $value)
    {
        return ('<textarea id="' . $id . '" name="' . $id . '" class="form-control"">' .
            $value .
            '</textarea>');
    }

    /**
     * Generate a yes/no listbox
     *
     * @param $id
     * @param $value
     * @param string $onChange
     * @param bool $addNotApplicable
     * @return string
     */
    public static function yesNo($id, $value, $onChange = '', $addNotApplicable = false)
    {
        if ($onChange) {
            $onChange = ('onchange="' . $onChange . '"');
        }
        $optionValues = ['Yes', 'No'];
        if ($addNotApplicable) {
            $optionValues[] = 'N/A';
        }
        $html = '<select id="' . $id . '" name="' . $id . '" class="form-control" ' . $onChange . '>';
        foreach ($optionValues as $optionValue) {
            $selected = ($value == $optionValue ? 'selected': '');
            $html .= '<option value="' . $optionValue . '" ' . $selected . '>' . $optionValue . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * Generate a listbox of Societies
     *
     * @return string
     */
    public static function societyListbox($currentId)
    {
        $societies = DbUtils::getSocieties();
        $html = '<select id="society-id" name="society-id" class="form-control">';
        $html .= '<option value="0">None</option>';
        foreach ($societies as $society) {
            $selected = ($currentId == $society['id'] ? 'selected': '');
            $html .= '<option value="' . $society['id'] . '" ' . $selected . '>' . $society['society-name'] .
                '</option>';
        }
        $html .= '</select>';

        return $html;
    }
}
