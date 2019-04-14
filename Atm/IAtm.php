<?php

namespace app\Atm;

use Money\Money;

/**
 * Написать код, который будет включать в себя класс, реализуюий интерфейс банкомата ATM.
 * Данные хранить в БД mysql. Для денег использовать библиотеку MoneyPHP. По возможности написать тесты.
 *
 * @property int $id_atm
 * @property string $currency
 * @property string $address
 */
interface IAtm
{

    /**
     * Загрузка наличных средств в банкомат
     * $notes = [
     *     // Номинал купюры => Количество
     *     100 => 25,
     *     5000 => 10,
     * ];
     */
    public function fill(array $notes): bool;

    /**
     * Выдача наличных средств. Выдавать запрошенную сумму имеющимися купюрами,
     * отдавая приоритет более крупным банкнотам
     *
     */
    public function withdrawal(Money $amount): bool;

    /**
     * Получение статистики
     * $statistics = [
     *     ['type' => 'up', 'amount' => Money(amount, currency), 'date' => DateTime(date)],
     *     ['type' => 'down', 'amount' => Money(amount, currency), 'date' => DateTime(date)],
     * ];
     */
    public function getStatistics(): array;

    /**
     * Текущий баланс банкомата
     */
    public function getBalance(): Money;

    /**
     * Получение информации о купюрах в кассете банкомата
     * $notes = [
     *     // Номинал купюры => Количество
     *     100 => 25,
     *     5000 => 10,
     * ];
     */
    public function getNotes(): array;

}