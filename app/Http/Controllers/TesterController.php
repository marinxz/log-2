<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\Contrib\Jaeger\AgentExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;

use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class ClockImp implements \OpenTelemetry\SDK\Common\Time\ClockInterface{
    public function now() : int {
        return intval(microtime(TRUE));
    }

    public function nanoTime() : int {
        return intval(microtime(TRUE));
    }

}

class TesterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tester');
    }

    public function store(Request $request)
    {
        $message = 'Logging';
        Log::info("in store");
        Log::info('Adding variable', ['app_record_id' => 123123123123]);

        // tracing 

        $tempo_protocol = "";
        $tempo_host = "172.20.0.4";
        $tempo_port = "6831";
        $tempo_rest = "";
        $endpointUrl = $tempo_protocol . $tempo_host . ":" . $tempo_port . $tempo_rest;
        
        Log::info("Endpoint url:" . $endpointUrl);    
        // 'http://jaeger:9412/api/v2/spans'
        // $exporter = JaegerExporter::fromConnectionString( $endpointUrl, 'AlwaysOnJaegerExample');
        
        // $tracerProvider = new TracerProvider(
        //     new SimpleSpanProcessor($exporter),
        //     new AlwaysOnSampler(),
        // );

        $exporter = new AgentExporter('Laravel-log-2', $endpointUrl);
        $attributes = \OpenTelemetry\SDK\Common\Attribute\Attributes::create( array( "SERVICE_NAME" => "Laravel-service", "service_name" => "Laravel-service-malo", 'app'=>'test-app' ));
        $resInfo = \OpenTelemetry\SDK\Resource\ResourceInfo::create($attributes);
        $tracerProvider = new \OpenTelemetry\SDK\Trace\TracerProvider(
            new \OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor(
                $exporter, 
                new ClockImp()
            ),
            new \OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler(), 
            $resInfo
        );
        // $tracerProvider =  new TracerProvider(
        //     new SimpleSpanProcessor(
        //         new ConsoleSpanExporter()
        //     )
        // );

        $tracer = $tracerProvider->getTracer("Laravel");
        $span = $tracer->spanBuilder('root')->startSpan();
        $span_scope = $span->activate();
        $context = $span->getContext();
        Log::info('In span');
        Log::info($context->getTraceId());
        Log::info('message with trace-id', ['trace_id'=>$context->getTraceId(), 'span_id' => $context->getSpanId()]);
        $span_scope->detach();
        $span->end();
        $tracerProvider->shutdown();

        return redirect('/tester')->with('message', $message);
    }

}