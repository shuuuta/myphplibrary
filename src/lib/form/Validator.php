<?php
/**
 * Form Validator
 *
 * フォームの妥当性を調査。
 *
 */
namespace lib\Form;

class Validator
{
    /** @var array $errors エラーメッセージを格納 */
    private $errors;

    /**
    * コンストラクタ
    *
    * 内部エンコードの設定と、$_GET, $POST, $_COOKIE全体の
    * エンコードチェックとNullチェック。
    *
    * @param string $encoding
    */
    public function __construct(string $encoding = 'UTF-8')
    {
        $this->errors = [];
        mb_internal_encoding($encoding);

        $this->checkEncoding($_GET);
        $this->checkEncoding($_POST);
        $this->checkEncoding($_COOKIE);
    }

    /**
    * エンコードチェック
    *
    * 配列内の要素のエンコードをチェック。
    * 特に_GET, $POST, $_COOKIEで使用。
    *
    * @access private
    * @param array $data チェックするデータの配列
    */
    private function checkEncoding(array $data)
    {
        foreach ($data as $key => $value) :
            if (!mb_check_encoding($value)) :
                if (!array_key_exists('encoding', $this->errors)) {
                    $this->errors['encoding'] = [];
                }
                $this->errors['encoding'][] = $key;
            endif;
        endforeach;
    }

    /**
    * Nullチェック
    *
    * Nullバイト攻撃対策
    * 配列内の要素にNull文字が使用されていないかチェック。
    * 特に_GET, $POST, $_COOKIEで使用。
    *
    * @access private
    * @param array $data チェックするデータの配列
    */
    private function checkNull(array $data)
    {
        foreach ($data as $key => $value) :
            if (preg_match('/\0/', $value)) :
                if (!array_key_exists('null', $this->errors)) {
                    $this->errors['null'] = [];
                }
                $this->errors['null'][] = $key;
            endif;
        endforeach;
    }

    /**
    * 必須チェック
    *
    * @access public
    * @param string $value チェック対象
    * @param string $name  エラー管理用の名前
    * @return bool 結果の成否
    */
    public function requiredCheck(string $value, string $name)
    {
        if (trim($value) === '') :
            if (!array_key_exists('require', $this->errors)) {
                $this->errors['require'] = [];
            }
            $this->errors['require'][] = $name;
            return false;
        endif;
        return true;
    }

    /**
    * 文字数制限チェック
    *
    * @access public
    * @param string $value チェック対象
    * @param string $name  エラー管理用の名前
    * @param int $max 最大値
    * @param int $min 最小値
    * @return bool 結果の成否
    */
    public function lengthCheck(string $value, string $name, int $max, int $min = 0)
    {
        if ($min > $max) {
            die('文字数の最小値に対して最大値が小さすぎます。');
        }

        if (mb_strlen($value) < $min || mb_strlen($value) > $max) :
            if (!array_key_exists('length', $this->errors)) {
                $this->errors['length'] = [];
            }
            $this->errors['length'][] = $name;
            return false;
        endif;

        return true;
    }

    /**
    * 数値型チェック
    *
    * @access public
    * @param string $value チェック対象
    * @param string $name  エラー管理用の名前
    * @return bool 結果の成否
    */
    public function intTypeCheck(string $value, string $name)
    {
        if (!ctype_digit($value)) :
            if (!array_key_exists('intType', $this->errors)) {
                $this->errors['intType'] = [];
            }
            $this->errors['intType'][] = $name;
            return false;
        endif;
        return true;
    }

    /**
    * 数値の範囲チェック
    *
    * @access public
    * @param string $value チェック対象
    * @param string $name  エラー管理用の名前
    * @param int $max 最大値
    * @param int $min 最小値
    * @return bool 結果の成否
    */
    public function rangeCheck($value, string $name, int $max, int $min = 0)
    {
        if ($min > $max) {
            die('文字数の最小値に対して最大値が小さすぎます。');
        }

        if ($value < $min || $value > $max) :
            if (!array_key_exists('range', $this->errors)) {
                $this->errors['range'] = [];
            }
            $this->errors['range'][] = $name;
            return false;
        endif;

        return true;
    }

    /**
    * 日付型チェック
    *
    * @access public
    * @param string $value チェック対象
    * @param string $name  エラー管理用の名前
    * @return bool 結果の成否
    */
    public function dateCheck(string $value, string $name)
    {
        $res = preg_split('|([/\-年月日])|u', $value);
        $res = array_values(array_filter($res, 'strlen'));

        if (count($res) !== 3 || !@checkdate($res[1], $res[2], $res[0])) :
            if (!array_key_exists('date', $this->errors)) {
                $this->errors['date'] = [];
            }
            $this->errors['date'][] = $name;
            return false;
        endif;

        return true;
    }

    /**
    * 正規表現チェック
    *
    * @access public
    * @param string $value    チェック対象
    * @param string $name     エラー管理用の名前
    * @param string $pattern  正規表現パターン
    * @return bool 結果の成否
    */
    public function regexCheck(string $value, string $name, string $pattern)
    {
        if (!preg_match($pattern, $value)) :
            if (!array_key_exists('regex', $this->errors)) {
                $this->errors['regex'] = [];
            }
            $this->errors['regex'][] = $name;
            return false;
        endif;
        return true;
    }

    /**
    * メールアドレスチェック
    *
    * @access public
    * @param string $value    チェック対象
    * @param string $name     エラー管理用の名前
    * @return bool 結果の成否
    */
    public function mailCheck(string $value, string $name)
    {
        $pattern = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+$/";
        if (!preg_match($pattern, $value)) :
            if (!array_key_exists('mail', $this->errors)) {
                $this->errors['mail'] = [];
            }
            $this->errors['mail'][] = $name;
            return false;
        endif;
        return true;
    }

    /**
    * 選択肢チェック
    *
    * チェックボックス等、値が選択肢から選ばれたものか。
    *
    * @access public
    * @param string $value    チェック対象
    * @param string $name     エラー管理用の名前
    * @param array  $option   選択肢の配列
    * @return bool 結果の成否
    */
    public function inArrayCheck(string $value, string $name, array $option)
    {
        if (!in_array($value, $option)) :
            if (!array_key_exists('inArray', $this->errors)) {
                $this->errors['inArray'] = [];
            }
            $this->errors['inArray'][] = $name;
            return false;
        endif;
        return true;
    }



    /**
    * Errorsを出力
    *
    * @return array エラー
    */
    public function getErrors()
    {
        return $this->errors;
    }
}
