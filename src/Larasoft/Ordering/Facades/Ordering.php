<?php 

/*
 * This file is part of the Larasoft package.
 *
 * (c) Rok Grabnar <rokgrabnar@hotmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Larasoft\Ordering\Facades;

use Illuminate\Support\Facades\Facade;

class Ordering extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'ordering'; }
	
}