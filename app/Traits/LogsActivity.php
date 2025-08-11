<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Stevebauman\Location\Facades\Location;
use App\Jobs\LogActivityJob;

trait LogsActivity
{
    /**
     * Log the activity of a user.
     *
     * @param string $action
     * @param string|null $details
     * @return void
     */
    protected function logActivity($action, $details = null)
    {
        $location = Location::get(Request::ip());

        // Intentar obtener el ID de usuario autenticado; si no existe (p. ej., antes de completar 2FA),
        // usar el ID temporal almacenado en la sesión durante el flujo de login.
        $userId = Auth::id() ?: Request::session()->get('login.id');

        // Si no hay userId disponible, evitar registrar para no violar la FK (user_id NOT NULL)
        if (!$userId) {
            return;
        }

        $data = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'geolocation' => $location ? $location->countryName . ', ' . $location->cityName : 'Unknown',
            'browser' => $this->getBrowser(),
            'os' => $this->getOS(),
            'referer' => Request::header('referer')
        ];

        dispatch(new LogActivityJob($data));
    }

    /**
     * Get the browser name from the User-Agent string.
     *
     * @return string
     */
    protected function getBrowser()
    {
        $userAgent = Request::header('User-Agent');
        $browsers = [
            'Brave' => 'Brave',
            'Edge' => 'Edge',
            'Opera' => 'Opera',
            'OPR' => 'Opera',
            'Vivaldi' => 'Vivaldi',
            'Firefox' => 'Firefox',
            'Chrome' => 'Chrome',
            'Safari' => 'Safari',
            'MSIE' => 'Internet Explorer',
            'Trident' => 'Internet Explorer',
            'SamsungBrowser' => 'Samsung Internet',
            'UCBrowser' => 'UC Browser',
            'QQBrowser' => 'QQ Browser',
            'Baiduspider' => 'Baidu Browser',
            'YaBrowser' => 'Yandex Browser',
            'Bot' => 'Bot',
            'Spider' => 'Spider'
        ];

        foreach ($browsers as $key => $browser) {
            if (strpos($userAgent, $key) !== false) {
                return $browser;
            }
        }

        return 'Other';
    }

    /**
     * Get the operating system name from the User-Agent string.
     *
     * @return string
     */
    protected function getOS()
    {
        $userAgent = Request::header('User-Agent');
        $osArray = [
            'Windows NT 11.0' => 'Windows 11',
            'Windows NT 10.0' => 'Windows 10',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 6.2' => 'Windows 8',
            'Windows NT 6.1' => 'Windows 7',
            'Windows NT 6.0' => 'Windows Vista',
            'Windows NT 5.1' => 'Windows XP',
            'Windows NT 5.0' => 'Windows 2000',
            'Macintosh' => 'Mac OS',
            'Mac OS X' => 'Mac OS X',
            'Linux' => 'Linux',
            'Ubuntu' => 'Ubuntu',
            'iPhone' => 'iOS',
            'iPad' => 'iOS',
            'Android' => 'Android',
            'BlackBerry' => 'BlackBerry',
            'Windows Phone' => 'Windows Phone',
            'Windows CE' => 'Windows CE',
            'Windows 98' => 'Windows 98',
            'Windows 95' => 'Windows 95',
            'Windows ME' => 'Windows ME',
            'Symbian' => 'Symbian',
            'Chrome OS' => 'Chrome OS'
        ];

        foreach ($osArray as $key => $os) {
            if (strpos($userAgent, $key) !== false) {
                return $os;
            }
        }

        return 'Other';
    }
}
