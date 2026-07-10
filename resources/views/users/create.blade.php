@extends('layouts.app')

@section('title', __('dobs.create_user'))

@section('header_title', __('dobs.create_user'))
@section('header_subtitle', __('dobs.create_user_subtitle'))

@section('header_actions')
<a href="{{ route('users.index') }}" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-right"></i> {{ __('dobs.back_to_list') }}
</a>
@endsection

@section('content')
@include('partials.role-permissions-hint')

<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">{{ __('dobs.col_name') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">{{ __('dobs.email') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="role" class="form-label">{{ __('dobs.col_role') }} <span style="color: var(--color-danger)">*</span></label>
            <select name="role" id="role" class="form-control" required>
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                        {{ __('dobs.role_' . $role) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">الحالات</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; padding: 10px; background: rgba(255, 255, 255, 0.05); border-radius: 6px;">
                @foreach($services as $service)
                    <label style="display: flex; align-items: center; gap: 8px; color: var(--text-primary); cursor: pointer; margin: 0;">
                        <input type="checkbox" name="services[]" value="{{ $service->id }}" {{ in_array($service->id, old('services', [])) ? 'checked' : '' }}>
                        {{ $service->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('dobs.permissions') }}</label>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">{{ __('dobs.permissions_subtitle') }}</p>
            
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAllPermissions(true)" style="padding: 4px 8px; font-size: 0.75rem;">تحديد الكل</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAllPermissions(false)" style="padding: 4px 8px; font-size: 0.75rem;">إلغاء تحديد الكل</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleColumnPermissions('read')" style="padding: 4px 8px; font-size: 0.75rem;">قراءة الكل</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleColumnPermissions('create')" style="padding: 4px 8px; font-size: 0.75rem;">إنشاء الكل</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleColumnPermissions('update')" style="padding: 4px 8px; font-size: 0.75rem;">تعديل الكل</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleColumnPermissions('delete')" style="padding: 4px 8px; font-size: 0.75rem;">حذف الكل</button>
            </div>

            <div class="table-responsive" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; padding: 12px; overflow-x: auto;">
                <table class="table" style="width: 100%; margin: 0; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
                            <th style="padding: 8px; text-align: right; color: var(--text-primary);">{{ __('dobs.module') }}</th>
                            <th style="padding: 8px; text-align: center; color: var(--text-primary);">{{ __('dobs.action_read') }}</th>
                            <th style="padding: 8px; text-align: center; color: var(--text-primary);">{{ __('dobs.action_create') }}</th>
                            <th style="padding: 8px; text-align: center; color: var(--text-primary);">{{ __('dobs.action_update') }}</th>
                            <th style="padding: 8px; text-align: center; color: var(--text-primary);">{{ __('dobs.action_delete') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $resources = [
                                'operations' => __('dobs.nav_operations'),
                                'operation-movements' => __('dobs.nav_operation_movements'),
                                'clients' => __('dobs.nav_clients'),
                                'items' => __('dobs.nav_items'),
                                'materials' => __('dobs.nav_materials'),
                                'suppliers' => __('dobs.nav_suppliers'),
                                'services' => 'الحالات',
                                'users' => __('dobs.nav_users'),
                                'categories' => __('dobs.nav_categories'),
                            ];
                            $actions = ['read', 'create', 'update', 'delete'];
                        @endphp
                        @foreach($resources as $resKey => $resName)
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="padding: 8px; font-weight: 500; color: var(--text-primary); text-align: right;">{{ $resName }}</td>
                                @foreach($actions as $action)
                                    @php
                                        $permName = "{$resKey}.{$action}";
                                        $checked = in_array($permName, old('permissions', []));
                                    @endphp
                                    <td style="padding: 8px; text-align: center;">
                                        <input type="checkbox" name="permissions[]" value="{{ $permName }}" {{ $checked ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.15);">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <script>
                function toggleAllPermissions(checked) {
                    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
                    checkboxes.forEach(cb => cb.checked = checked);
                }

                function toggleColumnPermissions(action) {
                    const checkboxes = document.querySelectorAll('input[name="permissions[]"][value$=".' + action + '"]');
                    const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
                    checkboxes.forEach(cb => cb.checked = anyUnchecked);
                }
            </script>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">{{ __('dobs.password') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">{{ __('dobs.password_confirmation') }} <span style="color: var(--color-danger)">*</span></label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>

        <div class="form-actions">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('dobs.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> {{ __('dobs.save_user') }}
            </button>
        </div>
    </form>
</div>
@endsection
