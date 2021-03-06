<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\PredictionIO;

use Buzz\Browser;
use Buzz\Client\MultiCurl;
use PredictionIO\PredictionIOClient;

class PredictionIO
{
    /**
     * @var string
     */
    protected $apiUrl = 'http://localhost:8000/';

    /**
     * @var PredictionIOClient
     */
    protected $client;

    /**
     * Class constructor
     *
     * @param $appKey
     * @param null $apiUrl
     */
    public function __construct($appKey, $apiUrl = null)
    {
        $config = array('appkey' => $appKey);

        if ($apiUrl) {
            $config['apiurl'] = $apiUrl;
        }

        $this->client = PredictionIOClient::factory($config);
    }

    /**
     * Create a user.
     *
     * @param $userId
     * @return mixed
     */
    public function createUser($userId)
    {
        $command = $this->client->getCommand('create_user', array('pio_uid' => $userId));
        $response = $this->client->execute($command);

        return $response;
    }

    /**
     * Create an item.
     *
     * @param $itemId
     * @param array $itemTypes
     * @return mixed
     */
    public function createItem($itemId, $itemTypes = [])
    {
        $command = $this->client->getCommand('create_item', array('pio_iid' => $itemId, 'pio_itypes' => $itemTypes));
        $response = $this->client->execute($command);

        return $response;
    }

    /**
     * Record a user action on an item.
     *
     * @param $userId
     * @param $itemId
     * @param string $action
     * @param array $args
     * @return mixed
     */
    public function recordAction($userId, $itemId, $action = 'view', $args = [])
    {
        $args['pio_action'] = $action;
        $args['pio_iid']    = $itemId;
        $this->client->identify($userId);
        $response = $this->client->execute($this->client->getCommand('record_action_on_item', $args));

        return $response;
    }

    /**
     * Returns the recommendations for the given user according to the engine.
     *
     * @param $userId
     * @param $engine
     * @param array $args
     * @param int $count
     * @return mixed
     */
    public function getRecommendations($userId, $engine, $args = [], $count = 3)
    {
        $args["pio_engine"] = $engine;
        $args["pio_n"]      = $count;
        $this->client->identify($userId);
        $command = $this->client->getCommand('itemrec_get_top_n', $args);
        $response = $this->client->execute($command);

        return $response['pio_iids'];
    }

    /**
     * Returns the items similar to the given item according to the engine.
     *
     * @param $itemId
     * @param $engine
     * @param array $args
     * @param int $count
     * @return mixed
     */
    public function getSimilarItems($itemId, $engine, $args = [], $count = 3)
    {
        $args["pio_iid"]    = $itemId;
        $args["pio_engine"] = $engine;
        $args["pio_n"]      = $count;
        $command = $this->client->getCommand('itemsim_get_top_n', $args);
        $response = $this->client->execute($command);

        return $response['pio_iids'];
    }
}
