<div class="delete-password-modal" id="deletePasswordModal" hidden aria-hidden="true">
    <div class="delete-password-modal__backdrop" data-delete-password-dismiss></div>
    <div
        class="delete-password-modal__dialog glass-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deletePasswordModalTitle"
    >
        <h2 class="delete-password-modal__title" id="deletePasswordModalTitle">
            <i class="fa-solid fa-lock"></i>
            {{ __('dobs.delete_password_modal_title') }}
        </h2>
        <p class="delete-password-modal__message" id="deletePasswordModalMessage"></p>

        <form id="deletePasswordModalForm" autocomplete="off">
            <div class="form-group">
                <label for="deletePasswordInput" class="form-label">{{ __('dobs.delete_password_label') }}</label>
                <input
                    type="password"
                    id="deletePasswordInput"
                    class="form-control"
                    autocomplete="off"
                    required
                >
            </div>

            <p class="delete-password-modal__error" id="deletePasswordModalError" hidden></p>

            <div class="form-actions delete-password-modal__actions">
                <button type="button" class="btn btn-secondary" data-delete-password-dismiss>
                    {{ __('dobs.cancel') }}
                </button>
                <button type="submit" class="btn btn-danger" id="deletePasswordModalConfirm">
                    <i class="fa-solid fa-trash"></i> {{ __('dobs.delete') }}
                </button>
            </div>
        </form>
    </div>
</div>
