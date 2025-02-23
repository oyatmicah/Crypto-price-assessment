<?php

use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

WebSocketsRouter::webSocket('/crypto-updates', App\WebSockets\CryptoUpdatesHandler::class);
