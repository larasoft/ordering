<?php 

/*
 * This file is part of the Larasoft package.
 *
 * (c) Rok Grabnar <rokgrabnar@hotmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Larasoft\Ordering;

use Illuminate\Http\Request;
use Illuminate\View\Environment as ViewEnvironment;
use Symfony\Component\Translation\TranslatorInterface;

class Environment {
    
    /**
     * The request instance.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * The view environment instance.
     *
     * @var \Illuminate\View\Environment
     */
    protected $view;

    /**
     * The translator implementation.
     *
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $trans;

    /**
     * The name of the pagination view.
     *
     * @var string
     */
    protected $viewName;

    /**
     * The locale to be used by the translator.
     *
     * @var string
     */
    protected $locale;

    /**
     * The base URL in use by the paginator.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * The input parameter used for the order.
     *
     * @var string
     */
    protected $orderName;

    /**
     * The input parameter used for the order direction.
     *
     * @var string
     */
    protected $orderDirection;

    /**
     * The input parameter used for the current order.
     *
     * @var string
     */
    protected $currentOrder;

    /**
     * The input parameter used for the current order direction.
     *
     * @var string
     */
    protected $currentDirection = 'ASC';

    /**
     * The input parameter used for the default order.
     *
     * @var string
     */
    protected $defaultOrder = false;

    /**
     * The input parameter used for the default order direction.
     *
     * @var string
     */
    protected $defaultDirection = 'ASC';

    /**
     * All of the additional query string values.
     *
     * @var array
     */
    protected $query = array();

    /**
     * All of the order values.
     *
     * @var array
     */
    protected $orders = array();

    /**
     * The base query builder instance.
     *
     * @var \Illuminate\Database\Query\Builder
     */
    protected $queryBuilder;

    /**
     * Create a new ordering environment.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Illuminate\View\Environment  $view
     * @param  \Symfony\Component\Translation\TranslatorInterface  $trans
     * @param  string  $orderName
     * @param  string $orderDirection
     * @return void
     */
    public function __construct(Request $request, ViewEnvironment $view, TranslatorInterface $trans, $orderName = 'order', $orderDirection = 'order_direction')
    {
        $this->view = $view;
        $this->trans = $trans;
        $this->request = $request;
        $this->orderName = $orderName;
        $this->orderDirection = $orderDirection;
        $this->setupViewEnvironment();
    }

    /**
     * Setup the view environment.
     *
     * @return void
     */
    protected function setupViewEnvironment()
    {
        $this->view->addNamespace('ordering', __DIR__.'/../../views');
    }

    /**
     * Get new environment
     *
     * @param array $orders
     * @param string $defaultOrder
     * @param string $defaultDirection
     * @return \Larasoft\Ordering\Environment
     */

    public function makeOrder($order, $title, $defaultOrder = false, $defaultDirection = false)
    {
        if($defaultOrder) $this->setDefaultOrder($order);

        if($defaultDirection) $this->setDefaultDirection($defaultDirection);

        if($order == $this->getCurrentOrder())
        {
            $this->applyQuery($this->getCurrentOrder(), $this->getCurrentDirection());

            $active = ($this->getCurrentDirection() == 'ASC') ? 'ASC' : 'DESC';
        }
        else
        {
            $active = false;
        }

        if(is_array($title))
        {
            $title = ucfirst($title);

            $ascTitle = $title[0];

            $descTitle = $title[1];
        }
        else
        {
            $title = $title;

            $ascTitle = $title . ' &uarr;';

            $descTitle = $title . ' &darr;';
        }

        $this->orders[$order] = array(
            'title' => $title,
            'asc_title' => $ascTitle,
            'asc_action' => $this->getAscUrl($order),
            'desc_title' => $descTitle,
            'desc_action' => $this->getDescUrl($order),
            'active' => $active
        );
    }

    public function make(array $orders, $defaultOrder = false, $defaultDirection = false)
    {
        foreach($orders as $order => $orderTitle)
        {
            if($order == $defaultOrder)
            {
                $defaultOrder = true;
            }

            $this->makeOrder($order, $orderTitle, $defaultOrder, $defaultDirection);
        }

        return $this;
    }

    public function getAscUrl($order)
    {
        return $this->getUrl(array($this->orderName => $order, $this->orderDirection => 'ASC'));
    }

    public function getDescUrl($order)
    {
        return $this->getUrl(array($this->orderName => $order, $this->orderDirection => 'DESC'));
    }

    public function getDisableUrl()
    {
        return $this->getUrl(array($this->orderName, $this->orderDirection), true);
    }

    public function links($view = null)
    {
        $data = array(
            'orders' => $this->orders,
            'disable_action' => $this->getDisableUrl()
        );

        return $this->view->make($this->getViewName($view), $data);
    }

    /**
     * Get orders
     *
     * @return array
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Get particular order from array by order name
     *
     * @return array
     */
    public function getOrder($orderName)
    {
        return (isset($this->orders[$orderName])) ? $this->orders[$orderName] : null;
    }

    /**
     * Checks if it has particular order from array by order name
     *
     * @return array
     */
    public function hasOrder($orderName)
    {
        return (isset($this->orders[$orderName])) ? true : false;
    }

    /**
     * Remove particular order from array by order name
     *
     * @return array
     */
    public function removeOrder($orderName)
    {
        unset($this->orders[$orderName]);
    }

    /**
     * Get current order
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        if($this->currentOrder)
        {
            return $this->currentOrder;
        }
        else
        {
            $requestData = $this->getRequest()->all();

            $this->currentOrder = (isset($requestData[$this->orderName])) ? $requestData[$this->orderName] : $this->defaultOrder;
            
            return $this->currentOrder;
        }        
    }

    /**
     * Get current order direction
     *
     * @return string
     */
    public function getCurrentDirection()
    {
        $requestData = $this->getRequest()->all();

        return (isset($requestData[$this->orderDirection])) ? $requestData[$this->orderDirection] : $this->defaultDirection;
    }

    /**
     * Get builded url with provided parameters
     *
     * @param  array $query
     * @param  bool  $remove
     * @return string
     */
    protected function getUrl($query = array(), $remove = false)
    {
        $parameters = $this->getRequest()->all();

        foreach($query as $key => $value)
        {
            // if remove param is true the we unset 
            // order values from query string
            if($remove)
            {
                unset($parameters[$value]);
            }
            else
            {
                $parameters[$key] = $value;
            }
        }

        return $this->getCurrentUrl() .'?'.http_build_query($parameters, null, '&');
    }

    public function queryBuilder($queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);

        return $this;
    }

    /**
     * Set the base query builder instance.
     *
     * @param \Illuminate\Database\Query\Builder $queryBuilder
     * @return void
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Get the base query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Check if it has query builder instance
     *
     * @return bool
     */
    public function hasQueryBuilder()
    {
        return ($this->queryBuilder) ? true : false;
    }

    /**
     * Add current order and direction to the sql query builder
     *
     * @param  string $currentOrder
     * @param  string  $currentDirection
     * @return void
     */
    protected function applyQuery($currentOrder, $currentDirection = 'ASC')
    {
        if($this->queryBuilder)
        {
            $this->queryBuilder->orderBy($currentOrder, $currentDirection);
        }
    }

    /**
     * Set the default order.
     *
     * @param  string  $defaultOrder
     * @return void
     */
    public function setdefaultOrder($defaultOrder)
    {
        $this->defaultOrder = $defaultOrder;
    }

    /**
     * Set the default order direction.
     *
     * @param  string  $defaultOrderDirection
     * @return void
     */
    public function setdefaultDirection($defaultDirection)
    {
        $this->defaultDirection = $defaultDirection;
    }

    /**
     * Get the root URL for the request.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->baseUrl ?: $this->request->url();
    }

    /**
     * Set the base URL in use by the paginator.
     *
     * @param  string  $baseUrl
     * @return void
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set the input order parameter name used by the ordering.
     *
     * @param  string  $pageName
     * @return void
     */
    public function setOrderName($orderName)
    {
        $this->orderName = $orderName;
    }

    /**
     * Get the input order parameter name used by the ordering.
     *
     * @return string
     */
    public function getOrderName()
    {
        return $this->orderName;
    }

    /**
     * Set the input order parameter name used by the ordering.
     *
     * @param  string  $pageName
     * @return void
     */
    public function setOrderDirection($orderDirection)
    {
        $this->orderDirection = $orderDirection;
    }

    /**
     * Get the input order parameter name used by the ordering.
     *
     * @return string
     */
    public function getOrderDirection()
    {
        return $this->orderDirection;
    }

    /**
     * Get the name of the pagination view.
     *
     * @param  string  $view
     * @return string
     */
    public function getViewName($view = null)
    {
        if ( ! is_null($view)) return $view;

        return $this->viewName ?: 'ordering::list';
    }

    /**
     * Set the name of the pagination view.
     *
     * @param  string  $viewName
     * @return void
     */
    public function setViewName($viewName)
    {
        $this->viewName = $viewName;
    }

    /**
     * Get the locale of the paginator.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the locale of the paginator.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get the active request instance.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the active request instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the current view driver.
     *
     * @return \Illuminate\View\Environment
     */
    public function getViewDriver()
    {
        return $this->view;
    }

    /**
     * Set the current view driver.
     *
     * @param  \Illuminate\View\Environment  $view
     * @return void
     */
    public function setViewDriver(ViewEnvironment $view)
    {
        $this->view = $view;
    }

    /**
     * Get the translator instance.
     *
     * @return \Symfony\Component\Translation\TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->trans;
    }

}
