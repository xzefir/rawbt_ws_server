<?php

namespace App;

use Hoa\Event\Bucket;
use Hoa\Websocket\Server;
use Noodlehaus\Config;

use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\UriPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class WebSocket
{

    private $conf;
    private $websocket;

    public function __construct($conf)
    {
        $this->conf = $conf;
    }

    public function build()
    {
        $port = $this->conf->get('PrintConnector.port', 40213);

        echo '> Starting server on ws://127.0.0.1:'.$port.' ...', "\n";

        $websocket = new Server(
            new \Hoa\Socket\Server('ws://127.0.0.1:'.$port)
        );

        $websocket->on('open', function (Bucket $bucket) {
            echo '> Connected', "\n";
            return;
        });

        $websocket->on('message', function (Bucket $bucket) {
            $data = $bucket->getData();
            echo '> Received request ', "\n";

            $toprint = $data['message'];
            $toprint = str_replace("intent:base64,", "", $toprint);
            $toprint = str_replace("#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;", "", $toprint);
            $toprint = base64_decode($toprint);

            switch ($this->conf->get('PrintConnector.Type')) {
                case 'Network':
                    set_time_limit($this->conf->get('PrintConnector.Params.timeout', 10) + 10);
                    $connector = new NetworkPrintConnector($this->conf->get('PrintConnector.Params.ip', '127.0.0.1'), $this->conf->get('PrintConnector.Params.port', 9100), $this->conf->get('PrintConnector.Params.timeout', 10));
                    break;
                case 'Uri':
                    $connector = UriPrintConnector::get($this->conf->get('PrintConnector.Params.uri', 'tcp://127.0.0.1:9100'));
                    break;
                case 'Cups':
                    $connector = new CupsPrintConnector($this->conf->get('PrintConnector.Params.dest'));
                    break;
                case 'File':
                    $connector = new FilePrintConnector($this->conf->get('PrintConnector.Params.filename'));
                    break;
                default:
                    $connector = new WindowsPrintConnector($this->conf->get('PrintConnector.Params.dest', 'LPT1'));
            }
            $connector->write($toprint);
            $connector->finalize();
            echo '> Done print task ', "\n";
            return;
        });

        $websocket->on('close', function (Bucket $bucket) {
            echo '> Disconnected', "\n";
            return;
        });

        $this->websocket = $websocket;
    }

    public function run()
    {
        $this->websocket->run();
    }

    public function stop()
    {
        $this->websocket->stop();
    }

}