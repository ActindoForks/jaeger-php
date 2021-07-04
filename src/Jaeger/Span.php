<?php
/*
 * Copyright (c) 2019, The Jaeger Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Jaeger;


use OpenTracing\Reference;

class Span implements \OpenTracing\Span, \JsonSerializable {

    protected $operationName = '';

    /**
     * @var int $startTime Unix timestamp in microseconds
     */
    protected $startTime = '';

    /**
     * @var int $finishTime Unix timestamp in microseconds
     */
    protected $finishTime;

    /**
     * @var string $spanKind
     */
    protected $spanKind = '';

    /**
     * @var \OpenTracing\SpanContext|null $spanContext
     */
    protected $spanContext = null;

    /**
     * @var int $finishTime duration in microseconds after finish
     */
    protected $duration;

    /**
     * @var string[][] $logs
     */
    protected $logs = [];

    /**
     * @var string[] $tags
     */
    protected $tags = [];

    /**
     * @var Reference[] $references
     */
    protected $references = [];

    public function __construct($operationName, \OpenTracing\SpanContext $spanContext, $references, $startTime = null){
        $this->operationName = $operationName;
        $this->startTime = $startTime == null ? $this->microtimeToInt() : $startTime;
        $this->spanContext = $spanContext;
        $this->references = $references;
    }

    /**
     * @return string
     */
    public function getOperationName(){
        return $this->operationName;
    }

    /**
     * @return SpanContext
     */
    public function getContext(){
        return $this->spanContext;
    }

    /**
     * @param int|null $finishTime if passing int: unix timestamp in microseconds
     * @param array $logRecords
     * @return mixed
     */
    public function finish($finishTime = null, array $logRecords = []){
        $this->finishTime = $finishTime == null ? $this->microtimeToInt() : $finishTime;
        $this->duration = $this->finishTime - $this->startTime;
    }

    /**
     * @param string $newOperationName
     */
    public function overwriteOperationName($newOperationName){
        $this->operationName = $newOperationName;
    }


    public function setTag($key, $value){
        $this->tags[$key] = $value;
    }


    /**
     * Adds a log record to the span
     *
     * @param array $fields [key => val]
     * @param int|float|\DateTimeInterface $timestamp
     * @throws SpanAlreadyFinished if the span is already finished
     */
    public function log(array $fields = [], $timestamp = null){
        $log['timestamp'] = $timestamp ? $timestamp : $this->microtimeToInt();
        $log['fields'] = $fields;
        $this->logs[] = $log;
    }

    /**
     * Adds a baggage item to the SpanContext which is immutable so it is required to use SpanContext::withBaggageItem
     * to get a new one.
     *
     * @param string $key
     * @param string $value
     * @throws SpanAlreadyFinished if the span is already finished
     */
    public function addBaggageItem($key, $value){
        $this->log([
            'event' => 'baggage',
            'key' => $key,
            'value' => $value,
        ]);
        return $this->spanContext->withBaggageItem($key, $value);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getBaggageItem($key){
        return $this->spanContext->getBaggageItem($key);
    }


    private function microtimeToInt(){
        return intval(microtime(true) * 1000000);
    }

    /**
     * @return int Unix timestamp in microseconds
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return int|null Duration in microseconds after finish
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return int|null Unix timestamp in microseconds
     */
    public function getFinishTime()
    {
        return $this->finishTime;
    }



    public function jsonSerialize()
    {
        return [
            'operationName' => $this->operationName,
            'startTime' => $this->startTime,
            'finishTime' => $this->finishTime,
            'spanKind' => $this->spanKind,
            'spanContext' => $this->spanContext,
            'duration' => $this->duration,
            'logs' => $this->logs,
            'tags' => $this->tags,
            'references' => $this->references,
        ];
    }

    /**
     * @return string
     */
    public function getSpanKind()
    {
        return $this->spanKind;
    }

    /**
     * @return \OpenTracing\SpanContext|null
     */
    public function getSpanContext()
    {
        return $this->spanContext;
    }

    /**
     * @return \string[][]
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @return string[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return Reference[]
     */
    public function getReferences()
    {
        return $this->references;
    }


}