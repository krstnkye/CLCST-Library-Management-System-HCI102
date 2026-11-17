{{-- Any other 4xx error (for example 400, 405, 413, 422) gets the library's own page instead of the framework's. --}}
@extends('errors.layout')
@section('code', (string) $exception->getStatusCode())
@section('eyebrow', 'Request not completed')
@section('title', 'We couldn’t complete that request')
@section('message', 'The library system could not process that request. Go back, refresh the page, and try again.')
