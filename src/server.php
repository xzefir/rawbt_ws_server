<?php
/**
 *  WebSocket server for RawBT
 *
 *  Based on PHP POS Print (Local Server)
 *  https://github.com/Tecdiary/ppp
 *  MIT License
 *
 *  Modified by 402d (oleg@muraveyko.ru)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Noodlehaus\Config;

use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\UriPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

$conf = Config::load('server.json');

while (true) {
    echo '> Starting WebSocket Interface.', "\n";

    while (true) {
        $websocket = new App\WebSocket($conf);

        try {
            echo '> Building server...', "\n";
            $websocket->build();

            echo '> Running server...', "\n";
            $websocket->run();
        } catch (Exception $e) {
            echo '> Error occurred, server stopped. ', $e->getMessage(), "\n";
        } finally {
            $websocket->stop();
            $websocket = null;
        }

        echo '> Restarting server in 1 seconds...', "\n";
        sleep(1);
    }
}

