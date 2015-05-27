<?php
/**
 * Simple Double Entry Accounting
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson, 2015, UK
 * @license GPL V3+ See LICENSE.md
 */
namespace chippyash\Test\Accounts;

use chippyash\Accounts\Journal;
use chippyash\Accounts\Nominal;
use chippyash\Accounts\Transaction;
use chippyash\Currency\Factory;
use chippyash\Type\Number\IntType;
use chippyash\Type\String\StringType;

class JournalTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Journal
     */
    protected $sut;

    /**
     * Mock
     * @var JournalStorageInterface
     */
    protected $journalist;

    /**
     * Mock
     * @var Chart
     */
    protected $chart;

    /**
     * @var Transaction
     */
    protected $transaction;

    protected function setUp()
    {
        $this->transaction = new Transaction(
            new Nominal('0000'),
            new Nominal('0001'),
            Factory::create('gbp', 12.26),
            new \DateTime()
        );

        $this->chart = $this->getMock('chippyash\Accounts\Chart', array(), array(), '', false);
        $this->journalist = $this->getMock('chippyash\Accounts\JournalStorageInterface');

        $this->sut = new Journal(new StringType('Foo Bar'), $this->chart, $this->journalist);
    }

    public function testWritingATransactionWillReturnTransactionWithIdSet()
    {
        //txn before the write
        $this->assertNull($this->transaction->getId());

        $this->journalist
            ->expects($this->once())
            ->method('writeTransaction')
            ->will($this->returnValue(new IntType(1)));
        $txn = $this->sut->write($this->transaction);

        //txn after the write
        $this->assertInstanceOf('chippyash\Accounts\Transaction', $txn);
        $this->assertInstanceOf('chippyash\Type\Number\IntType', $txn->getId());
        $this->assertEquals(1, $txn->getId()->get());
    }

    public function testReadingATransactionWillReturnATransaction()
    {
        $this->journalist
            ->expects($this->once())
            ->method('readTransaction')
            ->will($this->returnValue($this->transaction));
        $this->assertInstanceOf('chippyash\Accounts\Transaction', $this->sut->readTransaction(new IntType(1)));
    }

    public function testReadingTransactionsForAnAccountWillReturnAnArrayOfTransactions()
    {
        $ret = array($this->transaction, $this->transaction, $this->transaction);
        $this->journalist
            ->expects($this->once())
            ->method('readTransactions')
            ->will($this->returnValue($ret));
        $this->assertInternalType('array', $this->sut->readTransactions(new Nominal('0000')));;
    }

    public function testYouCanGetNameOfJournal()
    {
        $this->assertEquals('Foo Bar', $this->sut->getName()->get());
    }
}