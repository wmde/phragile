<?php namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
		if ($e instanceof \ConduitClientException && $e->getErrorCode() === 'ERR-INVALID-SESSION')
		{
			return response()->view('errors.missing_api_token', [], 500);
		}

		if ($this->isHttpException($e))
		{
			return $this->renderHttpException($e);
		}
		else
		{
			return parent::render($request, $e);
		}
	}

}
