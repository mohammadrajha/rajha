@extends('layouts.app')

@section('title', 'Sync Rooms')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .sync-card { max-width: 640px; margin: 2rem auto; }
    .spinner-hidden { display: none !important; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="card shadow sync-card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Sync Rooms From External API</h4>
        </div>
        <div class="card-body text-center">
            <p class="text-muted">
                Click the button below to pull the latest room schedule data from the external API
                and store it in the local database.
            </p>

            <button id="sync-btn" type="button" class="btn btn-primary btn-lg">
                <span id="sync-btn-label">Sync Rooms Data</span>
                <span id="sync-spinner" class="spinner-border spinner-border-sm ms-2 spinner-hidden"
                      role="status" aria-hidden="true"></span>
            </button>

            <div id="sync-alert" class="alert mt-4 d-none" role="alert"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn     = document.getElementById('sync-btn');
    const label   = document.getElementById('sync-btn-label');
    const spinner = document.getElementById('sync-spinner');
    const alertEl = document.getElementById('sync-alert');
    const url     = "{{ route('admin.sync-rooms.run') }}";
    const token   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function showAlert(type, message) {
        alertEl.className = 'alert mt-4 alert-' + type;
        alertEl.textContent = message;
        alertEl.classList.remove('d-none');
    }

    function hideAlert() {
        alertEl.classList.add('d-none');
        alertEl.textContent = '';
    }

    function setLoading(loading) {
        btn.disabled = loading;
        label.textContent = loading ? 'Syncing...' : 'Sync Rooms Data';
        spinner.classList.toggle('spinner-hidden', !loading);
    }

    btn.addEventListener('click', function () {
        hideAlert();
        setLoading(true);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async function (response) {
            const data = await response.json().catch(function () { return {}; });
            if (response.ok && data.success) {
                showAlert('success', data.message + ' (' + (data.synced ?? 0) + ' records)');
            } else {
                showAlert('danger', data.message || ('Sync failed (HTTP ' + response.status + ').'));
            }
        })
        .catch(function (err) {
            showAlert('danger', 'Network error: ' + err.message);
        })
        .finally(function () {
            setLoading(false);
        });
    });
});
</script>
@endpush
