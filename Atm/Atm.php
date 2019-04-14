<?php

namespace app\Atm;

use Exception;
use Money\Currency;
use Money\Money;
use app\models\database;
use yii\db\ActiveRecord;


class Atm implements IAtm
{

    private const TYPE_UP = 'up';
    private const TYPE_DOWN = 'down';


    /**
     * @var string|null
     */
    private $currentCurrency;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $notes;

    public function __construct(array $currencies, string $currentCurrency = null)
    {
        $this->currentCurrency = $currentCurrency ?? reset($currencies);

        foreach ($currencies as $currency) {
            $this->notes[$currency] = [];
        }
    }

    public function init(int $id): bool
    {
        $this->id = $id;
        $res = $this->loadAtm();

        if (false === $res) {
            return false;
        }

        $this->loadNodes();

        return true;
    }

    /**
     * Загрузка наличных средств в банкомат
     * $notes = [
     *     // Номинал купюры => Количество
     *     100 => 25,
     *     5000 => 10,
     * ];
     */
    public function fill(array $notes): bool
    {
        $currentNotes = $this->getNotes();

        //validate

        $denominations = [];
        foreach ($notes as $denomination => $count) {
            $currentNotes[$denomination] = ($currentNotes[$denomination] ?? 0) + $count;
            $denominations[] = $denomination;
        }
        $this->setNotes($currentNotes);

        $this->saveToStore($denominations);

        $this->addOperationInfo($this->calcMoneyByNotes($notes), self::TYPE_UP);

        return true;
    }

    /**
     * Выдача наличных средств. Выдавать запрошенную сумму имеющимися купюрами,
     * отдавая приоритет более крупным банкнотам
     *
     */
    public function withdrawal(Money $amount): bool
    {
        if ($amount->greaterThan($this->getBalance())) {
            throw new Exception('NO MONEY');
        }

        $notes = $this->getNotes();

        $withdrawalNotes = [];

        foreach ($notes as $denomination => $count) {
            $needCount = (int)($amount->getAmount() / $denomination);

            $withdrawalCount = $count >= $needCount ? $needCount : $count;
            $amount = $amount->add(new Money(-$withdrawalCount * $denomination, $amount->getCurrency()));
            $withdrawalNotes[$denomination] = $withdrawalCount;
            $notes[$denomination] -= $withdrawalCount;

            if (!$amount->isPositive()) {
                break;
            }
        }

        if ($amount->isPositive()) {
            throw new Exception('CANT COMBINE');
        }

        $this->setNotes($notes);

        $this->saveToStore(array_keys($withdrawalNotes));

        $this->addOperationInfo($this->calcMoneyByNotes($withdrawalNotes), self::TYPE_DOWN);

        return true;
    }

    /**
     * Получение статистики
     * $statistics = [
     *     ['type' => 'up', 'amount' => Money(amount, currency), 'date' => DateTime(date)],
     *     ['type' => 'down', 'amount' => Money(amount, currency), 'date' => DateTime(date)],
     * ];
     */
    public function getStatistics(): array
    {
        $data = self::getStatisticModel()::find()
            ->where(['atm_id' => $this->id])
            ->where(['currency' => $this->getCurrentCurrency()])
            ->all();

        $result = [];
        foreach ($data as $datum) {
            $result[] = [
                'type' => $datum->type,
                'amount' => new Money($datum->amount, new Currency($datum->currency)),
                'date' => $datum->date,
            ];
        }

        return $result;
    }

    /**
     * Текущий баланс банкомата
     */
    public function getBalance(): Money
    {
        return $this->calcMoneyByNotes($this->getNotes());
    }

    /**
     * Получение информации о купюрах в кассете банкомата
     * $notes = [
     *     // Номинал купюры => Количество
     *     100 => 25,
     *     5000 => 10,
     * ];
     */
    public function getNotes(): array
    {
        return $this->notes[$this->getCurrentCurrency()];
    }

    protected function loadAtm(): bool
    {
        $modelAtm = database\AtmRecord::findOne(['id' => $this->id]);

        if (null === $modelAtm) {
            return false;
        }

        $this->name = $modelAtm->name;

        return true;
    }

    protected function loadNodes(): bool
    {
        $resources = $this->getResourcesRecords();

        foreach ($resources as $resource) {
            $currency = $resource->currency;
            if (!isset($this->notes[$currency])) {
                $this->notes[$currency] = [];
            }
            $this->notes[$currency][$resource->denomination] = $resource->count;
        }

        return true;
    }

    protected static function getResourceModel(array $config = []): ActiveRecord
    {
        return new database\ResourceRecord($config);
    }

    protected static function getStatisticModel(array $config = []): ActiveRecord
    {
        return new database\StatisticRecord($config);
    }

    protected function getResourcesRecords(string $currency = null, array $denominations = null): ?array
    {
        $query = self::getResourceModel()::find()
            ->where(['atm_id' => $this->id]);

        if (null !== $currency) {
            $query = $query
                ->andWhere(['currency' => $currency])
                ->indexBy('denomination');
        }

        if (is_array($denominations)) {
            $query = $query
                ->andWhere(['in', 'denomination', $denominations]);
        }
        return $query->orderBy(['denomination' => SORT_DESC])->all();
    }

    private function saveToStore(array $denominations): bool
    {
        $currency = $this->getCurrentCurrency();

        $existingResources = $this->getResourcesRecords($currency, $denominations);

        foreach ($this->getNotes() as $denomination => $count) {
            if (!isset($existingResources[$denomination])) {
                $resource = self::getResourceModel([
                    'atm_id' => $this->id,
                    'currency' => $currency,
                    'denomination' => $denomination,
                ]);
            } else {
                $resource = $existingResources[$denomination];
            }

            $resource->count = $count;

            $resource->save();
        }

        return true;
    }

    private function getCurrentCurrency(): string
    {
        return $this->currentCurrency;
    }

    private function calcMoneyByNotes(array $notes): Money
    {
        $money = new Money(0, new Currency($this->getCurrentCurrency()));

        foreach ($notes as $denomination => $count) {
            $money = $money->add(new Money($denomination * $count, $money->getCurrency()));
        }

        return $money;
    }

    private function setNotes(array $nodes, string $currency = null): void
    {
        $currency = $currency ?? $this->getCurrentCurrency();
        $this->notes[$currency] = $nodes;
    }

    private function addOperationInfo(Money $money, string $type): void
    {
        $info = self::getStatisticModel([
            'atm_id' => $this->id,
            'amount' => $money->getAmount(),
            'currency' => $money->getCurrency()->getCode(),
            'type' => $type,
        ]);

        $info->save();
    }

}