<?php namespace Cms\Helpers;

use Url;
use Site;
use Route;
use System\Helpers\DateTimeHelper;
use Config;

/**
 * Cms Helper
 *
 * @package october\cms
 * @see \Cms\Facades\Cms
 * @author Alexey Bobkov, Samuel Georges
 */
class Cms
{
    /**
     * @var bool actionExists determines if the run action exists
     */
    protected static $actionExists;

    /**
     * @var string urlPathPrefix prefixes every URL
     */
    protected $urlPathPrefix;

    /**
     * setUrlPrefix sets a prefix for every URL
     */
    public function setUrlPrefix(string $prefix)
    {
        $this->urlPathPrefix = trim($prefix, '/');
    }

    /**
     * url returns a URL in context of the frontend
     */
    public function url($path = null)
    {
        // Process path
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1);
        }

        if ($this->urlPathPrefix !== null) {
            $path = $this->urlPathPrefix . '/' . $path;
        }

        // Use the router
        $routeAction = 'Cms\Classes\CmsController@run';

        if (self::$actionExists === null) {
            self::$actionExists = Route::getRoutes()->getByAction($routeAction) !== null;
        }

        if (self::$actionExists) {
            return Url::action($routeAction, ['slug' => $path]);
        }

        // Use the base URL
        return Url::to($path);
    }

    /**
     * fullUrl returns a complete URL considering the current site context
     * and base URL, including prefix and hostname.
     */
    public function fullUrl($path = null)
    {
        if ($site = Site::getSiteFromContext()) {
            return rtrim($site->base_url . $path, '/');
        }

        return $this->url($path);
    }

    /**
     * makeCarbon converts mixed inputs to a Carbon object and sets the CMS timezone
     * @return \Carbon\Carbon
     */
    public function makeCarbon($value, $throwException = true)
    {
        $carbon = DateTimeHelper::makeCarbon($value, $throwException);

        $carbon->setTimezone(Config::get('cms.timezone', Config::get('app.timezone')));

        return $carbon;
    }
}
