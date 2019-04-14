<?php

namespace app\controllers;

use app\Atm\Atm;
use Money\Currency;
use Money\Money;
use Yii;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class AtmController extends Controller
{
    public function actionFill($id)
    {
        $atm = new Atm(['RUB']);

        if (!$atm->init($id)) {
            throw new NotFoundHttpException('atm not found');
        }

        $notes = Yii::$app->request->post();

        return $atm->fill($notes);
    }

    public function actionWithdrawal($id)
    {
        $atm = new Atm(['RUB']);

        if (!$atm->init($id)) {
            throw new NotFoundHttpException('atm not found');
        }

        $moneyData = Yii::$app->request->post();

        $money = new Money($moneyData['amount'], new Currency($moneyData['currency']));

        return $atm->withdrawal($money);
    }

    public function actionBalance($id)
    {
        $atm = new Atm(['RUB']);

        if (!$atm->init($id)) {
            throw new NotFoundHttpException('atm not found');
        }

        return $atm->getBalance();
    }

    public function actionNotes($id)
    {
        $atm = new Atm(['RUB']);

        if (!$atm->init($id)) {
            throw new NotFoundHttpException('atm not found');
        }

        return $atm->getNotes();
    }

    public function actionStatistics($id)
    {
        $atm = new Atm(['RUB']);

        if (!$atm->init($id)) {
            throw new NotFoundHttpException('atm not found');
        }

        return $atm->getStatistics();
    }


}