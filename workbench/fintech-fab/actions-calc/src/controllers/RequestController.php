<?php
/**
 * Class RequestController
 *
 * @author Ulashev Roman <truetamtam@gmail.com>
 */

namespace FintechFab\ActionsCalc\Controllers;

use Controller;
use Input;
use Log;

class RequestController extends Controller
{
	public function getRequest() {
		$data = Input::get('all');
		$requestTestData = json_decode($data['data']);
		Log::info("Request in: $requestTestData");
		$requestTestData['test'] = 2;
		return json_encode(['data' => $requestTestData]);
	}
}

 