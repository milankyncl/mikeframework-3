<?php
/**
 * Mike Framework
 * Copyright (c) 2016 http://mikeframework.com
 * -----------------------------------------------------------------
 * Mike (PHP Framework) is an open source framework for PHP language
 * developers based on local files and folders implementation.
 * ------------------------------------------------------------------
 * @author Milan Kyncl <kyncl@kyro.cz>
 * @version Release: 4.0
 * @licence MIT Licence
 * @copyright  2016 (c) KyRo
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace Postmix\Core;

use Postmix\Info;


class Debugger {

	private $listening = false;

	public function ErrorHandler($errno, $errstr, $errfile, $errline) {

		switch ($errno) {
			case E_ERROR:
				self::Exception($errstr, $errno, $errfile, $errline, 'ERROR', 1);
				break;

			case E_WARNING:
				self::Exception($errstr, $errno, $errfile, $errline, 'WARNING', 2);
				break;

			case E_NOTICE:
				self::Exception($errstr, $errno, $errfile, $errline, 'NOTICE', 2);
				break;

			case E_PARSE:
				self::Exception($errstr, $errno, $errfile, $errline, 'PARSE', 1);
				break;

			default:
				self::Exception($errstr, $errno, $errfile, $errline, $errno, 1);
				break;
		}


		return true;
	}

	public function listen() {

		set_error_handler([$this, 'ErrorHandler'], E_ALL);
	}


	private static $debugg_variables = [];

	private static $exclude = 0;


	protected function Exception( $message, $num = null, $file = null, $line = null, $error_type = 'Exception') {

		ob_end_clean();

		$dbt = debug_backtrace();

		if($num != null && $file != null && $line != null) {

			$inserted = [
				'file' => $file,
				'line' => $line,
				'num' => $num
			];

			array_splice($dbt, 1, 0, [$inserted]);
		}

		for($i = 0; $i < self::$exclude+1; $i++ ) {

			if(isset($dbt[$i]))
				unset($dbt[$i]);
		}

		?>
		<html>
		<head>
			<title><?=$error_type. ': ' .$message?></title>
			<style>

				html{
					font-family: 'Verdana', sans-serif;
					font-size: 13px;
					margin-top: 0;
					padding-top: 0;
				}

				.container {
					max-width:1200px;
					margin:auto;
				}

				.head{
					padding:5px 20px;
					background: #6e1c59;
					-webkit-border-radius: 4px;
					-moz-border-radius: 4px;
					border-radius: 4px;
					min-height: 100px;
					padding-bottom: 15px;
				}

				.head h1{
					color:white;
					font-size:19px;
					line-height: 25px;
					text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
					font-weight: 500;
				}

				.head h1 > small{
					font-weight: 200;
					font-size: 14px;
					color: #eee;
				}

				.head h2{
					color:#ccc;
					font-size:14px;
					text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
					font-weight:lighter;
					padding: 0;
					margin: 0;
				}

				.head .header-footer{
					margin-top: 5px;
				}

				.head .header-footer h2{
					padding-top: 20px;
					text-align: right;
				}

				.head a{
					color: #ccc;
				}

				.debugg-content{
					margin:auto;
					margin-top: 20px;
					width:100%;
					border:1px solid #DDD;
					-webkit-border-radius: 4px;
					-moz-border-radius: 4px;
					border-radius: 4px;
				}

				.debugg-content .header{
					background:#DDD;
					padding:6px;
					padding-bottom: 0;
					-webkit-border-radius: 4px;
					-moz-border-radius: 4px;
					border-radius: 4px;
					margin:4px;
					font-size: 14px;
					color: #333;
					margin-bottom:20px;
				}

				.tab{
					display: none;
				}

				.tab > .tab-content{
					padding: 20px;
					-webkit-border-radius: 4px;
					-moz-border-radius: 4px;
					border-radius: 4px;
					margin:4px;
				}

				.tabs-nav{
					width: 100%;
					margin-bottom: 0;
					margin-top: 0;
					padding-left: 15px;
					width: 75%;
					display:inline-block;
				}

				.tabs-nav > li{
					display: inline-block;
					list-style: none;
					margin-right: 5px;
				}

				.tabs-nav > li > a {
					font-size: 13px;
					display: block;
					padding: 7px 10px;
					border-top-left-radius: 4px;
					border-top-right-radius: 4px;
					text-decoration: none;

					background: #efefef;
					border:1px solid #efefef;
					border-bottom: 1px solid #ddd;
					color: #777;

					font-weight: 200;
				}

				.tabs-nav > li > a.active {
					color: #333;
					border-color: #fff;
					background: #fff;
				}

				.debugg-content .header .ver{
					float:right;
					color:#666;
					line-height:20px;
					font-size:11px;
				}

				.debugg-content .overlay{
					overflow-x: scroll;
					max-height: 200px;
					max-width: 100%;
					margin: 20px;
					-webkit-border-radius: 4px;
					-moz-border-radius: 4px;
					border-radius: 4px;
					margin-bottom:30px;
				}

				.debugg-content .code{
					background:#ddd;
				}

				.debug-content .code td{
					vertical-align: top;
					line-height: 25px;
				}

				.debugg-content .fileinfo{
					width:97%;
					margin:auto;
					color:#555;
					font-size:14px;
					font-weight:lighter;
					margin-bottom:5px;
				}

				.debugg-content .fileinfo span{
					color:#999;
					font-size:12px;
				}

				table td{
					font-size:12px;
					line-height: 25px;
					vertical-align: top;
				}

				table td.line-numbers{
					background: #333;
					text-align: center;
					color: white;
					font-weight: bold;
					padding-left: 10px;
					padding-right: 10px;
				}

				.footer{
					color: #333;
					padding:10px;
					text-align: center;
					font-size:11px;
				}

				table td .code-pack{
					overflow-y: scroll;
					max-width: 100%;
				}

				.active-line{
					background: #111!important;
				}

				.active-line *{
					color: white!important;
				}

				table td.code-td{
					padding-left: 0;
				}

				.tab > .tab-content,
				.tab > .tab-content table td{
					background: #f1f1f1;
				}

				.tab > .tab-content table td .list-number{
					padding: 8px;
					background: #333;
					-webkit-border-radius: 4px;
					-moz-border-radius: 4px;
					border-radius: 4px;
					color: white;
					font-weight: bold;
				}

				.tab > .tab-content table tr > td{
					padding: 0;
				}

				.tab > .tab-content table tr > td:first-child{
					max-width: 80px;
				}

				pre{
					margin-top: 0;
					padding-top: 1px;
				}

				code{
					padding: 0!important;
				}
			</style>
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/highlight.js/9.6.0/styles/monokai.min.css">
		</head>
		<body>
		<div class="container">
			<div class="head">
				<h1><?php if(isset($dbt[2]['class'])) echo '\\'.$dbt[2]['class'].'::';
					echo ''.$error_type.'['.$num.'] : '.$message; ?></h1>

				<?php if($num != 'Unknown') {?>
					<div class="header-footer">
						<a href="http://github.com/milankyncl/postmix-framework">Documentation - Framework</a>
						<h2><?php if($file == null && $line == null){
								foreach($dbt as $i => $single){
									if(isset($single['file']) && isset($single['line'])) {
										$index = $i;
										break;
									}
								}
								echo $dbt[$index]['file'].' ('.$dbt[$index]['line'].')';
							} else{
								echo $file .' ('.$line.')';
							}
							?></h2>
					</div>
				<?php } ?>
			</div>

			<div class="debugg-content">
				<div class="header">
					<ul class="tabs-nav">
						<li><a href="#" id="link-backtrace" class="active" onclick="tab('backtrace')">Backtrace</a></li>
						<li><a href="#" id="link-database" onclick="tab('database')">Database</a></li>
						<li><a href="#" id="link-variables" onclick="tab('variables')">Variables</a></li>
					</ul>
					<span class="ver"><?=self::$version?></span>
				</div>
				<div class="tab" id="tab-backtrace" style="display: block;">
					<?php
					$c = 0;
					foreach($dbt as $trace_item){
						if(isset($trace_item['file'])) {
							$c++;

							if (strpos($trace_item['file'], '/library/View.php(120) : eval()\'d code') !== false) {
								$trace_item['file'] = APP_PATH . '/modules/' . ucfirst(\Mike::$moduleName). 'Module/views/' . \Mike\View::$viewUrl . '.phtml';
							} else if (strpos($trace_item['file'], '/library/View.php(141) : eval()\'d code') !== false) {
								$trace_item['file'] = APP_PATH . '/modules/' . ucfirst(\Mike::$moduleName). 'Module/layouts/' . \Mike\View::$layoutName . '.phtml';
							}
							?>
							<div class="fileinfo">
								<span>#<?php echo $c ?></span>
								<?php echo $trace_item['file'] . ' (' . $trace_item['line'] . ')' ?>
							</div>
							<div id="file-<?php echo $c ?>" class="overlay">
								<table class="code" cellpadding="2" cellspacing="0" width="100%">
									<td class="line-numbers">
										<?php
										if (isset($trace_item['file']) and isset($trace_item['line'])) {
											$f = fopen($trace_item['file'], 'r');
											$n = 0;
											$code = '';
											while(!feof($f)){
												$n++;
												echo $n . '<br>';
												$line = fgets($f);
												if($n == $trace_item['line']) $code .= '<div class="active-line">';
												$code .= htmlspecialchars($line);
												if($n == $trace_item['line']) $code .= '</div>';
											}
											echo '</td><td class="code-td"><div class="code-pack"><pre><code class="html php">'.$code.'</code></pre>';
											fclose($f);
										}
										echo '</div>';
										?>
									</td>
								</table>
							</div>
							<script type="text/javascript">
	                            var tabs = [
	                                'variables',
	                                'database',
	                                'backtrace'
	                            ];
	                            var table = document.getElementById('file-<?=$c?>');
	                            var topOffset = 25 * <?=($trace_item['line'] - 4)?>;
	                            table.scrollTop = topOffset;

	                            function tab( id ){
	                                tabs.forEach(function(value, index){
	                                    document.getElementById('tab-'+value).style="";
	                                    document.getElementById('link-'+value).className='';
	                                });
	                                document.getElementById('tab-'+id).style="display: block";
	                                document.getElementById('link-'+id).className='active';
	                            }
							</script>
							<?php
						}
					}
					?>
				</div>

				<div class="tab" id="tab-variables">
					<div class="tab-content">
						<table cellpadding="2" cellspacing="0" width="100%">
							<?php foreach(self::$debugg_variables as $i => $var){?>
								<tr>
									<td><span class="list-number">#<?=$i?></span></td>
									<td width="100%"><pre style="padding-left: 20px;"><?php
											if(is_array($var))
												print_r($var);
											else if(is_int($var) || is_string($var))
												echo $var;
											else if(is_bool($var)){
												if($var) echo 'true'; else echo 'false';
											}
											?></pre>
									</td>
								</tr>
							<?php }?>
						</table>
					</div>
				</div>

				<div class="tab" id="tab-database">
					<div class="tab-content">

						<!-- Database queries here ? -->

					</div>
				</div>
			</div>
			<div class="footer">
				&copy; Milan Kyncl 2018 &copy; <?= Info::FRAMEWORK_VERSION ?>
			</div>
		</div>
		<!--script src="https://cdn.jsdelivr.net/g/jquery@3.1.1,highlight.js@9.6.0(highlight.min.js+languages/css.min.js+languages/javascript.min.js+languages/php.min.js+languages/sql.min.js)"></script>
		<script>hljs.initHighlightingOnLoad();</script-->
		</body>
		</html>

		<?php
		die();
	}

}