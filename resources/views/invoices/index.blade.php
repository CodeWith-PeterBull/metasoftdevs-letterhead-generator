@extends('layouts.letterhead')

@section('title', 'Invoice Management')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            @livewire('invoice-management')
        </div>
    </div>
</div>
@endsection