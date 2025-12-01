<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Aquí puedes registrar todos los canales de broadcasting que soporta
| tu aplicación. Los closure dados autorizan a un usuario para
| escuchar el canal.
|
*/

/**
 * Canal público de sensores
 * Todos pueden escuchar datos de sensores en tiempo real
 */
Broadcast::channel('sensors', function () {
    return true;
});

/**
 * Canal público de alertas
 * Todos pueden escuchar alertas en tiempo real
 */
Broadcast::channel('alerts', function () {
    return true;
});
