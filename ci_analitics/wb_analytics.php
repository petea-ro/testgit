<?php
//	300 is more than decent
ini_set ( "max_execution_time", 300 );

/**
* Analytics reports class
* @author  :  drsoft.com <office@drsoft.com>
* @version :  1.0
* @date       22/02/2009
* Purpose  : Fun and fame!
* Released : Under GPL
* Requirements: curl extension loaded, PHP5+ would be nice as I haven't tested it on 4
* Usage:

	$analytics = new wb_analytics;
	$analytics->config [ 'user' ] = 'analytics username';
	$analytics->config [ 'password' ] = 'analytics password';

	$report = array
	(
		'profile'	=> 11040536,
		'from'		=> '2009-01-22',
		'to'		=> '2009-02-22',
		'report'	=> 'Dashboard'
	);
	
	echo $analytics->report ( $report );//	output is XML
*/

class WB_analytics {
	
	var $config = array
	(
		'datasource'	=> 'google_analytics',
		'user'		=> '',//	analytics username
		'password'	=> '',//	analytics password

	);

	//	connection status
	var $connected = FALSE;

	function connected ()
	{
		return $this->connected;
	}

	function login ()
	{
		if ( empty ( $user ) ) {
			extract ( $this->config );
		}
		if ( @empty ( $user ) || @empty ( $password ) )
		{
			return trigger_error ( 'Please specify a user / password for using this service' );
		}

		$post = array
		(
			'continue'		=> 'http://www.google.com/analytics/home/?et=reset&hl=en-US',
			'service'		=> 'analytics',
			'rmShown'		=> 1,
			'nui'			=> 'hidden',
			'hl'			=> 'en-US',
			'Email'			=> $user,
			'PersistentCookie'	=> 'yes',
			'Passwd'		=> $password
		);

		$response = $this->fetch ( 'https://www.google.com/accounts/ServiceLoginBoxAuth', $post, TRUE );

		//	there was no way to move further without following this url
		$redir = 'https://www.google.com/accounts/CheckCookie?continue=http%3A%2F%2Fwww.google.com%2Fanalytics%2Fhome%2F%3Fet%3Dreset%26hl%3Den-US&amp;amp;hl=en-US&amp;amp;service=analytics&amp;amp;chtml=LoginDoneHtml';
		
		$response = $this->fetch ( $redir );
		$response = $this->fetch ( "https://www.google.com/analytics/settings/?et=reset&hl=en-US" );
		
		$this->config [ 'database' ] = $user;
		
		if ( preg_match ( '/' . preg_quote ( $post [ 'Email' ] ) . '/', $response ) ) {
			return $this->connected = true;
		}
	}

	function fetch ( $url, $vars = array (), $is_post = FALSE )
	{
		$post_array = array();

		foreach ( $vars as $key => $value )
		{
			$post_array [] = urlencode($key) . "=" . urlencode($value);
		}

		$post_string = implode ( "&", $post_array );
		
		$url = ( $is_post ) ? $url : $url . '?' . $post_string;

		$ch = curl_init ();

		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6" );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, "cookies.txt" );
		curl_setopt ( $ch, CURLOPT_COOKIEFILE, "cookies.txt" );

		if ( $is_post ) {
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_string );
			curl_setopt ( $ch, CURLOPT_POST, 1 );
		}
	
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		return $result;
	}

	function listSources ()
	{
		if ( ! $this->connected () && ! $this->login () ) {
			return FALSE;
		}
		
		$sources = array();
		$response = $this->fetch ( 'https://www.google.com/analytics/home/?et=reset&hl=en-US&ns=100' );

		$optionsRegex = '/<option.+?value="([0-9]+)".*?>([^<]+)<\/option>/si';
		preg_match ( '/<select.+?id="account".*?>(.+?)<\/select>/is', $response, $accounts );

		if ( empty ( $accounts ) ) {
			return FALSE;
		}

		preg_match_all ( $optionsRegex, $accounts [ 1 ], $accounts, PREG_SET_ORDER );

		if ( empty ( $accounts ) ) {
			return FALSE;
		}

		foreach ( $accounts as $i => $account )
		{
			list ( , $id, $name ) = $account;
			if ( empty ( $id ) || ! is_numeric ( $id ) ) {
				continue;
			}

			$account = array ( 'Account' => compact ( 'id', 'name' ) );
			if ( $i != 0 ) {
				$response = $this->fetch ( 'https://www.google.com/analytics/home/admin?scid=' . $id . '&ns=100' );
			}

			preg_match ( '/<select.+?name="id".+?id="profile".*?>(.+?)<\/select>/is', $response, $profiles );

			if ( empty ( $profiles ) ) {
				$account [ 'Profile' ] = array ();
				continue;
			}

			preg_match_all ( $optionsRegex, $profiles [ 1 ], $profiles, PREG_SET_ORDER );

			foreach ( $profiles as $profile )
			{
				list ( , $pid, $pname ) = $profile;
	
				if ( empty ( $pid ) || ! is_numeric ( $pid ) ) {
					continue;
				}

				$account [ 'Profile' ] [] = array ( 'id' => $pid, 'name' => $pname );
			}
	
			$sources [] = $account;
		}

		return $sources;
	}

	function report ( $conditions = array () )
	{
		if ( ! $this->connected () && ! $this->login () ) {
			return FALSE;
		}

		$defaults = array
		(
			'profile' => NULL,
			'report'  => 'Dashboard',
			'from'    => date ( 'Y-m-d', time () - 1 * 2692000 ),
			'to'      => date ( 'Y-m-d' ),
			'query'   => array (),
			'tab'     => 0,
			'format'  => 'xml',
			'compute' => 'average',
			'view'    => 0
		);

		$conditions = $this->am ( $defaults, $conditions );
		$formats = array ( 'pdf' => 0, 'xml' => 1, 'csv' => 2, 'tsv' => 3 );

		foreach ( array ( 'from', 'to' ) as $condition ) {
			if ( is_string ( $conditions [ $condition ] ) ) {
				$conditions [ $condition ] = strtotime ( $conditions [ $condition ] );
			}
		}
		
		if ( ! isset ( $conditions [ 'profile' ] ) ) {
			$sources = $this->listSources ();
			$conditions [ 'profile' ] = $sources [ 0 ] [ 'Profile' ] [ 0 ] [ 'id' ];
		}
		elseif ( is_string ( $conditions [ 'profile' ] ) ) {
			$sources = $this->listSources ();
			foreach ( $sources as $source ) {
				$profiles = WB_analytics::combine ( $source, 'Profile.{n}.name', 'Profile.{n}.id' );
				if ( isset ( $profiles [ $conditions [ 'profile' ] ] ) ) {
					$conditions [ 'profile' ] = $profiles [ $conditions [ 'profile' ] ];
					break;
				}
			}
		}
		
		$query = array
		(
			'fmt'	=> isset ( $formats [ $conditions [ 'format' ] ] ) ? $formats [ $conditions [ 'format' ] ] : $conditions [ 'format' ],
			'id'	=> $conditions [ 'profile' ],
			'pdr'	=> date ( 'Ymd', $conditions [ 'from' ] ) . '-' . date ( 'Ymd', $conditions [ 'to' ] ),
			'tab'	=> $conditions [ 'tab' ],
			'cmp'	=> $conditions [ 'compute' ],
			'view'	=> $conditions [ 'view' ],
			'rpt'	=> $conditions [ 'report' ] . 'Report',
		);

		$query = WB_analytics::am ( $query, $conditions [ 'query' ] );

		$report = $this->fetch ( "https://www.google.com/analytics/reporting/export", $query, FALSE );

		//	we're using our own xml to array library here,
		//	you should look for something similar as the output is in xml.
		return $report;
	}


	/**
	 * Function to merge a group of arrays
	 * Taken from cakephp to make this lib functional.
	 */
	
	function am ()
	{
		$r = array ();
		$args = func_get_args ();
		foreach ( $args as $a ) {
			if ( ! is_array ( $a ) ) {
				$a = array ( $a );
			}
			$r = array_merge ( $r, $a );
		}
		return $r;
	}
	
	function tokenize($data, $separator = ',', $leftBound = '(', $rightBound = ')') {
		if (empty($data) || is_array($data)) {
			return $data;
		}

		$depth = 0;
		$offset = 0;
		$buffer = '';
		$results = array();
		$length = strlen($data);
		$open = false;

		while ($offset <= $length) {
			$tmpOffset = -1;
			$offsets = array(strpos($data, $separator, $offset), strpos($data, $leftBound, $offset), strpos($data, $rightBound, $offset));
			for ($i = 0; $i < 3; $i++) {
				if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset == -1)) {
					$tmpOffset = $offsets[$i];
				}
			}
			if ($tmpOffset !== -1) {
				$buffer .= substr($data, $offset, ($tmpOffset - $offset));
				if ($data{$tmpOffset} == $separator && $depth == 0) {
					$results[] = $buffer;
					$buffer = '';
				} else {
					$buffer .= $data{$tmpOffset};
				}
				if ($leftBound != $rightBound) {
					if ($data{$tmpOffset} == $leftBound) {
						$depth++;
					}
					if ($data{$tmpOffset} == $rightBound) {
						$depth--;
					}
				} else {
					if ($data{$tmpOffset} == $leftBound) {
						if (!$open) {
							$depth++;
							$open = true;
						} else {
							$depth--;
							$open = false;
						}
					}
				}
				$offset = ++$tmpOffset;
			} else {
				$results[] = $buffer . substr($data, $offset);
				$offset = $length + 1;
			}
		}
		if (empty($results) && !empty($buffer)) {
			$results[] = $buffer;
		}

		if (!empty($results)) {
			$data = array_map('trim', $results);
		} else {
			$data = array();
		}
		return $data;
	}
	
	function classicExtract($data, $path = null)
	{
		if (empty($path)) {
			return $data;
		}
		if (is_object($data)) {
			$data = get_object_vars($data);
		}
		if (!is_array($data)) {
			return $data;
		}

		if (!is_array($path)) {
			$path = WB_analytics::tokenize($path, '.', '{', '}');
		}
		$tmp = array();

		if (!is_array($path) || empty($path)) {
			return null;
		}

		foreach ($path as $i => $key) {
			if (is_numeric($key) && intval($key) > 0 || $key === '0') {
				if (isset($data[intval($key)])) {
					$data = $data[intval($key)];
				} else {
					return null;
				}
			} elseif ($key === '{n}') {
				foreach ($data as $j => $val) {
					if (is_int($j)) {
						$tmpPath = array_slice($path, $i + 1);
						if (empty($tmpPath)) {
							$tmp[] = $val;
						} else {
							$tmp[] = WB_analytics::classicExtract($val, $tmpPath);
						}
					}
				}
				return $tmp;
			} elseif ($key === '{s}') {
				foreach ($data as $j => $val) {
					if (is_string($j)) {
						$tmpPath = array_slice($path, $i + 1);
						if (empty($tmpPath)) {
							$tmp[] = $val;
						} else {
							$tmp[] = WB_analytics::classicExtract($val, $tmpPath);
						}
					}
				}
				return $tmp;
			} elseif (false !== strpos($key,'{') && false !== strpos($key,'}')) {
				$pattern = substr($key, 1, -1);

				foreach ($data as $j => $val) {
					if (preg_match('/^'.$pattern.'/s', $j) !== 0) {
						$tmpPath = array_slice($path, $i + 1);
						if (empty($tmpPath)) {
							$tmp[$j] = $val;
						} else {
							$tmp[$j] = WB_analytics::classicExtract($val, $tmpPath);
						}
					}
				}
				return $tmp;
			} else {
				if (isset($data[$key])) {
					$data = $data[$key];
				} else {
					return null;
				}
			}
		}
		return $data;
	}
	
	function extract($path, $data = null, $options = array()) {
		if (empty($data) && is_string($path) && $path{0} === '/') {
			return array();
		}
		if (is_string($data) && $data{0} === '/') {
			$tmp = $path;
			$path = $data;
			$data = $tmp;
		}
		if (is_array($path) || empty($data) || is_object($path) || empty($path)) {
			return WB_analytics::classicExtract($path, $data);
		}
		if ($path === '/') {
			return $data;
		}
		$contexts = $data;
		$options = array_merge(array('flatten' => true), $options);
		if (!isset($contexts[0])) {
			$contexts = array($data);
		}
		$tokens = array_slice(preg_split('/(?<!=)\/(?![a-z]*\])/', $path), 1);

		do {
			$token = array_shift($tokens);
			$conditions = false;
			if ( preg_match_all ( '/\[([^\]]+)\]/', $token, $m ) )
			{
				$conditions = $m[1];
				$token = substr($token, 0, strpos($token, '['));
			}
			$matches = array();
			foreach ($contexts as $key => $context) {
				if (!isset($context['trace'])) {
					$context = array('trace' => array(null), 'item' => $context, 'key' => $key);
				}
				if ($token === '..') {
					if (count($context['trace']) == 1) {
						$context['trace'][] = $context['key'];
					}
					$parent = join('/', $context['trace']) . '/.';
					$context['item'] = Set::extract($parent, $data);
					$context['key'] = array_pop($context['trace']);
					if (isset($context['trace'][1]) && $context['trace'][1] > 0) {
						$context['item'] = $context['item'][0];
					} else {
						$context['item'] = $context['item'][$key];
					}
					$matches[] = $context;
					continue;
				}
				$match = false;
				if ($token === '@*' && is_array($context['item'])) {
					$matches[] = array(
						'trace' => array_merge($context['trace'], (array)$key),
						'key' => $key,
						'item' => array_keys($context['item']),
					);
				} elseif (is_array($context['item']) && array_key_exists($token, $context['item'])) {
					$items = $context['item'][$token];
					if (!is_array($items)) {
						$items = array($items);
					} elseif (!isset($items[0])) {
						$current = current($items);
						if ((is_array($current) && count($items) <= 1) || !is_array($current)) {
							$items = array($items);
						}
					}

					foreach ( $items as $key => $item )
					{
						$ctext = array ( $context [ 'key' ] );
						if (!is_numeric($key)) {
							$ctext[] = $token;
							$token = array_shift($tokens);
							if (isset($items[$token])) {
								$ctext[] = $token;
								$item = $items[$token];
								$matches[] = array(
									'trace' => array_merge($context['trace'], $ctext),
									'key' => $key,
									'item' => $item,
								);
								break;
							}
						}
						else {
							$key = $token;
						}

						$matches[] = array(
							'trace' => array_merge($context['trace'], $ctext),
							'key' => $key,
							'item' => $item,
						);
					}
				} elseif (($key === $token || (ctype_digit($token) && $key == $token) || $token === '.')) {
					$context['trace'][] = $key;
					$matches[] = array(
						'trace' => $context['trace'],
						'key' => $key,
						'item' => $context['item'],
					);
				}
			}
			if ($conditions) {
				foreach ($conditions as $condition) {
					$filtered = array();
					$length = count($matches);
					foreach ($matches as $i => $match) {
						if (WB_analytics::matches(array($condition), $match['item'], $i + 1, $length)) {
							$filtered[] = $match;
						}
					}
					$matches = $filtered;
				}
			}
			$contexts = $matches;

			if (empty($tokens)) {
				break;
			}
		} while(1);

		$r = array();

		foreach ($matches as $match) {
			if ((!$options['flatten'] || is_array($match['item'])) && !is_int($match['key'])) {
				$r[] = array($match['key'] => $match['item']);
			} else {
				$r[] = $match['item'];
			}
		}
		return $r;
	}
	
	function matches($conditions, $data = array(), $i = null, $length = null) {
		if (empty($conditions)) {
			return true;
		}
		if (is_string($conditions)) {
			return !!WB_analytics::extract($conditions, $data);
		}
		foreach ($conditions as $condition) {
			if ($condition === ':last') {
				if ($i != $length) {
					return false;
				}
				continue;
			} elseif ($condition === ':first') {
				if ($i != 1) {
					return false;
				}
				continue;
			}
			if (!preg_match('/(.+?)([><!]?[=]|[><])(.*)/', $condition, $match)) {
				if (ctype_digit($condition)) {
					if ($i != $condition) {
						return false;
					}
				} elseif (preg_match_all('/(?:^[0-9]+|(?<=,)[0-9]+)/', $condition, $matches)) {
					return in_array($i, $matches[0]);
				} elseif (!array_key_exists($condition, $data)) {
					return false;
				}
				continue;
			}
			list(,$key,$op,$expected) = $match;
			if (!isset($data[$key])) {
				return false;
			}

			$val = $data[$key];

			if ($op === '=' && $expected && $expected{0} === '/') {
				return preg_match($expected, $val);
			}
			if ($op === '=' && $val != $expected) {
				return false;
			}
			if ($op === '!=' && $val == $expected) {
				return false;
			}
			if ($op === '>' && $val <= $expected) {
				return false;
			}
			if ($op === '<' && $val >= $expected) {
				return false;
			}
			if ($op === '<=' && $val > $expected) {
				return false;
			}
			if ($op === '>=' && $val < $expected) {
				return false;
			}
		}
		return true;
	}
	
	function format ( $data, $format, $keys ) {

		$extracted = array ();
		$count = count ( $keys );

		if ( ! $count ) {
			return;
		}

		for ( $i = 0; $i < $count; $i++ ) {
			$extracted[] = WB_analytics::extract ( $data, $keys [ $i ] );
		}
		$out = array ();
		$data = $extracted;
		$count = count ( $data [ 0 ] );

		if ( preg_match_all ( '/\{([0-9]+)\}/msi', $format, $keys2 ) && isset ( $keys2 [ 1 ] ) )
		{
			$keys = $keys2 [ 1 ];
			$format = preg_split ( '/\{([0-9]+)\}/msi', $format );
			$count2 = count ( $format );

			for ( $j = 0; $j < $count; $j++ ) {
				$formatted = '';
				for ( $i = 0; $i <= $count2; $i++ )
				{
					if ( isset ( $format [ $i ] ) ) {
						$formatted .= $format [ $i ];
					}

					if ( isset ( $keys [ $i ] ) && isset ( $data [ $keys [ $i ] ] [ $j ] ) ) {
						$formatted .= $data [ $keys [ $i ] ] [ $j ];
					}
				}
				$out [] = $formatted;
			}
		}
		else {
			$count2 = count ( $data );
			for ( $j = 0; $j < $count; $j++ ) {
				$args = array ();
				for ( $i = 0; $i < $count2; $i++ )
				{
					if ( isset ( $data [ $i ] [ $j ] ) ) {
						$args [] = $data [ $i ] [ $j ];
					}
				}
				$out [] = vsprintf ( $format, $args );
			}
		}
		return $out;
	}
	
	function combine ( $data, $path1 = NULL, $path2 = NULL, $groupPath = NULL )
	{
		if ( empty ( $data ) ) {
			return array ();
		}

		if ( is_object ( $data ) ) {
			$data = get_object_vars ( $data );
		}

		if ( is_array ( $path1 ) ) {
			$format = array_shift ( $path1 );
			$keys = WB_analytics::format ( $data, $format, $path1 );
		}
		else {
			$keys = WB_analytics::extract ( $data, $path1 );
		}

		if ( ! empty ( $path2 ) && is_array ( $path2 ) ) {
			$format = array_shift ( $path2 );
			$vals = WB_analytics::format ( $data, $format, $path2 );

		}
		elseif ( ! empty ( $path2 ) ) {
			$vals = WB_analytics::extract ( $data, $path2 );

		}
		else {
			$count = count ( $keys );
			for ( $i = 0; $i < $count; $i++ ) {
				$vals [ $i ] = NULL;
			}
		}

		if ( $groupPath != NULL ) {
			$group = WB_analytics::extract ( $data, $groupPath );
			if ( ! empty ( $group ) )
			{
				$c = count ( $keys );
				for ( $i = 0; $i < $c; $i++ ) {
					if ( ! isset ( $group [ $i ] ) ) {
						$group [ $i ] = 0;
					}
					if ( ! isset ( $out [ $group [ $i ] ] ) ) {
						$out [ $group [ $i ] ] = array ();
					}
					$out [ $group [ $i ] ] [ $keys [ $i ] ] = $vals [ $i ];
				}
				return $out;
			}
		}

		return array_combine ( $keys, $vals );
	}
}

//END