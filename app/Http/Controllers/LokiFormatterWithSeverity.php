<?php 

namespace App\Http\Controllers;

class LokiFormatterWithSeverity extends \Itspire\MonologLoki\Formatter\LokiFormatter {

    public function __construct(
        array $labels = [],
        array $context = [],
        ?string $systemName = null,
        string $extraPrefix = '',
        string $contextPrefix = 'ctxt_'
    )
    {
        parent::__construct($labels, $context, $systemName, $extraPrefix, $contextPrefix);
    }

    public function format(array $record): array
    {
        $severity = $record['level_name'] ?? 'not-found';
        error_log('Severity in format: ' . $severity);    
        $record = parent::format($record);
        // return $record;
        return $this->addSeverity($record, $severity);
    }

    protected function addSeverity(array $record, string $severity) : array
    {
        return [
            'stream' => array_merge($record['stream'], ['severity' => strtolower($severity)]),
            'values' => $record['values']
        ];
    }
}