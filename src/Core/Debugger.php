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

	/**
     * Error handler
     *
	 * @param $severity
	 * @param $message
	 * @param $file
	 * @param $line
	 *
	 * @throws \ErrorException
	 */

	public function errorHandler($severity, $message, $file, $line) {

	    if(error_reporting() && $severity)
	        throw new \ErrorException($message, 0, $severity, $file, $line);
	}

	/**
     * Exception handler
     *
	 * @param \Exception $exception
	 */

	public function exceptionHandler(\Exception $exception) {

	    /**
         * Clean the buffer output
         */

	    if(ob_get_level())
            ob_end_clean();

		?>
        <html>
        <head>
            <title><?= $exception->getMessage() ?></title>
            <style type="text/css">

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

                .tab {

                    display: none;
                }

                .tab > .tab-content {

                    padding: 20px;
                    -webkit-border-radius: 4px;
                    -moz-border-radius: 4px;
                    border-radius: 4px;
                    margin:4px;
                }

                .tabs-nav {

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

                .debugg-content .header .ver {
                    float:right;
                    color:#666;
                    line-height:20px;
                    font-size:11px;
                }

                .debugg-content .overlay {
                    overflow-x: scroll;
                    max-height: 200px;
                    max-width: 100%;
                    margin: 20px;
                    -webkit-border-radius: 4px;
                    -moz-border-radius: 4px;
                    border-radius: 4px;
                    margin-bottom:30px;
                }

                .debugg-content .code {

                    background: #ddd;
                }

                .debug-content .code td {
                    vertical-align: top;
                }

                .debugg-content .fileinfo {
                    width:97%;
                    margin:auto;
                    color:#555;
                    font-size:14px;
                    font-weight:lighter;
                    margin-bottom:5px;
                }

                .debugg-content .fileinfo span {
                    color:#999;
                    font-size:12px;
                }

                table td {
                    font-size: 13px;
                    line-height: 1.325;
                    vertical-align: top;
                }

                table td.line-numbers {
                    background: #333;
                    text-align: right;
                    color: white;
                    font-weight: normal;
                    padding: 0 14px;
                    font-size: 11px;
                    line-height: 17px;
                }

                .footer {
                    color: #333;
                    padding: 10px;
                    text-align: center;
                    font-size: 11px;
                }

                .active-line {

                    background: #111!important;
                }

                .active-line * {

                    /*color: white!important;*/
                }

                table td.code-td {

                    padding: 0;
                    width: 100%;
                    background-color: #272822;
                }

                .tab > .tab-content,
                .tab > .tab-content table td {

                    background: #f1f1f1;
                }

                .tab > .tab-content table td .list-number {

                    padding: 8px;
                    background: #333;
                    -webkit-border-radius: 4px;
                    -moz-border-radius: 4px;
                    border-radius: 4px;
                    color: white;
                    font-weight: bold;
                }

                .tab > .tab-content table tr > td {
                    padding: 0;
                }

                .tab > .tab-content table tr > td:first-child {
                    max-width: 80px;
                }

                pre {
                    margin: 0;
                    padding: 0;
                }

                code {
                    padding: 0!important;
                }

                code > span,
                .active-line > span {

                    display: block;
                    padding-left: 15px;
                }
            </style>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/highlight.js/9.6.0/styles/monokai.min.css">
        </head>
        <body>
            <div class="container">
                <div class="head">
                    <h1><?= get_class($exception) . ' - ' . $exception->getMessage() ?></h1>

                    <div class="header-footer">
                        <a href="http://github.com/milankyncl/postmix-framework">Documentation - Framework</a>
                        <h2><?= $exception->getFile() . ' (' . $exception->getLine() . ')' ?></h2>
                    </div>
                </div>

                <div class="debugg-content">
                    <div class="header">
                        <ul class="tabs-nav">
                            <li><a href="#" id="link-backtrace" class="active" onclick="tab('backtrace')">Backtrace</a></li>
                            <li><a href="#" id="link-database" onclick="tab('database')">Database</a></li>
                            <li><a href="#" id="link-variables" onclick="tab('variables')">Variables</a></li>
                        </ul>
                        <span class="ver"><?= Info::FRAMEWORK_VERSION ?></span>
                    </div>
                    <div class="tab" id="tab-backtrace" style="display: block;">
                        <div class="fileinfo">
                            <span>#1</span>
                            <?= $exception->getFile() . ' (' . $exception->getLine() . ')' ?>
                        </div>
                        <div id="file-1" class="overlay">
                            <table class="code" cellpadding="2" cellspacing="0" width="100%">
                                <tr>
                                    <td class="line-numbers" width="auto">
                                        <?php

                                        $f = fopen($exception->getFile(), 'r');
                                        $n = 0;
                                        $code = '';

                                        while(!feof($f)){
                                            $n++;
                                            echo $n . '<br>';
                                            $line = fgets($f);
                                            if($n == $exception->getLine()) $code .= '<div class="active-line">';
                                            $code .= htmlspecialchars($line);
                                            if($n == $exception->getLine()) $code .= '</div>';
                                        }

                                        echo '</td><td class="code-td"><pre><code class="html php">'.$code.'</code></pre>';

                                        fclose($f);
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <script type="text/javascript">
                            var tabs = [
                                'variables',
                                'database',
                                'backtrace'
                            ];
                            var table = document.getElementById('file-0');
                            var topOffset = 16.75 * <?=($exception->getLine() - 4)?>;
                            table.scrollTop = topOffset;
                        </script>
                        <?php

                        foreach($exception->getTrace() as $k => $trace_item) {

                            $k = $k + 2;

                            ?>
                            <div class="fileinfo">
                                <span>#<?= $k ?></span>
                                <?php echo $trace_item['file'] . ' (' . $trace_item['line'] . ')' ?>
                            </div>
                            <div id="file-<?= $k ?>" class="overlay">
                                <table class="code" cellpadding="2" cellspacing="0" width="100%">
                                    <tr>
                                        <td class="line-numbers" width="auto">
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

                                                echo '</td><td class="code-td"><pre><code class="html php">'.$code.'</code></pre>';

                                                fclose($f);

                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <script type="text/javascript">
                                var tabs = [
                                    'variables',
                                    'database',
                                    'backtrace'
                                ];
                                var table = document.getElementById('file-<?=$k?>');
                                var topOffset = 16.75 * <?=($trace_item['line'] - 4)?>;
                                table.scrollTop = topOffset;
                            </script>
                            <?php
                        }
                        ?>
                    </div>

                    <div class="tab" id="tab-variables">
                        <div class="tab-content">

                        </div>
                    </div>

                    <div class="tab" id="tab-database">
                        <div class="tab-content">
                        </div>
                    </div>
                </div>
                <div class="footer">
                    &copy; Milan Kyncl 2018 &copy; <?= Info::FRAMEWORK_VERSION ?>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/g/jquery@3.1.1,highlight.js@9.6.0(highlight.min.js+languages/css.min.js+languages/javascript.min.js+languages/php.min.js+languages/sql.min.js)"></script>
            <script>hljs.initHighlightingOnLoad();</script>
            <script type="text/javascript">
                function tab( id ){
                    tabs.forEach(function(value, index){
                        document.getElementById('tab-'+value).style="";
                        document.getElementById('link-'+value).className='';
                    });
                    document.getElementById('tab-'+id).style="display: block";
                    document.getElementById('link-'+id).className='active';
                }
            </script>
        </body>
        </html>

		<?php
    }

	/**
	 * Listen to exceptions and errors
	 */

	public function listen() {

		set_error_handler([$this, 'ErrorHandler'], E_ALL);

		set_exception_handler([$this, 'ExceptionHandler']);
	}
}