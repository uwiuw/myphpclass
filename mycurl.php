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
 * @todo test and perfecting operation save of html file
 * @todo simplefied curl operation in home.php and transfer all of its
 *      messaging mechanism into this class
 * @todo otomatic download --> make this class able to download file without user have to click the link and
 *      save it via browser. if possible, let's the class do it for yoou
 * @todo if otomatic download berhasil maka tambahkan cronjob. kemampuan mengeksekusi cron
 *      sehingga bisa melakukan download sesuai interval yg telah ditentukan di dalam database
 * @todo tambahkan kemampuan mendownload dari berbagai layanan website populer
 *          1. youtube
 *          2. youporn
 *          3. dll
 *          4. komik
 * @todo buat method yg mampu mengurus class yg bekerja sebagai loop. class ini akan diinjekkan kedalam
	class mycurl sehingga keduanya bisa independent dari satu sama lain akan tetapi tetap bisa bekerja sama
 * @author uwiuw
 * @copyright 2010 uwiuw
 */

class mycurl{

    var $myurl;
    var $token;
    var $title = '<h1>My Curl Form of Madness</h1>';

    public function mycurl(){
        $this->myurl = '';
    }

    public function set_title($title) {
        $this->title = $title;
    }
    public function get_title() {
        return $this->title;
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

    public function get_webPageFileSize($url =''){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data === false)
            return false;
        if (preg_match('/Content-Length: (\d+)/', $data, $matches))
            return (float) $matches[1];
    }

    function get_webPageFileSizeformat($size) {
		if ($size != '') {
			$units = array(' B', ' KB', ' MB', ' GB', ' TB');
			for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
			return round($size, 2).$units[$i];
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
                    $urls['style'] = array_merge_recursive($urls['style'], $style_urls);
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

    /**
     * get external class and do the operation here
     *
     * @param <type> $object
     * @return mixed the class can return anything that external class object will return
     */
    public function get_externalclass($external_object){
        if (is_object($external_object)) {

            if (property_exists($external_object, 'title')) {
                $this->set_title($this->get_title() . $external_object->title);
            }

            if (method_exists($external_object, 'do_inject')) {
                return $external_object->do_inject();
            } else {
                echo get_class($external_object)  . ' class object has no "do_inject" method. This method is
                    the brigde to communicate with mycurl class';
                return false;
            }
        } else {
           echo 'The object is not an instance of a class.';
           return false;
        }
    }

}

/**
 * special method that going to use in my_curl->get_externalclass()
 * build for getting the url of keezmovies.com file. After click them, the file will
 * get downloaded
 * @example
        $keezmovies = new keezmovies();
        $keezmovies->output = $output;
        $url = $mycurl->get_externalclass($keezmovies);
 * @properties $output html putput came form main class.
 */
class keezmovies{
    public $output = '';
    public $title = '<h3>Keezmovies Downloads Url by @uwiuw</h3>';

    public function do_inject(){
        if ($output = $this->output) {
            $output = strip_tags($output);
            $output = explode('flashvars.video_url = ', $output);
            $output = explode(';', $output[1]);
            $output = explode("'", $output[0]);

            $url = rawurldecode ($output[1]);

            return $url;
        }
    }
}


/**
 * @todo Lists of todo
 * 1. Pindahkan Form dibawah ini kedalam class khusus
 * 3. memiliki mekanisme yg lebih siap untuk bridge
 * 4. memiliki nama variable yg mirip dengan mycurl
 * inspired by http://vipld.com/how-to-download-video-from-youtube-using-php/
 */
class youtube {
    public $output = '';
    public $video_url = '';
    public $title = '<h3>Youtube Downloads by @uwiuw</h3>';

    /**
     * get the url of flv file
     *
     * @param <type> $data
     * @return string url of the flv file
     */
    function get_flv($data)
    {

        //After &fmt_url_map=
        //Before &
        //Split by %2C
        //Select first
        //After %7C
        if(eregi('fmt_url_map',$data))
        {
            $data = end(split('&fmt_url_map=',$data));
            $data = current(split('&',$data));
            $split = explode('%2C',$data);
            $data = $split[0];
            $data = end(split('%7C',$data));
            return(urldecode($data));
        }else{
            if(eregi('verify-age-details',$data))
            {
                echo 'Age verification needed<br>';
            }
            return(false);
        }
    }

    /**
     * get the html output by mimicing http request of browser
     */
    function get_html($url)
    {
        $ch = curl_init();
        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[] = "Accept-Language: en-us,en;q=0.5";
        $header[] = "Pragma: "; //browsers keep this blank.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows;U;Windows NT 5.0;en-US;rv:1.4) Gecko/20030624 Netscape/7.1 (ax)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_REFERER, "http://www.youtube.com/");
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE);
        $result = curl_exec ($ch);
        if (!$result)
        {
            echo "cURL error number:" .curl_errno($ch);
            echo "cURL error:" . curl_error($ch);
            exit;
        }
        curl_close ($ch);
        return($result);
    }

    /**
     * download the file
     *
     * @param <type> $url
     * @param <type> $filename
     * @return <type>
     */
    function get_file($url, $filename)
    {
        $file = fopen($filename, 'wb');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE);
        curl_exec($ch);
        curl_close($ch);
        fclose($file);

        return(true);
    }

    function get_playlist($url)
    {
        $data = get_html($url);
        $data = end(split('FULL_SEQUENTIAL_VIDEO_LIST',$data));
        $data = current(split('FULL_SHUFFLED_VIDEO_LIST',$data));
        $data = substr(ereg_replace('[^-_a-zA-Z,"0-9]', '', $data),0,-1);
        eval('$tracks = array('.$data.');');

        return($tracks);
    }

    /***
     * get list of youtube url and then download it
     *
     * @todo buat kemampaun method bisa mereturn value url yg berasal dari kumpulan array
     * coba periksa cara penggunaannya pada class ini
     */
    function download_playlist($tracks)
    {
        foreach($tracks as $id)
        {
            if (!is_integer($id)) {
                continue;
            }

            $data = get_html('http://youtube.com/watch?v='.$id);
            if ($data != '') {
                $this->download_track($data);
            }
        }
    }

    /**
     * get the file url and then download it
     *
     * @todo    buat class ini bisa mengkomunikasikan pesan error yg terjadi
     *          dengan form yg ada di home.php. Saat ini internal error cuma
     *          bisa di-echo. Tapi tidak bisa dibypass ke class mycurl
     *          dan dioutput dengan benar pada form html yg ada pada home.php
     *
     * @param <type> $data
     * @return <type>
     */
    function download_track($data)
    {
        $name = end(split('eow-title',$data));
        $name = current(split('">',$name));
        $name = ereg_replace('[^-_a-zA-Z,"\' :0-9]',"",end(split('title="',$name)));
        $name = ereg_replace(' ','_',$name);

        $filename = getcwd() . '\\' . $name.'.flv';
        if(!file_exists($filename))
        {
            echo 'Downloading  : '. $filename;
            $flv = $this->get_flv($data);
            if($flv)
            {
                flush();
                if($this->get_file($flv, $filename))
                {
                    echo 'Done<br> the file is ' . $filename;
                }
                flush();
            }
        } else {
            echo "$filename is exist";
        }

        return $this->video_url;
    }

    /**
     * method for bridging with other class (in this context, it would be myclass)
     *
     * @todo testing how the brigde works
     * @return string url file
     */
    public function do_inject(){
        if ($output = $this->output) {
            return $this->download_track($output);
        }
    }

    /**
     * do the basic operation
     *
     * @param <type> $url
     * @return mixed string of url of false
     */
    function init($url) {
        /**
         * make the download have no script time limitation. it will excecuted forever
         * if they have to
         */
        ini_set('max_execution_time',0);

        $vars = end(explode('?',$url));
        $pairs = explode('&',$vars);
        foreach($pairs as $pair)
        {
            $var = explode('=',$pair);
            $data[$var[0]] = $var[1];
        }
        if($data['v']>0)
        {
            $this->download_playlist(get_playlist($url));
            return $url;
        }else{
            $this->video_url = 'http://youtube.com/watch?v='.$data['v'];
            $this->download_track($this->get_html($this->video_url));

            return $this->video_url;
        }
    }
}