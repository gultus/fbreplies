<?php

/*****************************************************************************
 *
 * This script provides non Facebook specific utility functions that you may
 * wish to use in your app.  These functions involve 'sanitizing' output to
 * prevent XSS (Cross-site scripting) vulnerabilities.  By using these
 * functions, you remove a certain type of malicious content that could
 * be displayed to your users.  For a more robust and comprehensive solution,
 * Facebook has open sourced XHP.  XHP is a PHP extension which makes your
 * front-end code easier to understand and help you avoid cross-site
 * scripting attacks.  Learn more at 'https://github.com/facebook/xhp/wiki/'.
 *
 ****************************************************************************/


/**
 * @return the value at $index in $array or $default if $index is not set.
 *         By default, the value returned will be sanitized to prevent
 *         XSS attacks, however if $sanitize is set to false, the raw
 *         values will be returned
 */
function idx($array, $index, $default = null, $saniztize = true) {
  if (array_key_exists($index, $array)) {
    $value = $array[$index];
  } else {
    $value = $default;
  }
  if ($sanitize) {
    return htmlentities($value);
  } else {
    return $value;
  }
}

/**
 * This will echo $value after sanitizing any html tags in it.
 * This is for preventing XSS attacks.  You should use echoSafe whenever
 * you are echoing content that could possibly be malicious (i.e.
 * content from an external request). This does not sanitize javascript
 * or attributes
 */
function echoEntity($value) {
  echo(htmlentities($value));
}

/**
 * @return $value if $value is numeric, else null.  Use this to assert that
 *         a value (like a user id) is a number.
 */
function assertNumeric($value) {
  if (is_numeric($value)) {
    return $value;
  } else {
    return null;
  }
}

function checkNum($id){
	if(strpos($id,'.') === false){
		return $id;
	}else{
		$patterns = array();
		$patterns[0] = '/\./';
		$patterns[1] = '/E.*$/';
		$replacements = array();
		$replacements[2] = '';
		$replacements[1] = '';
		return preg_replace($patterns, $replacements, $id);
	}
	
}

function nearestfriend($id,$list){
	$pattern = '/^'.$id.'.*$/';
	foreach($list as $key => $value){
		//echo('Matching '.$pattern.' with '.$key.' '.preg_match($pattern,$key).'<br/>');
		if(preg_match($pattern,$key)){
				return $key;
		}
	}
	return $id;
}
class facebook_batch {
    protected $requests = array();
    protected $responses = null;
    protected $cur = 1;
    protected $map = array();

    const MAX_NUMBER_OF_REQUESTS = 50;

    public function add($path, $method = 'GET', $params = array(), $extra = array()) {
        if(count($this->requests) > self::MAX_NUMBER_OF_REQUESTS) return false;

        $path = trim($path, '/ ');

        $body = http_build_query($params);
        $body = urldecode($body);

        if(strtolower($method) == 'get') {
            if(count($params) > 0) {
                $path .= "?" . $body;
                $body = "";
            }
        }

        $key = $this->cur++;
        $this->requests[$key] = array_merge(array('relative_url' => $path, 'method' => $method, 'body' => $body), $extra);

        return intval($key);
    }

    public function remove($key) {
        unset($this->requests[$key]);
    }
    
    public function removeAll() {
        unset($this->requests);
    }

    public function execute() {
        global $facebook;

        $i = 0;
        foreach($this->requests as $k => $r) {
            $this->map[$k] = $i++;
        }

        $batch = json_encode(array_values($this->requests));
        $params = array('batch' => $batch);

        $this->responses = $facebook->api('/', 'POST', $params);
    }

    public function response($key) {
        if(! $this->responses) $this->execute();

        $rkey = $this->map[intval($key)];
        $resp = $this->responses[intval($rkey)];

        if($resp['code'] != 200) return false;
        return json_decode($resp['body'], true);
    }

    public function getRequests() {
        return $this->requests;
    }
}


