<?php

namespace Charcoal\Tests\Validator;

use DateTime;
use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Validator\ValidatorResult;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Mock\ValidatorClass;
use Charcoal\Tests\Mock\ValidatableClass;

/**
 *
 */
class ValidatorTest extends AbstractTestCase
{
    /**
     * @var ValidatorClass
     */
    public $obj;

    /**
     * @var ValidatableClass
     */
    public $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->model = new ValidatableClass();
        $this->obj   = new ValidatorClass($this->model);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Validator\AbstractValidator', $obj);
    }

    /**
     * @return void
     */
    public function testError()
    {
        $obj = $this->obj;
        $ret = $obj->error('foo');
        $this->assertSame($ret, $obj);
        // var_dump($obj->errorResults());
    }

    /**
     * @return void
     */
    public function testWarning()
    {
        $obj = $this->obj;
        $ret = $obj->warning('foo');
        $this->assertSame($ret, $obj);
        // var_dump($obj->warningResults());
    }

    /**
     * @return void
     */
    public function testNotice()
    {
        $obj = $this->obj;
        $ret = $obj->notice('foo');
        $this->assertSame($ret, $obj);
        // var_dump($obj->noticeResults());
    }

    /**
     * @return void
     */
    public function testAddResult()
    {
        $result = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::ERROR,
            'message' => 'foo',
        ];

        $obj = $this->obj;
        $ret = $obj->addResult($result);
        $this->assertSame($ret, $obj);

        $resultObj = new ValidatorResult($result);
        $ret = $obj->addResult($resultObj);
        $this->assertSame($ret, $obj);

        $this->expectException(InvalidArgumentException::class);
        $obj->addResult(false);
    }

    /**
     * @group time-sensitive
     * @return void
     */
    public function testResults()
    {
        $result = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::ERROR,
            'message' => 'foo',
        ];

        $obj = $this->obj;
        $this->assertEquals([], $obj->results());

        $obj->addResult($result);

        $expectedResult = new ValidatorResult($result);
        $expectedResult->setTs($expectedResult->ts()->format(DateTime::ATOM));

        $actualResult = $obj->results();
        $actualResult[ValidatorClass::ERROR][0]->setTs(
            $actualResult[ValidatorClass::ERROR][0]->ts()->format(DateTime::ATOM)
        );

        $this->assertEquals([ ValidatorClass::ERROR => [ $expectedResult ] ], $actualResult);
    }

    /**
     * @return void
     */
    public function testErrorResults()
    {
        $result1 = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::ERROR,
            'message' => 'foo',
        ];
        $result2 = [
            'ident'   => 'foo',
            'level'   => ValidatorClass::NOTICE,
            'message' => 'bar',
        ];

        $obj = $this->obj;
        $this->assertEquals([], $obj->errorResults());

        $obj->addResult($result1);
        $obj->addResult($result2);

        $expectedResult = new ValidatorResult($result1);
        $expectedResult->setTs($expectedResult->ts()->format(DateTime::ATOM));

        $actualResult = $obj->errorResults();
        $actualResult[0]->setTs(
            $actualResult[0]->ts()->format(DateTime::ATOM)
        );

        $this->assertEquals([ $expectedResult ], $actualResult);
    }

    /**
     * @return void
     */
    public function testWarningResults()
    {
        $result1 = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::WARNING,
            'message' => 'foo',
        ];
        $result2 = [
            'ident'   => 'foo',
            'level'   => ValidatorClass::NOTICE,
            'message' => 'bar',
        ];

        $obj = $this->obj;
        $this->assertEquals([], $obj->warningResults());

        $obj->addResult($result1);
        $obj->addResult($result2);

        $expectedResult = new ValidatorResult($result1);
        $expectedResult->setTs($expectedResult->ts()->format(DateTime::ATOM));

        $actualResult = $obj->warningResults();
        $actualResult[0]->setTs(
            $actualResult[0]->ts()->format(DateTime::ATOM)
        );

        $this->assertEquals([ $expectedResult ], $actualResult);
    }

    /**
     * @return void
     */
    public function testNoticeResults()
    {
        $result1 = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::NOTICE,
            'message' => 'foo',
        ];
        $result2 = [
            'ident'   => 'foo',
            'level'   => ValidatorClass::ERROR,
            'message' => 'bar',
        ];

        $obj = $this->obj;
        $this->assertEquals([], $obj->noticeResults());

        $obj->addResult($result1);
        $obj->addResult($result2);

        $expectedResult = new ValidatorResult($result1);
        $expectedResult->setTs($expectedResult->ts()->format(DateTime::ATOM));

        $actualResult = $obj->noticeResults();
        $actualResult[0]->setTs(
            $actualResult[0]->ts()->format(DateTime::ATOM)
        );

        $this->assertEquals([ $expectedResult ], $actualResult);
    }

    /**
     * @return void
     */
    public function testMerge()
    {
        $result1 = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::NOTICE,
            'message' => 'foo',
        ];
        $result2 = [
            'ident'   => 'foo',
            'level'   => ValidatorClass::ERROR,
            'message' => 'bar',
        ];

        $resultObj1 = new ValidatorResult($result1);
        $resultObj2 = new ValidatorResult($result2);

        $obj1 = $this->obj;
        $obj2 = new ValidatorClass($this->model);

        $obj1->addResult($result1);
        $obj2->addResult($result2);
        $obj1->merge($obj2);

        $resultObj1->setTs($resultObj1->ts()->format(DateTime::ATOM));
        $resultObj2->setTs($resultObj2->ts()->format(DateTime::ATOM));

        $actualResult = $obj1->results();
        foreach ($actualResult as $resultCategory => $results) {
            foreach ($results as $i => $result) {
                $actualResult[$resultCategory][$i]->setTs(
                    $result->ts()->format(DateTime::ATOM)
                );
            }
        }

        $this->assertEquals(
            [
                ValidatorClass::NOTICE => [ $resultObj1 ],
                ValidatorClass::ERROR  => [ $resultObj2 ],
            ],
            $actualResult
        );
    }
}
