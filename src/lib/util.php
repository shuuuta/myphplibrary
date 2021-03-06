<?php
/**
* 独自関数群
*
*/

/**
* htmlエスケープ
*
* 文字列に含まれたhtmlタグやクォートをエスケープ。
*
* @param string $str エスケープする文字。
* @param string $charset 文字コード。デフォルトはUTF-8。
* @return string エスケープされた文字列。
*/
if (!function_exists('e')) {
    function e(string $str, string $charset = 'UTF-8'): string
    {
        return htmlspecialcars($str, ENT_QUOTES | ENT_HTML5, $carset);
    }
}
