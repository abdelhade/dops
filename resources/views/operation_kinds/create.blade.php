@extends('layouts.app')

@section('title', __('dobs.create_operation_kind'))

@section('header_title', __('dobs.create_operation_kind'))
@section('header_subtitle', __('dobs.create_operation_kind_subtitle'))

@section('header_actions')
    <a href="{{ route('operation-kinds.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
    </a>
@endsection

@section('content')
<div class="glass-card" style="max-width: 640px; margin: 0 auto;">
    <form action="{{ route('operation-kinds.store') }}" method="POST">
        @csrf
        @include('operation_kinds._form')
        <div class="form-actions">
            <a href="{{ route('operation-kinds.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_operation_kind') }}
            </button>
        </div>
    </form>
</div>
@endsection
