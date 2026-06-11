@extends('layouts.app')

@section('title', __('dobs.create_operation_type'))

@section('header_title', __('dobs.create_operation_type'))
@section('header_subtitle', __('dobs.create_operation_type_subtitle'))

@section('header_actions')
    <a href="{{ route('operation-types.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
    </a>
@endsection

@section('content')
<div class="glass-card">
    <form action="{{ route('operation-types.store') }}" method="POST">
        @csrf
        @include('operation_types._form')
        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_operation_type') }}
            </button>
        </div>
    </form>
</div>
@endsection
