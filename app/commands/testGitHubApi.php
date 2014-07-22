<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use FintechFab\Models\MembersGitHub;

class testGitHubApi extends Command
{
	private static $apiBaseUrl = 'https://api.github.com/';
	private $_rateLimit = 0;
	private $_rateLimitRemaining = 0;
	private $_rateLimitReset = 0;

	private $apiRepos;


	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:testGitHubApi';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command for testing GitHub Api';


	public function __construct()
	{
		parent::__construct();
		$this->apiRepos = "https://api.github.com/repos/fintech-fab/fintech-fab.ru/";

		//$res = self::getFromGitHubApi(self::$apiBaseUrl . "rate_limit");
		//$this->_rateLimit = $res->rate->limit;
		//$this->_rateLimitRemaining = $res->rate->remaining;
		//$this->_rateLimitReset = $res->rate->reset;

	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->info("It's OK");


		//$res = getFromGitHubApi("https://api.github.com/users/fintech-fab");
		//$res = $this->getFromGitHubApi("https://api.github.com/repos/fintech-fab/fintech-fab.ru/issues/9/events");
		//$res = $this->getFromGitHubApi("https://api.github.com/repos/fintech-fab/fintech-fab.ru"); //+
		//$res = $this->getFromGitHubApi("https://api.github.com/repos/fintech-fab/fintech-fab.ru/assignees"); //+
		//$res = $this->getFromGitHubApi("https://api.github.com/repos/fintech-fab/fintech-fab.ru/languages"); //+(?)
		//$res = $this->getFromGitHubApi("https://api.github.com/repos/fintech-fab/fintech-fab.ru/contributors"); //+(?)
		//$res = $this->getFromGitHubApi("https://api.github.com/repos/fintech-fab/fintech-fab.ru/subscribers"); //+(?)

		//$res = self::getFromGitHubApi("https://api.github.com/repos/fintech-fab/fintech-fab.ru/issues");//+
		//$res = self::getFromGitHubApi("https://api.github.com/repos/fintech-fab/fintech-fab.ru/issues?state=closed");
		//$res = self::getIssuesComments('2014-06-10T00:00:00Z');
		//$res = self::getIssuesComments('2014-06-10');


		//$res = self::getFromGitHubApi("https://api.github.com/repos/fintech-fab/fintech-fab.ru/issues?state=closed");

		$opt = $this->option();
		$dt = getdate();
		$qDate = date('c', $dt[0]-(3600*24*40));
		//$this->info(date('c', $dt[0]-(3600*24*30)));

		switch($this->argument('firstArg')) {
			case "comments":
				$res = $this->getFromGitHubApi($this->apiRepos . "issues/comments?since=" . $qDate, "issuesCommentsData");
				break;
			case "commits":
				$res = $this->getFromGitHubApi($this->apiRepos . "commits?since=" . $qDate, "commitsData");
				break;
			case "events":
				$res = $this->getFromGitHubApi($this->apiRepos . "events", "eventsData");
				break;
			case "issues":
				/**?state=open|closed|all
				 * ?since='YYY-MM-DDTHH:MM:SSZ'
				 */
				$res = $this->getFromGitHubApi($this->apiRepos . "issues?state=all", "issuesData");
				break;
			case "issuesEvents":
				$res = $this->getFromGitHubApi($this->apiRepos . "issues/events?page=2", "issuesEventsData");
				break;
			case "users":
				$res = $this->getFromGitHubApi($this->apiRepos . "contributors");
				//$res = self::getFromGitHubApi($this->apiRepos . "assignees");
				//$res = self::getFromGitHubApi($this->apiRepos . "collaborators");
				if(! empty($opt["saveInDB"])) {
					$this->info("Addition to DataBase...");
					$this->saveUsers($res['response']);
					$res = '';
				}

				break;
			default:
				//$res = $this->argument();
				//$res = self::getFromGitHubApi("https://api.github.com/orgs/fintech-fab/members");
				$res = $this->option();
				$this->info("saveInDB: " . (empty($res["saveInDB"]) ? "false" : "true"));
		}
		//$this->_rateLimitRemaining -= 1;



		ob_start();
		print_r($res);  // var_dump($this->argument('firstArg'));
		$d =  ob_get_clean();
		$this->info($d);
		$this->info("OK");
		$this->info("rateLimit: " . $this->_rateLimit);
		$this->info("rateLimitRemaining: " . $this->_rateLimitRemaining);
		$this->info("rateLimitReset: " . date("c", $this->_rateLimitReset));


	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('firstArg', InputArgument::OPTIONAL, 'An argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('firstOpt', null, InputOption::VALUE_OPTIONAL, 'An option.', null),
			array('saveInDB', null, InputOption::VALUE_NONE, 'An option.', null),
		);
	}
	protected function getFromGitHubApi($httpRequest, $func = '')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $httpRequest);
		//curl_setopt($ch, CURLOPT_HEADER, 0); //<---------------
		curl_setopt($ch, CURLOPT_HEADER, 1);   //<===============
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "fintech-fab");


		//curl_setopt($ch, CURLOPT_USERPWD, ":");


		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		//<===
		$res = explode("\n", $response);        //<===header
		$response = array_pop($res);
		$header = array();
		foreach($res as $str)
		{
			$p = strpos($str, ":");
			if($p > 0)
			{
				$header[substr($str, 0, $p)] = substr($str, $p + 1);
			}
		}
		$fullResponse = array();
		 $this->_rateLimit = $header['X-RateLimit-Limit'];
		 $this->_rateLimitRemaining = $header['X-RateLimit-Remaining'];
		 $this->_rateLimitReset = intval($header['X-RateLimit-Reset']);
		$fullResponse['header'] = $header;                          //<===header


		if($http_code == 200)
		{
			if($func == '')
			{
				$fullResponse['response'] = json_decode($response);//<===header
				return $fullResponse;                              //<===header
				//return json_decode($response);//<---без заголовка
			} else
			{
				$res = array();
				foreach(json_decode($response) as $inData)
				{
					$res[] = self::$func($inData);
				}
				//return $res;  //<---без заголовка
				$fullResponse['response'] = $res;//<===header
				return $fullResponse;            //<===header
			}

		} else {
			return $http_code;
		}
	}

	protected static function commitsData($inData)
	{
		$x = array();
		$x['html_url'] = $inData->html_url;
		$x['date'] = $inData->commit->author->date;
		$x['message'] = $inData->commit->message;
		$x['authorName'] = $inData->commit->author->name;
		$x['committerName'] = $inData->commit->committer->name;
		$x['author'] = self::isNull(array($inData, "author", "login")); //empty($inData->author->login) ? '' : $inData->author->login;
		$x['committer'] = self::isNull(array($inData, "committer", "login")); //empty($inData->committer->login) ? '' : $inData->committer->login;
		return $x;
	}

	protected static function eventsData($inData)
	{
		$x = array();
		$x['id'] = $inData->id;
		$x['type'] = $inData->type;
		$x['actorLogin'] = $inData->actor->login;
		$x['created_at'] = $inData->created_at;
		return $x;
	}

	protected static function issuesCommentsData($inData)
	{
		$x = array();
		$x['html_url'] = $inData->html_url;
		$x['issue_url'] = $inData->issue_url;
		$n = explode('/', $x['issue_url']);
		$x['issue_num'] = $n[count($n)-1];
		$x['created_at'] = $inData->created_at;
		$x['updated_at'] = $inData->updated_at;
		$x['user'] = $inData->user->login;
		return $x;

	}

	protected static function issuesEventsData($inData)
	{
		$x = array();
		$x['id'] = $inData->id;
		$x['event'] = $inData->event;
		$x['actorLogin'] = $inData->actor->login;
		$x['commit_id'] = $inData->commit_id;
		$x['created_at'] = $inData->created_at;
		$x['issue'] = $inData->issue->number;
		return $x;
	}

	protected static function issuesData($inData)
	{
		$x = array();
		$x['html_url'] = $inData->html_url;
		$x['number'] = $inData->number;
		$x['title'] = $inData->title;
		$x['state'] = $inData->state;
		$x['created_at'] = $inData->created_at;
		$x['updated_at'] = $inData->updated_at;
		$x['closed_at'] = self::isNull($inData->closed_at);
		$x['user'] = $inData->user->login;
		return $x;

	}

	/**
	 * @param        $inVal
	 * @param string $val
	 *
	 * @return string
	 */
	private static function isNull($inVal = null, $val = '')
	{
		if(is_array($inVal))
		{
			if(empty($inVal[0])){
				return '';
			} else {
				$x = $inVal[0];
				for($i = 1; $i < count($inVal); $i++)
				{
					if(empty($x->$inVal[$i])){
						return '';
					} else {
						$x = $x->$inVal[$i];
					}
				}
				return $x;
			}
		}
		return empty($inVal) ? $val : $inVal;
	}

	private function saveUsers($inData)
	{
		foreach($inData as $inMember)
		{
			$member = MembersGitHub::where("login", $inMember->login)->first();
			if(isset($member->id))
			{
				$this->info("User: " . $member->login);
				if(! empty($inMember->contributions))
				{
					if($member->contributions != $inMember->contributions)
					{
						$this->info("Update: " . $member->login);
						$member->contributions = $inMember->contributions;
						$member->save();
					}
				}
			}
			else
			{
				$this->info("Addition user: " . $inMember->login);
				$member = new MembersGitHub;
				$member->dataGitHub($inMember);
				$member->save();
			}


		}
	}

}
