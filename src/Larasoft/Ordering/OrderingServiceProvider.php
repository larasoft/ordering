<?php 

namespace Larasoft\Ordering;

use Illuminate\Support\ServiceProvider;

class OrderingServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('larasoft/ordering');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('ordering', function($app)
		{
			$ordering = new Environment($app['request'], $app['view'], $app['translator']);
			
			$ordering->setViewName($app['config']->get('view.ordering'));
			
			return $ordering;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}