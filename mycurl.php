<?php
/**
 * Instructions & Comments:
    $tbl = new tableExtractor;
 *  $tbl->source = $data; // Set the HTML Document
 *  $tbl->anchor = ''; // Set an anchor that is unique and occurs before the Table
 * $tpl->anchorWithin = true; // To use a unique anchor within the table to be retrieved
 * $d = $tbl->extractTable(); // The array

 */
    /*----------------------------------------------------------------------
        Table Extractor
        ===============
        Table extractor is a php class that can extract almost any table
        from any html document/page, and then convert that html table into
        a php array.

        Version 1.3
        Compatibility: PHP 4.4.1 +
        Copyright Jack Sleight - www.reallyshiny.com
        This script is licensed under the Creative Commons License.
    ----------------------------------------------------------------------*/

    class tableExtractor {

        var $source            = NULL;
        var $anchor            = NULL;
        var $anchorWithin    = false;
        var $headerRow        = true;
        var $startRow        = 0;
        var $maxRows        = 0;
        var $startCol        = 0;
        var $maxCols        = 0;
        var $stripTags        = false;
        var $extraCols        = array();
        var $rowCount        = 0;
        var $dropRows        = NULL;

        var $cleanHTML        = NULL;
        var $rawArray        = NULL;
        var $finalArray        = NULL;

        function extractTable() {

            $this->cleanHTML();
            $this->prepareArray();

            return $this->createArray();

        }


        function cleanHTML() {

            // php 4 compatibility functions
            if(!function_exists('stripos')) {
                function stripos($haystack,$needle,$offset = 0) {
                   return(strpos(strtolower($haystack),strtolower($needle),$offset));
                }
            }

            // find unique string that appears before the table you want to extract
            if ($this->anchorWithin) {
                /*------------------------------------------------------------
                    With thanks to Khary Sharp for suggesting and writing
                    the anchor within functionality.
                ------------------------------------------------------------*/
                $anchorPos = stripos($this->source, $this->anchor) + strlen($this->anchor);
                $sourceSnippet = strrev(substr($this->source, 0, $anchorPos));
                $tablePos = stripos($sourceSnippet, strrev(("<table"))) + 6;
                $startSearch = strlen($sourceSnippet) - $tablePos;
            }
            else {
                $startSearch = stripos($this->source, $this->anchor);
            }

            // extract table
            $startTable = stripos($this->source, '<table', $startSearch);
            $endTable = stripos($this->source, '</table>', $startTable) + 8;
            $table = substr($this->source, $startTable, $endTable - $startTable);

            if(!function_exists('lcase_tags')) {
                function lcase_tags($input) {
                    return strtolower($input[0]);
                }
            }

            // lowercase all table related tags
            $table = preg_replace_callback('/<(\/?)(table|tr|th|td)/is', 'lcase_tags', $table);

            // remove all thead and tbody tags
            $table = preg_replace('/<\/?(thead|tbody).*?>/is', '', $table);

            // replace th tags with td tags
            $table = preg_replace('/<(\/?)th(.*?)>/is', '<$1td$2>', $table);

            // clean string
            $table = trim($table);
            $table = str_replace("\r\n", "", $table);

            $this->cleanHTML = $table;

        }

        function prepareArray() {

            // split table into individual elements
            $pattern = '/(<\/?(?:tr|td).*?>)/is';
            $table = preg_split($pattern, $this->cleanHTML, -1, PREG_SPLIT_DELIM_CAPTURE);

            // define array for new table
            $tableCleaned = array();

            // define variables for looping through table
            $rowCount = 0;
            $colCount = 1;
            $trOpen = false;
            $tdOpen = false;

            // loop through table
            foreach($table as $item) {

                // trim item
                $item = str_replace(' ', '', $item);
                $item = trim($item);

                // save the item
                $itemUnedited = $item;

                // clean if tag
                $item = preg_replace('/<(\/?)(table|tr|td).*?>/is', '<$1$2>', $item);

                // pick item type
                switch ($item) {


                    case '<tr>':
                        // start a new row
                        $rowCount++;
                        $colCount = 1;
                        $trOpen = true;
                        break;

                    case '<td>':
                        // save the td tag for later use
                        $tdTag = $itemUnedited;
                        $tdOpen = true;
                        break;

                    case '</td>':
                        $tdOpen = false;
                        break;

                    case '</tr>':
                        $trOpen = false;
                        break;

                    default :

                        // if a TD tag is open
                        if($tdOpen) {

                            // check if td tag contained colspan
                            if(preg_match('/<td [^>]*colspan\s*=\s*(?:\'|")?\s*([0-9]+)[^>]*>/is', $tdTag, $matches))
                                $colspan = $matches[1];
                            else
                                $colspan = 1;

                            // check if td tag contained rowspan
                            if(preg_match('/<td [^>]*rowspan\s*=\s*(?:\'|")?\s*([0-9]+)[^>]*>/is', $tdTag, $matches))
                                $rowspan = $matches[1];
                            else
                                $rowspan = 0;

                            // loop over the colspans
                            for($c = 0; $c < $colspan; $c++) {

                                // if the item data has not already been defined by a rowspan loop, set it
                                if(!isset($tableCleaned[$rowCount][$colCount]))
                                    $tableCleaned[$rowCount][$colCount] = $item;
                                else
                                    $tableCleaned[$rowCount][$colCount + 1] = $item;

                                // create new rowCount variable for looping through rowspans
                                $futureRows = $rowCount;

                                // loop through row spans
                                for($r = 1; $r < $rowspan; $r++) {
                                    $futureRows++;
                                    if($colspan > 1)
                                        $tableCleaned[$futureRows][$colCount + 1] = $item;
                                    else
                                        $tableCleaned[$futureRows][$colCount] = $item;
                                }

                                // increase column count
                                $colCount++;

                            }

                            // sort the row array by the column keys (as inserting rowspans screws up the order)
                            ksort($tableCleaned[$rowCount]);
                        }
                        break;
                }
            }
            // set row count
            if($this->headerRow)
                $this->rowCount    = count($tableCleaned) - 1;
            else
                $this->rowCount    = count($tableCleaned);

            $this->rawArray = $tableCleaned;

        }

        function createArray() {

            // define array to store table data
            $tableData = array();

            // get column headers
            if($this->headerRow) {

                // trim string
                $row = $this->rawArray[$this->headerRow];

                // set column names array
                $columnNames = array();
                $uniqueNames = array();

                // loop over column names
                $colCount = 0;
                foreach($row as $cell) {

                    $colCount++;

                    $cell = strip_tags($cell);
                    $cell = trim($cell);

                    // save name if there is one, otherwise save index
                    if($cell) {

                        if(isset($uniqueNames[$cell])) {
                            $uniqueNames[$cell]++;
                            $cell .= ' ('.($uniqueNames[$cell] + 1).')';
                        }
                        else {
                            $uniqueNames[$cell] = 0;
                        }

                        $columnNames[$colCount] = $cell;

                    }
                    else
                        $columnNames[$colCount] = $colCount;

                }

                // remove the headers row from the table
                unset($this->rawArray[$this->headerRow]);

            }

            // remove rows to drop
            foreach(explode(',', $this->dropRows) as $key => $value) {
                unset($this->rawArray[$value]);
            }

            // set the end row
            if($this->maxRows)
                $endRow = $this->startRow + $this->maxRows - 1;
            else
                $endRow = count($this->rawArray);

            // loop over row array
            $rowCount = 0;
            $newRowCount = 0;
            foreach($this->rawArray as $row) {

                $rowCount++;

                // if the row was requested then add it
                if($rowCount >= $this->startRow && $rowCount <= $endRow) {

                    $newRowCount++;

                    // create new array to store data
                    $tableData[$newRowCount] = array();

                    //$tableData[$newRowCount]['origRow'] = $rowCount;
                    //$tableData[$newRowCount]['data'] = array();
                    $tableData[$newRowCount] = array();

                    // set the end column
                    if($this->maxCols)
                        $endCol = $this->startCol + $this->maxCols - 1;
                    else
                        $endCol = count($row);

                    // loop over cell array
                    $colCount = 0;
                    $newColCount = 0;
                    foreach($row as $cell) {

                        $colCount++;

                        // if the column was requested then add it
                        if($colCount >= $this->startCol && $colCount <= $endCol) {

                            $newColCount++;

                            if($this->extraCols) {
                                foreach($this->extraCols as $extraColumn) {
                                    if($extraColumn['column'] == $colCount) {
                                        if(preg_match($extraColumn['regex'], $cell, $matches)) {
                                            if(is_array($extraColumn['names'])) {
                                                $this->extraColsCount = 0;
                                                foreach($extraColumn['names'] as $extraColumnSub) {
                                                    $this->extraColsCount++;
                                                    $tableData[$newRowCount][$extraColumnSub] = $matches[$this->extraColsCount];
                                                }
                                            } else {
                                                $tableData[$newRowCount][$extraColumn['names']] = $matches[1];
                                            }
                                        } else {
                                            $this->extraColsCount = 0;
                                            if(is_array($extraColumn['names'])) {
                                                $this->extraColsCount = 0;
                                                foreach($extraColumn['names'] as $extraColumnSub) {
                                                    $this->extraColsCount++;
                                                    $tableData[$newRowCount][$extraColumnSub] = '';
                                                }
                                            } else {
                                                $tableData[$newRowCount][$extraColumn['names']] = '';
                                            }
                                        }
                                    }
                                }
                            }

                            if($this->stripTags)
                                $cell = strip_tags($cell);

                            // set the column key as the column number
                            $colKey = $newColCount;

                            // if there is a table header, use the column name as the key
                            if($this->headerRow)
                                if(isset($columnNames[$colCount]))
                                    $colKey = $columnNames[$colCount];

                            // add the data to the array
                            //$tableData[$newRowCount]['data'][$colKey] = $cell;
                            $tableData[$newRowCount][$colKey] = $cell;
                        }
                    }
                }
            }

            $this->finalArray = $tableData;
            return $tableData;
        }
    }
?>
<?php


class mycurl{

    var $myurl;
    var $token;

    public function mycurl(){
        $this->myurl = '';
    }

    public function save_web_page(){
        if ($this->myurl != '') {
            $ch = curl_init($this->myurl);
            $fp = fopen("example_homepage.txt", "w");

            $args = array (
                CURLOPT_FILE => $fp,
                CURLOPT_HEADER => 0,
            );
            curl_setopt($ch, $args, $fp);
            curl_exec($ch);
            curl_close($ch);

            fclose($fp);
        }

    }
    /**
     * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
     * array containing the HTTP server response header fields and content.
     */
    function get_web_page() {
        if (!empty($this->myurl)) {
            $url  = $this->myurl;
            $options = array(
                CURLOPT_RETURNTRANSFER => true, // return web page
                CURLOPT_HEADER => false, // don't return headers
                CURLOPT_FOLLOWLOCATION => true, // follow redirects
                CURLOPT_ENCODING => "", // handle all encodings
                CURLOPT_USERAGENT => "spider", // who am i
                CURLOPT_AUTOREFERER => true, // set referer on redirect
                CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
                CURLOPT_TIMEOUT => 120, // timeout on response
                CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
            );

            $ch = curl_init($url);
            curl_setopt_array($ch, $options);
            $content = curl_exec($ch);
            $err = curl_errno($ch);
            $errmsg = curl_error($ch);
            $header = curl_getinfo($ch);
            curl_close($ch);

            $header['errno'] = $err;
            $header['errmsg'] = $errmsg;
            $header['content'] = $content;


            /**
             * Error Checking
             */
            if ( $header['errno'] != 0 ) {
                $error[] = 'error: bad url, timeout, redirect loop';
            }

            if ( $header['http_code'] != 200 ) {
                $error[] = 'error: no page, no permissions, no service';
            }

            if (isset($error)) {
                $error['error'] = TRUE;
                return $error;
            }

            return $header;
        }
    }


    public function get_web_page_content(){
        $result = $this->get_web_page();
        if (!isset($result['error'])) {
            return $result['content'];
        }
    }


    public function gettoken($token) {
        if (empty($token)) {
            $token = $this->token;
        } else {
            $this->token = $token;
        }

        return rawurldecode ($token);

    }
    /**
 * Extract URLs from a web page.
 */

    public function extract_html_urls( $text )
{
    $match_elements = array( // HTML
            array('element' => 'a', 'attribute' => 'href'), // 2.0
            array('element' => 'a', 'attribute' => 'urn'), // 2.0
            array('element' => 'base', 'attribute' => 'href'), // 2.0
            array('element' => 'form', 'attribute' => 'action'), // 2.0
            array('element' => 'img', 'attribute' => 'src'), // 2.0
            array('element' => 'link', 'attribute' => 'href'), // 2.0

            array('element' => 'applet', 'attribute' => 'code'), // 3.2
            array('element' => 'applet', 'attribute' => 'codebase'), // 3.2
            array('element' => 'area', 'attribute' => 'href'), // 3.2
            array('element' => 'body', 'attribute' => 'background'), // 3.2
            array('element' => 'img', 'attribute' => 'usemap'), // 3.2
            array('element' => 'input', 'attribute' => 'src'), // 3.2

            array('element' => 'applet', 'attribute' => 'archive'), // 4.01
            array('element' => 'applet', 'attribute' => 'object'), // 4.01
            array('element' => 'blockquote', 'attribute' => 'cite'), // 4.01
            array('element' => 'del', 'attribute' => 'cite'), // 4.01
            array('element' => 'frame', 'attribute' => 'longdesc'), // 4.01
            array('element' => 'frame', 'attribute' => 'src'), // 4.01
            array('element' => 'head', 'attribute' => 'profile'), // 4.01
            array('element' => 'iframe', 'attribute' => 'longdesc'), // 4.01
            array('element' => 'iframe', 'attribute' => 'src'), // 4.01
            array('element' => 'img', 'attribute' => 'longdesc'), // 4.01
            array('element' => 'input', 'attribute' => 'usemap'), // 4.01
            array('element' => 'ins', 'attribute' => 'cite'), // 4.01
            array('element' => 'object', 'attribute' => 'archive'), // 4.01
            array('element' => 'object', 'attribute' => 'classid'), // 4.01
            array('element' => 'object', 'attribute' => 'codebase'), // 4.01
            array('element' => 'object', 'attribute' => 'data'), // 4.01
            array('element' => 'object', 'attribute' => 'usemap'), // 4.01
            array('element' => 'q', 'attribute' => 'cite'), // 4.01
            array('element' => 'script', 'attribute' => 'src'), // 4.01

            array('element' => 'audio', 'attribute' => 'src'), // 5.0
            array('element' => 'command', 'attribute' => 'icon'), // 5.0
            array('element' => 'embed', 'attribute' => 'src'), // 5.0
            array('element' => 'event-source', 'attribute' => 'src'), // 5.0
            array('element' => 'html', 'attribute' => 'manifest'), // 5.0
            array('element' => 'source', 'attribute' => 'src'), // 5.0
            array('element' => 'video', 'attribute' => 'src'), // 5.0
            array('element' => 'video', 'attribute' => 'poster'), // 5.0

            array('element' => 'bgsound', 'attribute' => 'src'), // Extension
            array('element' => 'body', 'attribute' => 'credits'), // Extension
            array('element' => 'body', 'attribute' => 'instructions'), //Extension
            array('element' => 'body', 'attribute' => 'logo'), // Extension
            array('element' => 'div', 'attribute' => 'href'), // Extension
            array('element' => 'div', 'attribute' => 'src'), // Extension
            array('element' => 'embed', 'attribute' => 'code'), // Extension
            array('element' => 'embed', 'attribute' => 'pluginspage'), // Extension
            array('element' => 'html', 'attribute' => 'background'), // Extension
            array('element' => 'ilayer', 'attribute' => 'src'), // Extension
            array('element' => 'img', 'attribute' => 'dynsrc'), // Extension
            array('element' => 'img', 'attribute' => 'lowsrc'), // Extension
            array('element' => 'input', 'attribute' => 'dynsrc'), // Extension
            array('element' => 'input', 'attribute' => 'lowsrc'), // Extension
            array('element' => 'table', 'attribute' => 'background'), // Extension
            array('element' => 'td', 'attribute' => 'background'), // Extension
            array('element' => 'th', 'attribute' => 'background'), // Extension
            array('element' => 'layer', 'attribute' => 'src'), // Extension
            array('element' => 'xml', 'attribute' => 'src'), // Extension

            array('element' => 'button', 'attribute' => 'action'), // Forms 2.0
            array('element' => 'datalist', 'attribute' => 'data'), // Forms 2.0
            array('element' => 'form', 'attribute' => 'data'), // Forms 2.0
            array('element' => 'input', 'attribute' => 'action'), // Forms 2.0
            array('element' => 'select', 'attribute' => 'data'), // Forms 2.0
            // XHTML
            array('element' => 'html', 'attribute' => 'xmlns'),
            // WML
            array('element' => 'access', 'attribute' => 'path'), // 1.3
            array('element' => 'card', 'attribute' => 'onenterforward'), // 1.3
            array('element' => 'card', 'attribute' => 'onenterbackward'), // 1.3
            array('element' => 'card', 'attribute' => 'ontimer'), // 1.3
            array('element' => 'go', 'attribute' => 'href'), // 1.3
            array('element' => 'option', 'attribute' => 'onpick'), // 1.3
            array('element' => 'template', 'attribute' => 'onenterforward'), // 1.3
            array('element' => 'template', 'attribute' => 'onenterbackward'), // 1.3
            array('element' => 'template', 'attribute' => 'ontimer'), // 1.3
            array('element' => 'wml', 'attribute' => 'xmlns'), // 2.0
        );

        $match_metas = array(
            'content-base',
            'content-location',
            'referer',
            'location',
            'refresh',
        );


        // Extract all elements
        if (!preg_match_all('/<([a-z][^>]*)>/iu', $text, $matches)) {
            return array();
        }
        
        $elements = $matches[1];
        $value_pattern = '=(("([^"]*)")|([^\s]*))';


        // Match elements and attributes
        foreach ($match_elements as $match_element) {
            $name = $match_element['element'];
            $attr = $match_element['attribute'];
            $pattern = '/^' . $name . '\s.*' . $attr . $value_pattern . '/iu';
            if ($name == 'object')
                $split_pattern = '/\s*/u';  // Space-separated URL list
            else if ($name == 'archive')
                $split_pattern = '/,\s*/u'; // Comma-separated URL list
            else
                unset($split_pattern);    // Single URL
            foreach ($elements as $element) {
                if (!preg_match($pattern, $element, $match))
                    continue;
                $m = empty($match[3]) ? $match[4] : $match[3];
                if (!isset($split_pattern))
                    $urls[$name][$attr][] = $m;
                else {
                    $msplit = preg_split($split_pattern, $m);
                    foreach ($msplit as $ms)
                        $urls[$name][$attr][] = $ms;
                }
            }
        }

        // Match meta http-equiv elements
        foreach ($match_metas as $match_meta) {
            $attr_pattern = '/http-equiv="?' . $match_meta . '"?/iu';
            $content_pattern = '/content' . $value_pattern . '/iu';
            $refresh_pattern = '/\d*;\s*(url=)?(.*)$/iu';
            foreach ($elements as $element) {
                if (!preg_match('/^meta/iu', $element) ||
                        !preg_match($attr_pattern, $element) ||
                        !preg_match($content_pattern, $element, $match))
                    continue;
                $m = empty($match[3]) ? $match[4] : $match[3];
                if ($match_meta != 'refresh')
                    $urls['meta']['http-equiv'][] = $m;
                else if (preg_match($refresh_pattern, $m, $match))
                    $urls['meta']['http-equiv'][] = $match[2];
            }
        }

        // Match style attributes
        $urls['style'] = array();
        $style_pattern = '/style' . $value_pattern . '/iu';
        foreach ($elements as $element) {
            if (!preg_match($style_pattern, $element, $match))
                continue;
            $m = empty($match[3]) ? $match[4] : $match[3];
            $style_urls = $this->extract_css_urls($m);
            if (!empty($style_urls))
                $urls['style'] = array_merge_recursive(
                                $urls['style'], $style_urls);
        }

        // Match style bodies
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/siu', $text, $style_bodies)) {
            foreach ($style_bodies[1] as $style_body) {
                $style_urls = $this->extract_css_urls($style_body);
                if (!empty($style_urls))
                    $urls['style'] = array_merge_recursive(
                                    $urls['style'], $style_urls);
            }
        }
        if (empty($urls['style']))
            unset($urls['style']);

        return $urls;
    }

    /**
     * Extract URLs from CSS text.
     */
    public function extract_css_urls($text) {
        $urls = array();

        $url_pattern = '(([^\\\\\'", \(\)]*(\\\\.)?)+)';
        $urlfunc_pattern = 'url\(\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
        $pattern = '/(' .
                '(@import\s*[\'"]' . $url_pattern . '[\'"])' .
                '|(@import\s*' . $urlfunc_pattern . ')' .
                '|(' . $urlfunc_pattern . ')' . ')/iu';
        if (!preg_match_all($pattern, $text, $matches))
            return $urls;

        // @import '...'
        // @import "..."
        foreach ($matches[3] as $match)
            if (!empty($match))
                $urls['import'][] =
                        preg_replace('/\\\\(.)/u', '\\1', $match);

        // @import url(...)
        // @import url('...')
        // @import url("...")
        foreach ($matches[7] as $match)
            if (!empty($match))
                $urls['import'][] =
                        preg_replace('/\\\\(.)/u', '\\1', $match);

        // url(...)
        // url('...')
        // url("...")
        foreach ($matches[11] as $match)
            if (!empty($match))
                $urls['property'][] =
                        preg_replace('/\\\\(.)/u', '\\1', $match);

        return $urls;
    }
}

if (!empty($_POST['myurl'])) {
    $mycurl = new mycurl;
    $mycurl->myurl = (string) $_POST['myurl'];
    $output = $mycurl->get_web_page_content();
    $url = $mycurl->extract_html_urls($output);


    $hasildebug = print_r($url, TRUE);
    echo '<pre style="font-size:14px">' . '$url : ' . htmlentities($hasildebug) . '</pre>';



    //$url = $mycurl->extract_html_urls($output); //find list url form a page
    $output = strip_tags($output);
    $output = explode('flashvars.video_url = ', $output);
    $output = explode(';', $output[1]);
    $output = explode("'", $output[0]);

    $url = rawurldecode ($output[1]);
    if (!empty($url)) {
        echo '<a href ="' . $url . '">go</a>';
    } else {
        echo $mycurl->myurl . " has no flashvars.video_url";
    }
} ?>

<form name="myurl" method="post">
    <input type="text" name="myurl" value="<?php echo $_POST['myurl'] ?>" style="width:800px;"/>
    <input type="submit" value="go fetch" />
</form>