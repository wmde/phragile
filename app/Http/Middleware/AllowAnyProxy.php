<?php
namespace App\Http\Middleware;

use Closure;

class AllowAnyProxy {
	/**
	 * Adds the client IP to trusted proxies, thereby allowing SSL through server side proxies
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$request->setTrustedProxies([$request->getClientIp()]);
		return $next($request);
	}
}
