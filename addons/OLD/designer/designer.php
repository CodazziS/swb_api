<?php
/*
 * Designer
 *
 * Supported TAGS :
 * %.var.% => Variable
 * %_array,layout_% => Read array in other layout
 *
 * TODO TAGS
 *
 *
 */
class Designer {
    public function put_result($class) {
        if ($class->layout_type == 'json') {
            $class->result['error'] = $class->error;
            header('Content-Type: application/json');
            echo json_encode($class->result);
        } else {
            echo $this->get_html_result($class, $class->layout);
        }
    }

    private function get_html_result($obj, $layout_name) {
        $layout_html = file_get_contents(dirname(__FILE__).'/layouts/'.$layout_name.'.html');
        $layout_html = $this->search_variables($obj, $layout_html);
        $layout_html = $this->search_arrays($obj, $layout_html);
        return $layout_html;
    }

    private function search_variables($obj, $layout_html) {
        $start_tag = null;
        $end_tag = null;

        $start_tag = strpos ($layout_html, '%.');
        $end_tag = strpos ($layout_html, '.%');


        while ($start_tag !== false && $end_tag !== false) {
            $var = substr($layout_html, $start_tag + 2, ($end_tag - $start_tag) - 2);
            if (isset(${$var})) {
                $layout_html = str_replace('%.'.$var.'.%', ${$var}, $layout_html);
            } else if (isset($obj->$var)) {
                $layout_html = str_replace('%.'.$var.'.%', $obj->$var, $layout_html);
            } else if (isset($obj[$var])) {
                $layout_html = str_replace('%.'.$var.'.%', $obj[$var], $layout_html);
            } else {
                $layout_html = str_replace('%.'.$var.'.%', strtoupper($var), $layout_html);
            }
            $start_tag = strpos ($layout_html, '%.');
            $end_tag = strpos ($layout_html, '.%');
        }
        return $layout_html;
    }

    private function search_arrays($obj, $layout_html) {
        $start_tag = null;
        $end_tag = null;

        $start_tag = strpos ($layout_html, '%_');
        $end_tag = strpos ($layout_html, '_%');

        while ($start_tag !== false && $end_tag !== false) {
            $line = substr($layout_html, $start_tag + 2, ($end_tag - $start_tag) - 2);

            $column = strpos ($line, ',');
            $var = substr($line, 0, $column);
            $layout = substr($line, $column + 1);

            if (isset($obj->$var)) {
                $results = '';
                foreach ($obj->$var as $row) {
                    $results .= $this->get_html_result($row, $layout);
                }
                $layout_html = str_replace('%_'.$line.'_%', $results, $layout_html);
            } else if (isset($obj[$var])) {
                $results = '';
                foreach ($obj[$var] as $row) {
                    $results .= $this->get_html_result($row, $layout);
                }
                $layout_html = str_replace('%_'.$line.'_%', $results, $layout_html);
            }
            $start_tag = strpos ($layout_html, '%_');
            $end_tag = strpos ($layout_html, '_%');
        }
        return $layout_html;
    }
}