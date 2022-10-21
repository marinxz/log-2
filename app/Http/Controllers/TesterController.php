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
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;

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
        $tempo_host = "172.18.0.2";
        $tempo_port = "6832";
        $tempo_rest = "/v1/traces";
        $endpointUrl = $tempo_protocol . $tempo_host . ":" . $tempo_port . $tempo_rest;
        
        Log::info("Endpoint url:" . $endpointUrl);    
        // 'http://jaeger:9412/api/v2/spans'
        // $exporter = JaegerExporter::fromConnectionString( $endpointUrl, 'AlwaysOnJaegerExample');
        
        // $tracerProvider = new TracerProvider(
        //     new SimpleSpanProcessor($exporter),
        //     new AlwaysOnSampler(),
        // );

        $exporter = new AgentExporter('Laravel-log-2', $endpointUrl);
        $attributes = \OpenTelemetry\SDK\Common\Attribute\Attributes::create( 
            array( 'app'=>'test-app-laravel' ));
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
        // Log::withContext(['severity'=> 'warning']);
        Log::warning('before span');

        $tracer = $tracerProvider->getTracer("Laravel");
        $span = $tracer->spanBuilder('root')->startSpan();
        $span_scope = $span->activate();
        $context = $span->getContext();
        Log::info('In span');
        Log::info($context->getTraceId());
        Log::info('message with trace-id', ['trace_id'=>$context->getTraceId(), 'span_id' => $context->getSpanId()]);
        // ------------------------------ calling other service -------------------------

        $end_url = $request->get('end_url');
        error_log("Starting tracing for end url:" . $end_url);
        Log::info("Starting tracing for end url:" . $end_url, ['trace_id'=>$context->getTraceId(), 'span_id' => $context->getSpanId()]);
        $propagator = new TraceContextPropagator();
        $carrier = array();
        $propagator->inject($carrier); 
        $trace_id_formated = $carrier['traceparent'];
        error_log($carrier['traceparent']);
        
        $message = 'Requests not completed';
        
        $response = Http::withHeaders(['traceparent'=>$trace_id_formated])->get($end_url);

        if( $response->ok() ){
            error_log($response->body());
            $message = 'Requests completed, response: ' . $response->body();
        }
        else{
            error_log('Request not ok');
        }

        $span_scope->detach();
        $span->end();
        $tracerProvider->shutdown();

        return redirect('/tester')->with('message', $message);
    }

}