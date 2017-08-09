<?php

namespace LasseRafn\Dinero\Builders;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use LasseRafn\Dinero\Exceptions\DineroRequestException;
use LasseRafn\Dinero\Exceptions\DineroServerException;
use LasseRafn\Dinero\Responses\PaginatedResponse;
use LasseRafn\Dinero\Utils\Model;
use LasseRafn\Dinero\Utils\Request;

class Builder
{
    private $request;
    protected $entity;

    /** @var Model */
    protected $model;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $id
     *
     * @return mixed|Model
     */
    public function find($id)
    {
        try {
            $response = $this->request->curl->get("{$this->entity}/{$id}");
            $responseData = json_decode($response->getBody()->getContents());

            return new $this->model($this->request, $responseData);
        } catch (ClientException $exception) {
            throw new DineroRequestException($exception);
        } catch (ServerException $exception) {
            throw new DineroServerException($exception);
        }
    }

    /**
     * @param string $parameters
     *
     * @return PaginatedResponse
     */
    public function get($parameters = '')
    {
        try {
            $response = $this->request->curl->get("{$this->entity}{$parameters}");
            $paginatedResponse = new PaginatedResponse($response);
        } catch (ClientException $exception) {
            throw new DineroRequestException($exception);
        } catch (ServerException $exception) {
            throw new DineroServerException($exception);
        }

        $request = $this->request;

	    $paginatedResponse->setItems(array_map(function($item) use($request) {
		    return new $this->model($request, $item);
	    }, $paginatedResponse->items));

        return $paginatedResponse;
    }
}
