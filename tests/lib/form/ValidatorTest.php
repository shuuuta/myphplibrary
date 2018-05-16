<?php
namespace lib\Form;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testCheckEncoding()
    {
        $valid = new Validator();
        $refrection = new \ReflectionClass($valid);
        $method = $refrection->getMethod('checkEncoding');
        $method->setAccessible(true);

        // エンコードに問題がない場合
        $str = 'テスト文字列';

        $method->invoke($valid, [$str]);
        $value = $valid->getErrors();
        $this->assertEquals(0, count($value));

        // エンコードに問題がある場合
        $str = mb_convert_encoding($str, 'SJIS', 'UTF-8');

        $method->invoke($valid, [$str]);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['encoding']));
    }

    public function testCheckNull()
    {
        $valid = new Validator();
        $refrection = new \ReflectionClass($valid);
        $method = $refrection->getMethod('checkNull');
        $method->setAccessible(true);

        // Null文字が使用されていない場合
        $method->invoke($valid, ['test']);
        $value = $valid->getErrors();
        $this->assertEquals(0, count($value));

        // Null文字が使用されている場合
        $method->invoke($valid, ['test', "test\0test"]);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['null']));
    }

    public function testRequiredCheck()
    {
        $valid = new Validator();

        // 必須チェックOK
        $bool = $valid->requiredCheck('Test', 'name');

        $this->assertEquals(true, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(0, count($value));

        // 必須チェックNG
        $bool = $valid->requiredCheck(' ', 'name');

        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['require']));
    }

    public function testLengthCheck()
    {
        $valid = new Validator();

        // 文字数が妥当
        $bool = $valid->lengthCheck('範囲テスト', 'nameLen', 10, 5);
        $this->assertEquals(true, $bool);
        // 文字数が短すぎる
        $bool = $valid->lengthCheck('最小値テスト', 'nameMin', 10, 7);
        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['length']));

        // 最小値の引数を省略
        $bool = $valid->lengthCheck('最大値テスト', 'nameMax', 7);
        $this->assertEquals(true, $bool);
        // 文字数が長すぎる
        $bool = $valid->lengthCheck('最大値テスト', 'nameMax', 5);
        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(2, count($value['length']));
    }

    public function testIntTypeCheck()
    {
        $valid = new Validator();

        // 数値型チェックOK
        $bool = $valid->intTypeCheck(100, 'name');
        $this->assertEquals(true, $bool);
        // 数値型チェックNG
        $bool = $valid->intTypeCheck('test', 'name');
        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['intType']));
    }

    public function testRangeCheck()
    {
        $valid = new Validator();

        // 数値が妥当
        $bool = $valid->rangeCheck(100, 'nameRange', 100, 50);
        $this->assertEquals(true, $bool);
        // 数値が低すぎる
        $bool = $valid->rangeCheck(10, 'nameMin', 100, 50);
        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['range']));

        // 最小値の引数を省略
        $bool = $valid->rangeCheck(0, 'nameRange', 100);
        $this->assertEquals(true, $bool);
        // 数値が高すぎる
        $bool = $valid->rangeCheck(100, 'nameMax', 50);
        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(2, count($value['range']));
    }

    public function testDateCheck()
    {
        $valid = new Validator();

        // xxxx/xx/xxの形でチェック
        $bool = $valid->dateCheck('2018/05/15', 'date');
        $this->assertEquals(true, $bool);
        // xxxx-xx-xxの形でチェック
        $bool = $valid->dateCheck('2018-05-15', 'date');
        $this->assertEquals(true, $bool);
        // xxxx年xx月xx日の形でチェック
        $bool = $valid->dateCheck('2018年05月15日', 'date');
        $this->assertEquals(true, $bool);

        // 適当な文字列を入力
        $bool = $valid->dateCheck('test', 'date');
        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['date']));
    }

    public function testRegexCheck()
    {
        $valid = new Validator();

        // 正規表現チェックOK
        $bool = $valid->regexCheck('03-0000-0000', 'regex', '/\d{2}-\d{4}-\d{4}/');
        $this->assertEquals(true, $bool);
        // 正規表現チェックNG
        $bool = $valid->regexCheck('0000-0000', 'regex', '/\d{2}-\d{4}-\d{4}/');
        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['regex']));
    }

    public function testMailCheck()
    {
        $valid = new Validator();

        // メールチェックOK
        $bool = $valid->mailCheck('test@example.test', 'mail');
        $this->assertEquals(true, $bool);
        // メールチェックNG
        $bool = $valid->mailCheck('test@test', 'mail');
        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['mail']));
    }

    public function testInArrayCheck()
    {
        $valid = new Validator();
        // 選択肢チェックOK
        $bool = $valid->inArrayCheck('php', 'inArray', ['php', 'js', 'html', 'css' ]);
        $this->assertEquals(true, $bool);
        // 選択肢チェックNG
        $bool = $valid->inArrayCheck('java', 'inArray', ['php', 'js', 'html', 'css' ]);
        $this->assertEquals(false, $bool);
        $value = $valid->getErrors();
        $this->assertEquals(1, count($value['inArray']));
    }
}
