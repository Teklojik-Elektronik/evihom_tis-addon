<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('messages.language') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('language.change') }}" method="POST" class="d-inline">
            @csrf
            <div class="row align-items-center">
                <div class="col-md-6">
                    <select name="locale" class="form-select" id="languageSelect">
                        <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>
                            English
                        </option>
                        <option value="tr" {{ app()->getLocale() == 'tr' ? 'selected' : '' }}>
                            Türkçe
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="la la-save"></i> {{ __('messages.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('languageSelect').addEventListener('change', function() {
    this.form.submit();
});
</script>
