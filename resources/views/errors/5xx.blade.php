{{-- Any other 5xx error gets the library's own page instead of the framework's. --}}
@extends('errors.layout')
@section('code', (string) $exception->getStatusCode())
@section('eyebrow', 'Something went wrong')
@section('title', 'The library system hit a problem')
@section('message', 'This was not caused by anything you did. Please try again in a moment, and tell the library office if it keeps happening.')
