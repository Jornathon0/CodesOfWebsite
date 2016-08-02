<?php

/**
* @author Jornathon <lgj3089@163.com>
* @todo simple scraper
*/

class ScrapeController EXTENDS Controller
// class ScrapeController
{
	protected $config = array(
		'a1' => 'a1',
		'a2' => 'a2',
		'a3' => 'a3',
					);

	/**
	 * 获取配置内容
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	
	public function __construct($config = array()){
		$this->config = array_merge($this->config,$config);
	}
	public function __get($name){
		return $this->config[$name];
	}

	/**
	 * 设置配置信息
	 * @param [type] $name  [description]
	 * @param [type] $value [description]
	 */
	public function __set($name,$value){
		if(isset($this->config[$name])){
			$this->config[$name] = $value;
		}
	}

	/**
	 * excute scape
	 * @param str $url <discription:Controller + action>
	 * @return str $status 
	 */
	public function scape($url){
		if(file_get_contents($url)){
			$contents = file_get_contents($url);
			$this->readWeb($contents);
			$dir = $this->parseUrl($url);
			$ac_dir = dirname(__DIR__).DIRECTORY_SEPARATOR.$dir[$action];
			$this->createDir();
		}else{
			$contents = 'Couldn\'t get file from this website.';
		}
	}

	/**
	 * read the website && storage the controller and action
	 * @param str $contents 
	 */
	private function readWeb($contents){
		$pattern='/<a\s*(.*)href=(.*)(\'|")/i';
		preg_match_all($pattern, $contents, $matches);
	}

	private function parseUrl($url){
		$var = explode('/', $url);
		$contr = $var['3'];
		$action = empty($var['4'])?index:$action;
		$dir = array(
			'contro' => $contro,
			'action' => $action
			);
		return $dir;
	}
	/*
	 * 功能：循环检测并创建文件夹
	 * 参数：$path 文件夹路径
	 * 返回：
	 */
	private function createDir($path) {
	    if (!file_exists($path)) {
	        createDir(dirname($path));
	        mkdir($path, 0777);
	    }
	}

	/**
	 * make file
	 * @return str [<description>]
	 */
	private function createFile(){}

	/**
	 * make file of the controller
	 * @return  str [<description>]
	 */
	private function writerFile(){}

	/**
	 * modify the array
	 * @param str $contents [<description>]
	 * @return str $contents
	 */
	private function modifyCont($contents){
		return $contents;
	}

	public function __call($name, $value) {
		header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
		redirect('/404.html');
	}


}

$scraper = new Scrape();

$url = 'http://www.62.com/jg/wl';
$var = explode('/', $url);
var_dump($var);
