<?php

namespace FintechFab\QiwiGate\Controllers;

use Controller;
use FintechFab\QiwiGate\Components\Refunds;
use FintechFab\QiwiGate\Components\Validators;
use FintechFab\QiwiGate\Models\Bill;
use FintechFab\QiwiGate\Models\Refund;
use Input;
use Response;
use Validator;

class RestRefundController extends Controller
{

	/**
	 * Возврат средств
	 *
	 * @param int    $provider_id
	 * @param string $bill_id
	 * @param int    $refund_id
	 *
	 * @internal param int $id
	 *
	 * @return Response
	 */
	public function update($provider_id, $bill_id, $refund_id)
	{
		//Проверяем наличие счёта
		$bill = Bill::whereBillId($bill_id)
			->whereMerchantId($provider_id)
			->first();
		if ($bill == null) {
			$data['error'] = 210;
			$codeResponse = 404;

			return $this->responseFromGate($data, $codeResponse);
		}

		//Проверяем refund_id на уникальность в этом счёте
		$existRefund = Refund::whereBillId($bill_id)
			->whereRefundId($refund_id)->first();
		if ($existRefund != null) {
			$data['error'] = 215;
			$codeResponse = 403;

			return $this->responseFromGate($data, $codeResponse);
		}

		//Проверяем статус
		if ($bill->status != 'paid') {
			$data['error'] = 210;
			$codeResponse = 403;

			return $this->responseFromGate($data, $codeResponse);
		}

		//Берём общую сумму счёта
		$amountBill = $bill->amount;
		//Проверяем запрошенную сумму для отмены
		$amountQuery = Input::get('amount');
		$validator = Validator::make(array('amount' => $amountQuery), Validators::rulesForRefundBill());

		if (!$validator->passes()) {
			$data['error'] = 5;
			$codeResponse = 400;

			return $this->responseFromGate($data, $codeResponse);
		}
		//Берём суммы прошлых возвратов
		$refundsBefore = Refund::whereBillId($bill_id)->get();
		$amount_refund = 0;
		foreach ($refundsBefore as $one) {
			$amount_refund += $one->amount;
		}
		//Вычисляем возможную сумму возвтрата и определяем возврат
		$rest = $amountBill - $amount_refund;
		$amount = $amountQuery > $rest ? $rest : $amountQuery;

		if ($amount <= 0) {
			$data['error'] = 'Unknown error';
			$codeResponse = 403;

			return $this->responseFromGate($data, $codeResponse);
		}

		$data = array(
			'bill_id'   => $bill_id,
			'refund_id' => $refund_id,
			'amount'    => $amount,
			'status'    => 'processing',
		);
		$refund = Refunds::NewRefund($data);
		$data['user'] = $refund->bill->user;
		$data['error'] = 0;
		if ($amount_refund + $amount == $amountBill) {
			$bill->status = 'rejected';
			$bill->save();
		}

		return $this->responseFromGate($data);

	}

	/**
	 * Проверка статуса возврата
	 *
	 * @param int    $provider_id
	 * @param string $bill_id
	 * @param int    $refund_id
	 *
	 * @internal param int $id
	 *
	 * @return Response
	 */
	public function show($provider_id, $bill_id, $refund_id)
	{

		$bill = Bill::whereBillId($bill_id)
			->whereMerchantId($provider_id)
			->first();

		if (!$bill) {
			$data['error'] = 210;
			$code_response = 404;

			return $this->responseFromGate($data, $code_response);
		}

		$refund = Refund::whereBillId($bill_id)
			->whereRefundId($refund_id)->first();

		if ($refund == null) {
			$data['error'] = 210;
			$code_response = 404;

			return $this->responseFromGate($data, $code_response);
		}

		//Проводим платёж по прошествии минуты
		if ($refund->status == 'processing') {
			$date = date('Y-m-d H:i:s', time() - 60);
			$dateRefund = $refund->updated_at; //даём минуту для прохождения оплаты
			if ($date > $dateRefund) {
				Refund::find($refund->id)->whereStatus('processing')->update(array('status' => 'success'));
			}
			$refund = Refund::find($refund->id);
		}

		$data = $this->dataFromObj($refund);
		$data['error'] = 0;

		return $this->responseFromGate($data);

	}

	/**
	 * @param     $data
	 * @param int $code_response
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */

	private function responseFromGate($data, $code_response = 200)
	{

		$response = array(
			'response' => array(
				'result_code' => $data['error'],
			),
		);
		if (isset($data['status'])) {
			$response['response']['refund'] = $data;
		}

		return Response::json($response, $code_response);
	}

	/**
	 * @param Refund $refund
	 *
	 * @return array
	 */
	private function dataFromObj(Refund $refund)
	{
		$data = array();
		$data['refund_id'] = $refund->refund_id;
		$data['amount'] = $refund->amount;
		$data['status'] = $refund->status;
		$data['user'] = $refund->bill->user;

		foreach ($data as $key => $value) {
			if ($value === null) {
				unset($data[$key]);
			}
		}

		return $data;
	}

}