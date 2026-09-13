@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="alert alert-success">
        <strong>Setup berhasil.</strong>
        Laravel {{ app()->version() }} — PHP {{ PHP_VERSION }} — Bootstrap terpasang via CDN.
    </div>
@endsection