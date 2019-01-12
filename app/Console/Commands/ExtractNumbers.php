<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class ExtractNumbers extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'extract numbers from a base url, fixed in .env file';

    /**
     * Guzzle http client
     *    
     * @var GuzzleHttp\Client;
     */
    private $client;


    /**
     * keeps a reference to a failed http request, storing the page number
     * 
     * @var Array 
     */
    private $failedRequests;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $baseUri = 'http://challenge.dienekes.com.br:8888/api/numbers';
        $this->client = new \GuzzleHttp\Client(['base_uri' => $baseUri]);
        $this->failedRequests = [];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {       
        for ($pageNumber = 1; ; $pageNumber++) {
            $numbers = $this->readPage($pageNumber);

            if ($this->isLastPage($numbers)) {
                $this->info('Done');
                break;
            }

            $this->storeNumbers($numbers, $pageNumber);
        }

        $this->recoverFailures();
    }

    /**
     * Makes a http request to consume numbers
     * 
     * @param int $pageNumber the page that is being passed as query string
     * @param bool $persistFailures if true indicates that a failed request should be kept for later recover
     * 
     * @return Array An associative array with body response in numbers => [] format
     */
    private function readPage(int $pageNumber, bool $persistFailures = true)
    {
        try {
            
            $response = $this->client->request('GET', 'numbers', [
                'query' => [
                    'page' => $pageNumber
                ],
                'connect_timeout' => 10.0
            ]);
        }
        catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $this->info("Unable to seed the database at page $pageNumber");
            $this->addFailedRequests($pageNumber);
        }
        catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->info("timeout at $pageNumber");
            $this->addFailedRequests($pageNumber);
        }

        return  isset($response) ? json_decode($response->getBody(), true) : null;
    }

    /**
     * Check if last page is reached. 
     * 'numbers => []' indicates thats it's the last endpoint to visit
     * 
     * @param Array $bodyResponse
     * 
     * @return bool returns true when reachs the last endpoint
     */
    private function isLastPage($bodyResponse)
    {
        if ($bodyResponse && array_key_exists('numbers', $bodyResponse)) {
            return count($bodyResponse['numbers']) == 0;
        }

        return false;
    }

    /**
     * Receive a associative array and store its values
     * 
     * @param Array $numbersJson 
     */
    private function storeNumbers($numbersJson, $pageNumber)
    {
        if($numbersJson) {
            $records = $this->prepareRows($numbersJson['numbers'], $pageNumber);

            DB::table('numbers')->insert($records);
        }
    }

    /**
     * Performs a simple transformation in $numbers
     * making query builder able to bulk insert some rows
     * 
     * @param Array $numbers data being transformed
     */
    private function prepareRows($numbers, $pageNumber)
    {
        $rows = [];
        foreach ($numbers as $number) {
            array_push($rows, [
                'number' => $number,
                'page_number' => $pageNumber
            ]);
        }

        return $rows;
    }

    /**
     * Stores page numbers that failed in previus requests
     * so we can try to retrive them later
     * 
     * @param $pageNumber page that failed
     * 
     */
    private function addFailedRequests($pageNumber)
    {
        array_push($this->failedRequests, $pageNumber);
    }

    /**
     * Try to read failed requests again
     */
    private function recoverFailures()
    {
        // Some logic could be done to implements max retries
        // but for now we just try to recover once
        foreach($this->failedRequests as $key => $failedRequestNumber) {
            if ($this->readPage($failedRequestNumber, false)) {
                unset($this->failedRequests[$key]);
                $this->info("Page $failedRequestNumber recovered successfully");
            }
            else {
                $this->info("Unable to recover page $failedRequestNumber");
            }
        }
    }
}