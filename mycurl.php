<?php
/**
 * Library for curl outside website. based on ???
 *
 * @uses wordpress core 3.0.x
 * @category Options
 * @package Options
 * @subpackage Option
 * @example
 *
 * @version 0.0.1 19desc2010
 *
 * @access public
 * @todo test and perfectiong operation save of html file
 * @todo simplefied curl operation in home.php and transfer all of its
 *      messaging mechanism into this class
 * @todo otomatic download --> make this class able to download file without user have to click the link and
 *      save it via browser. if possible, let's the class do it for yoou
 * @todo if otomatic download berhasil maka tambahkan cronjob. kemampuan mengeksekusi cron
 *      sehingga bisa melakukan download sesuai interval yg telah ditentukan di dalam database
 * @todo kemampuan mengukur ukuran file
 * @todo tambahkan kemampuan mendownload dari berbagai layanan website lain
 *          1. youtube
 *          2. youporn
 *          3. dll
 *          4. komik
 * @author uwiuw
 * @copyright 2010 uwiuw
 */

class mycurl{

    var $myurl;
    var $token;

    public function mycurl(){
        $this->myurl = '';
    }

    /**
    * set saving operation of a web page
    */
    public function set_saveWebPage(){
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
    * @return boolean
    */
    function get_webPage($url ='') {
        if (!empty($this->myurl) || !empty($url)) {
            if (!$url) {
                $url  = $this->myurl;
            }


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

    /**
    * get the key of content of the array that came from curl output
    * @return <type>
    */
    public function get_webPageContent(){
        $result = $this->get_webPage();
        if (!isset($result['error'])) {
            return $result['content'];
        }
    }
    public function get_webPageFileSize($url =''){        
        $result = $this->get_webPage($url);
        if (!isset($result['error'])) {
            return $result['content'];
        }
    }

    public function get_token($token) {
        if (empty($token)) {
            $token = $this->token;
        } else {
            $this->token = $token;
        }

        return rawurldecode ($token);
    }

    /**
    * get list of urls from a webpage
    */
    public function get_urlsFromHtml( $text ){
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

            /**
             * Extract all elements
             */
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

            /*
             *  Match meta http-equiv elements
             */
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

            /*
             *  Match style attributes
             */
            $urls['style'] = array();
            $style_pattern = '/style' . $value_pattern . '/iu';
            foreach ($elements as $element) {
                if (!preg_match($style_pattern, $element, $match))
                    continue;
                $m = empty($match[3]) ? $match[4] : $match[3];
                $style_urls = $this->get_urlsFromCss($m);
                if (!empty($style_urls))
                    $urls['style'] = array_merge_recursive(
                                    $urls['style'], $style_urls);
            }

            /*
             *  Match style bodies
             */
            if (preg_match_all('/<style[^>]*>(.*?)<\/style>/siu', $text, $style_bodies)) {
                foreach ($style_bodies[1] as $style_body) {
                    $style_urls = $this->get_urlsFromCss($style_body);
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
    public function get_urlsFromCss($text) {
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