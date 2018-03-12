<?php

namespace Postmix\Core;

use Postmix\Info;

/**
 * Class Debugger
 * @package Postmix\Core
 */

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
	        throw new \ErrorException($message, 500, $severity, $file, $line);
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
            <title><?= substr(strrchr(get_class($exception), "\\"), 1) . ' - ' . $exception->getMessage() ?></title>
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
                    background: #4464AD;
                    -webkit-border-radius: 4px;
                    -moz-border-radius: 4px;
                    border-radius: 4px;
                    min-height: 100px;
                    padding-bottom: 15px;
                }

                .head h1 {

                    color: white;
                    font-size: 18px;
                    line-height: 25px;
                    text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
                    font-weight: 400;
                }

                .head h1 > strong {

                    color: #efefef;
                }

                .head h1 > small {

                    display: block;
                    margin-top: 8px;
                    font-weight: 400;
                    font-size: 16px;
                    color: #ddd;
                    word-break: break-all;
                }

                .head h2 {
                    color:#ccc;
                    font-size:14px;
                    text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
                    font-weight:lighter;
                    padding: 0;
                    margin: 0;
                }

                .head .header-footer{

                    margin-top: 0;
                }

                .head .header-footer h2 {

                    font-size: 11px;
                    margin-top: 17px;
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
                    font-size:13px;
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

                    padding: 0;
                    display: block;
                    padding-left: 15px;
                }
            </style>
            <script>
                function codeScroll(id, lineNum) {

                    document.getElementById('file-' + id).scrollTop = 16 * (lineNum - 3);
                }
            </script>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/highlight.js/9.6.0/styles/tomorrow-night.min.css">
        </head>
        <body>
            <div class="container">
                <div class="head">
                    <h1>
                        <?php if($exception->getCode() > 0) : ?><strong>#<?= $exception->getCode() ?></strong><?php endif ?>
                        <?= get_class($exception) ?>
                        <small><?= $exception->getMessage() ?></small>
                    </h1>

                    <div class="header-footer">
                        <!--a href="http://github.com/milankyncl/postmix-framework">Documentation</a-->
                        <h2><?= $exception->getFile() . ' (' . $exception->getLine() . ')' ?></h2>
                    </div>
                </div>

                <div class="debugg-content">
                    <div class="header">
                        <ul class="tabs-nav">
                            <li><a href="#" id="link-backtrace" class="active" onclick="tab('backtrace'); return false">Backtrace</a></li>
                            <li><a href="#" id="link-database" onclick="tab('database'); return false">Database</a></li>
                            <li><a href="#" id="link-variables" onclick="tab('variables'); return false">Variables</a></li>
                        </ul>
                        <span class="ver">v<?= Info::FRAMEWORK_VERSION ?></span>
                    </div>
                    <div class="tab" id="tab-backtrace" style="display: block;">
                        <?php /*
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
                        <script>codeScroll(1, <?= $exception->getLine() ?>)</script>
                        <?php

                        */

                        foreach($exception->getTrace() as $k => $trace_item) {

                            if(isset($trace_item['file'])):

                            $k = $k + 1;

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

                                                echo '</td><td class="code-td"><pre><code class="html php">' . $code . '</code></pre>';

                                                fclose($f);

                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <script>codeScroll(<?= $k ?>, <?= $trace_item['line'] ?>)</script>
                            <?php

                            endif;
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
                    Milan Kyncl 2018 &copy; Postmix Framework v<?= Info::FRAMEWORK_VERSION ?>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/g/jquery@3.1.1,highlight.js@9.6.0(highlight.min.js+languages/css.min.js+languages/javascript.min.js+languages/php.min.js+languages/sql.min.js)"></script>
            <script>hljs.initHighlightingOnLoad();</script>
            <script type="text/javascript">
                var tabs = [
                    'variables',
                    'database',
                    'backtrace'
                ];

                function tab(id) {

                    tabs.forEach(function(value, index){

                        document.getElementById('tab-' + value).style = '';
                        document.getElementById('link-' + value).className = '';
                    });

                    document.getElementById('tab-' + id).style = 'display: block';
                    document.getElementById('link-' + id).className = 'active';
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