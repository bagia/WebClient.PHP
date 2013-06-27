<?php
/**
 * Webclient
 *
 * Helper class to browse the web
 *
 * @author bagia
 */

class WebClient
{
	private $ch;
	
	private $url;
	private $maxRedirs;
	private $currentRedirs;

	private $headers;
	private $cookie;
	private $referer;
	private $html;
	
	public function Navigate($url, $post = array()) 
	{
		if ($this->currentRedirs > $this->maxRedirs)
			 trigger_error('Too many redirects.', E_USER_WARNING);
			 
		$this->url = $url;
		
		curl_setopt($this->ch, CURLOPT_URL, $url);
		if (!empty($this->referer))
			curl_setopt($this->ch, CURLOPT_REFERER, $this->referer);
		curl_setopt($this->ch, CURLOPT_COOKIE, $this->cookie);
		if (!empty($post)) {
			curl_setopt($this->ch, CURLOPT_POST, TRUE);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
		} else {
			curl_setopt($this->ch, CURLOPT_HTTPGET, TRUE);
		}
		
		$response = $this->exec();
		
		// Moved
		if ($this->currentRedirs <= $this->maxRedirs 
			&& ($response['Code'] == 301 || $response['Code'] == 302)) {
			$url = $this->getHeader('Location');
			if ( !is_null($url) ) {
				if (stripos($url, 'http') !== 0) {
					if (stripos($url, '/') !== 0)
						$url = '/' . $url;
						
					$url = $this->getRootUrl() . $url;
				}
				
				$this->currentRedirs++;
				return $this->Navigate($url);
			}
			
		}
		
		// We arrived to destination (hopefully).
		$this->currentRedirs = 0;
		
		if ($response['Code'] !== 200)
			return FALSE;
			
		return $response['Html'];
	}
	
	public function getInputs() 
	{
		if (empty($this->html))
			return array();
	
		$return = array();
	
		$dom = new DOMDocument();
		@$dom->loadHtml($this->html);
		$inputs = $dom->getElementsByTagName('input');
		foreach($inputs as $input)
		{
			if ($input->hasAttributes() && $input->attributes->getNamedItem('name') !== NULL)
			{
				if ($input->attributes->getNamedItem('value') !== NULL)
					$return[$input->attributes->getNamedItem('name')->value] = $input->attributes->getNamedItem('value')->value;
				else
					$return[$input->attributes->getNamedItem('name')->value] = NULL;
			}
		}
		
		return $return;
	}
	
	public function getHeader($name)
	{
		$headers = $this->getHeaders();
		if ( isset($headers[$name]) )
			return $headers[$name];
			
		return NULL;
	}
	
	public function __construct()
	{
		$this->maxRedirs = 5;
		$this->currentRedirs = 0;
		$this->url = '';
		$this->headers = array();
		$this->cookie = '';
		$this->referer = '';
		$this->html = '';
		
		$this->init();
	}
	
	public function __destruct()
	{
		$this->close();
	}
	
	private function init() 
	{
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/6.0 (Windows NT 6.2; WOW64; rv:16.0.1) Gecko/20121011 Firefox/16.0.1");
		curl_setopt($this->ch, CURLINFO_HEADER_OUT, TRUE);
		curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
		curl_setopt($this->ch, CURLOPT_FORBID_REUSE, FALSE);
	}

	private function exec() 
	{
		// Reset member variables
		$this->html = '';
		$this->headers = array();
	
		$headers = array();
		$html = '';
		
		ob_start();
		curl_exec($this->ch);
		$output = ob_get_contents();
		ob_end_clean();	
		
		$retcode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		
		// Separate Headers and HTML
		$split = explode("\r\n\r\n", $output, 2);
		if (count($split) > 1)
			$html = trim($split[1]);
		$h = trim($split[0]);
		$lines = explode("\n", $h);
		foreach($lines as $line) {
			$kv = explode(':', $line, 2);
			$k = trim($kv[0]);
			$v = '';
			if (count($kv) > 1)
				$v = trim($kv[1]);
			$headers[$k] = $v;
		}
		
		$this->referer = $this->url;
		
		// Set cookie
		if (!empty($headers['Set-Cookie']))
			$this->cookie = $headers['Set-Cookie'];
			
		// Set member variables
		$this->html = $html;
		$this->headers = $headers;

		return array('Code' => $retcode, 'Headers' => $headers, 'Html' => $html);
	}
	
	private function close()
	{
		curl_close($this->ch);
	}
	
	//
	// Some helpers
	//
	
	public function getHtml() 
	{
		return $this->html;
	}
	
	public function getUrl() 
	{
		return $this->url;
	}
	
	public function setMaxRedirs($maxRedirs = 5) {
		$this->maxRedirs = $maxRedirs;
	}
	
	public function getHeaders() 
	{
		return $this->headers;
	}
	
	public function getRootUrl() 
	{
		$user = $this->urlUser();
		if (!empty($user)) {
			$pass = $this->urlPass();
			if (!empty($pass))
				$user .= ':' . $pass;
				
			$user .= '@';
		}
		
		$port = $this->urlPort();
		if (!empty($port))
			$port = ':' . $port;
		
		return $this->urlScheme() . '://' . $user . $this->urlHost() . $port;
	}
	
	public function urlScheme()
	{
		return parse_url($this->url, PHP_URL_SCHEME);
	}
	
	public function urlHost()
	{
		return parse_url($this->url,  PHP_URL_HOST);
	}
	
	public function urlPort()
	{
		return parse_url($this->url,  PHP_URL_PORT);
	}
	
	public function urlUser()
	{
		return parse_url($this->url,  PHP_URL_USER);
	}
	
	public function urlPass()
	{
		return parse_url($this->url,  PHP_URL_PASS);
	}
	
	public function urlPath()
	{
		return parse_url($this->url,  PHP_URL_PATH);
	}
	
	public function urlQuery()
	{
		return parse_url($this->url,  PHP_URL_QUERY);
	}
	
	public function urlFragment()
	{
		return parse_url($this->url,  PHP_URL_FRAGMENT);
	}
}