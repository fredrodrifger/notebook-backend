<?php

namespace App\Constants;

class ConnectionConstants
{
    /**
     * The studied backend splits its data over a central ('app') and a per-tenant ('tenant')
     * connection. This application is single-tenant, so there is exactly one connection and the
     * constant exists only to keep the "declare your connection explicitly" rule in place.
     */
    const APP_CONNECTION = 'sqlite';
}
