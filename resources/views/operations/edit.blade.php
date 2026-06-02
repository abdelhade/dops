@extends('layouts.app')

@section('title', __('dobs.edit_operation_prefix') . ' ' . $operation->operation_number)

@section('header_title', __('dobs.edit_operation_prefix') . ' ' . $operation->operation_number)
@section('header_subtitle', __('dobs.edit_operation_subtitle'))

@section('header_actions')
<a href="{{ route('operations.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
<div class="glass-card operation-form-card">
    <form action="{{ route('operations.update', $operation->id) }}" method="POST" id="operation-form">
        @csrf
        @method('PUT')

        @include('operations._form', ['operation' => $operation])

        <div class="form-actions">
            <a href="{{ route('operations.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_changes') }}
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/operation-form.js') }}?v={{ @filemtime(public_path('js/operation-form.js')) ?: 1 }}"></script>
@endsection
