@extends('admin.layout')

@section('content')
    <div class="page-header mb-3">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="mb-1">{{ $title }}</h5>
                <p class="fs-12 text-muted mb-0">{{ $description }}</p>
            </div>
        </div>
    </div>

    @if (!empty($cards))
        <div class="row g-3">
            @foreach ($cards as $card)
                <div class="col-xl-3 col-md-6">
                    <div class="card admin-quick-card">
                        <div class="card-body">
                            <span class="badge bg-soft-primary text-primary">{{ $card['label'] }}</span>
                            <h3 class="mt-3 mb-0">{{ $card['value'] }}</h3>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if (!empty($meta['detail']))
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ $meta['detail']['title'] ?? __('messages.labels.selection') }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach (($meta['detail']['items'] ?? []) as $k => $v)
                        <div class="col-lg-4 col-md-6">
                            <div class="border rounded p-2 h-100">
                                <small class="text-muted d-block">{{ $k }}</small>
                                <strong>{{ $v }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if ($pageKey === 'dashboard')
        @if (!empty($meta['dashboard_chart']))
            <div class="card mt-3">
                <div class="card-header"><h6 class="mb-0">{{ __('messages.dashboard.global_chart') }}</h6></div>
                <div class="card-body"><div id="adminDashboardChart" style="height: 360px;"></div></div>
            </div>
        @endif

        @if (!empty($meta['dashboard_tables']))
            <div class="row mt-1 g-3">
                @foreach ($meta['dashboard_tables'] as $dashTable)
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><h6 class="mb-0">{{ $dashTable['title'] }}</h6></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover admin-table mb-0">
                                        <thead>
                                            <tr>
                                                @foreach ($dashTable['columns'] as $column)
                                                    <th>{{ strtoupper(str_replace('_', ' ', $column)) }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dashTable['rows'] as $row)
                                                <tr>
                                                    @foreach ($dashTable['columns'] as $column)
                                                        <td>{{ $row[$column] ?? '-' }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    @if (in_array($pageKey, ['notifications', 'manager_notifications']))
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ $title }}</h6>
                <span class="badge bg-light text-dark">{{ count($meta['notification_feed'] ?? []) }} {{ __('messages.labels.results') }}</span>
            </div>
            <div class="card-body p-0">
                @forelse (($meta['notification_feed'] ?? []) as $notif)
                    <a href="{{ $notif['view_url'] }}" class="d-flex align-items-start gap-3 p-3 border-bottom text-decoration-none">
                        <span class="rounded-circle bg-soft-primary text-primary d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="{{ $notif['icon'] ?? 'feather-bell' }}"></i>
                        </span>
                        <div class="flex-grow-1">
                            <div class="text-dark"><strong>{{ $notif['from'] }}</strong> {{ $notif['text'] }}</div>
                            <small class="text-muted">{{ $notif['date'] }}</small>
                        </div>
                    </a>
                @empty
                    <p class="text-muted p-3 mb-0">{{ __('messages.labels.no_data') }}</p>
                @endforelse
            </div>
        </div>
    @endif

    @if (!in_array($pageKey, ['dashboard', 'notifications', 'manager_notifications']))
        @php
            $totalResults = isset($meta['pagination']) ? $meta['pagination']->total() : count($table['rows'] ?? []);
            $roleSelectOptions = $meta['role_select_options'] ?? [];
            $statusSelectOptions = $meta['status_select_options'] ?? [];
            $workStatusOptions = $meta['work_status_options'] ?? [];
        @endphp
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0">{{ $title }}</h6>
                <div class="d-flex align-items-center gap-2">
                    @if (!empty($payload['selectedId']))
                        <span class="badge bg-primary">{{ __('messages.labels.selection') }} #{{ $payload['selectedId'] }}</span>
                    @endif
                    <span id="adminResultsCount" class="badge bg-light text-dark">{{ $totalResults }} {{ __('messages.labels.results') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="{{ !empty($form) ? 'col-12 col-xl-8' : 'col-12' }}">
                        @if ($pageKey === 'users' && !empty($meta['role_options']))
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="usersRoleFilter" class="form-label">{{ __('messages.users.filter_by_role') }}</label>
                                    <select id="usersRoleFilter" class="form-select">
                                        <option value="">{{ __('messages.users.all_roles') }}</option>
                                        @foreach ($meta['role_options'] as $role)
                                            <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if (!empty($meta['quick_links']) && !empty($payload['selectedId']))
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach ($meta['quick_links'] as $link)
                                    <a href="{{ route($link['route'], $link['params']) }}" class="btn btn-light-brand btn-sm">{{ $link['label'] }}</a>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($table['columns']) && !empty($table['rows']))
                            <div class="table-responsive">
                                <table class="table table-striped table-hover admin-table">
                                    <thead>
                                        <tr>
                                            @foreach ($table['columns'] as $column)
                                                <th>{{ strtoupper(str_replace('_', ' ', $column)) }}</th>
                                            @endforeach
                                            <th>{{ __('messages.actions.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="adminTableBody">
                                        @foreach ($table['rows'] as $row)
                                            <tr>
                                                @foreach ($table['columns'] as $column)
                                                    <td>
                                                        @if ($column === 'photo')
                                                            @if (!empty($row[$column]))
                                                                <img src="{{ $row[$column] }}" class="rounded-circle" width="32" height="32" alt="avatar" />
                                                            @else
                                                                <span class="badge bg-light text-dark">-</span>
                                                            @endif
                                                        @elseif ($column === 'role' && str_starts_with($pageKey, 'users'))
                                                            <select class="form-select form-select-sm admin-user-role" data-id="{{ $row['id'] }}">
                                                                <option value="">-</option>
                                                                @foreach ($roleSelectOptions as $roleId => $roleName)
                                                                    <option value="{{ $roleId }}" {{ (string) ($row['_role_id'] ?? '') === (string) $roleId ? 'selected' : '' }}>{{ $roleName }}</option>
                                                                @endforeach
                                                            </select>
                                                        @elseif ($column === 'etat' && str_starts_with($pageKey, 'users'))
                                                            @php
                                                                $currentStatusId = (string) ($row['_status_id'] ?? '');
                                                                $currentStatus = $statusSelectOptions[$currentStatusId] ?? null;
                                                                $statusLabel = $currentStatus['label'] ?? ($row['_status_name'] ?? '-');
                                                                $statusColor = $currentStatus['color'] ?? ($row['_status_color'] ?? 'secondary');
                                                            @endphp
                                                            <div class="dropdown">
                                                                <button class="badge border-0 dropdown-toggle badge-{{ e($statusColor) }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                                                                    {{ $statusLabel }}
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    @foreach ($statusSelectOptions as $statusId => $statusOption)
                                                                        <li><a href="#" class="dropdown-item admin-status-option" data-kind="user" data-id="{{ $row['id'] }}" data-status-id="{{ $statusId }}">{{ $statusOption['label'] }}</a></li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @elseif ($column === 'etat' && $pageKey === 'work')
                                                            @php
                                                                $currentStatusId = (string) ($row['_work_status_id'] ?? $row['_status_id'] ?? '');
                                                                $currentStatus = $workStatusOptions[$currentStatusId] ?? null;
                                                                $statusLabel = $currentStatus['label'] ?? ($row['_status_name'] ?? '-');
                                                                $statusColor = $currentStatus['color'] ?? ($row['_status_color'] ?? 'secondary');
                                                            @endphp
                                                            <div class="dropdown">
                                                                <button class="badge border-0 dropdown-toggle badge-{{ e($statusColor) }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                                                                    {{ $statusLabel }}
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    @foreach ($workStatusOptions as $statusId => $statusOption)
                                                                        <li><a href="#" class="dropdown-item admin-status-option" data-kind="work" data-id="{{ $row['id'] }}" data-status-id="{{ $statusId }}">{{ $statusOption['label'] }}</a></li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @else
                                                            {{ $row[$column] ?? '-' }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td>
                                                    <div class="d-flex align-items-center gap-3 fs-5">
                                                        @if (!empty($row['_view_url']))
                                                            <a href="{{ $row['_view_url'] }}" class="link-primary" title="{{ __('messages.actions.view') }}"><i class="feather-eye"></i></a>
                                                        @endif
                                                        @if (!empty($row['_delete_url']))
                                                            <a href="#" class="link-danger admin-delete-link" data-url="{{ $row['_delete_url'] }}" title="{{ __('messages.actions.delete') }}"><i class="feather-trash-2"></i></a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if (!empty($meta['pagination']) && $meta['pagination']->lastPage() > 1)
                                <nav class="d-flex justify-content-end" aria-label="Pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item {{ $meta['pagination']->onFirstPage() ? 'disabled' : '' }}"><a class="page-link" href="{{ $meta['pagination']->previousPageUrl() ?: '#' }}">&laquo;</a></li>
                                        @for ($i = 1; $i <= $meta['pagination']->lastPage(); $i++)
                                            <li class="page-item {{ $meta['pagination']->currentPage() === $i ? 'active' : '' }}"><a class="page-link" href="{{ $meta['pagination']->url($i) }}">{{ $i }}</a></li>
                                        @endfor
                                        <li class="page-item {{ $meta['pagination']->hasMorePages() ? '' : 'disabled' }}"><a class="page-link" href="{{ $meta['pagination']->nextPageUrl() ?: '#' }}">&raquo;</a></li>
                                    </ul>
                                </nav>
                            @endif
                        @else
                            <p class="text-muted mb-0">{{ __('messages.labels.no_data') }}</p>
                        @endif
                    </div>

                    @if (!empty($form))
                        <div class="col-12 col-xl-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ $form['title'] }}</h6>
                                </div>
                                <div class="card-body">
                                    @if ($pageKey === 'work')
                                        @php
                                            $workForm = $meta['work_form_options'] ?? [];
                                        @endphp
                                        <form id="adminAjaxForm" action="{{ $form['action'] }}" method="{{ $form['method'] ?? 'POST' }}" enctype="multipart/form-data">
                                            @csrf
                                            <div id="adminAjaxSuccess" class="alert alert-success d-none"></div>
                                            <div id="adminAjaxErrors" class="alert alert-danger d-none"></div>

                                            <div class="mb-3">
                                                <label class="form-label">Titre de l'oeuvre</label>
                                                <input type="text" name="work_title" class="form-control" required />
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="work_content" class="form-control" rows="3" required></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">URL YouTube</label>
                                                <input type="url" name="work_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." />
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Longueur (Heure:Minute:Seconde)</label>
                                                <input type="text" id="workMediaLength" name="media_length" class="form-control" placeholder="00:00:00" maxlength="8" />
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Auteur</label>
                                                <input type="text" name="author" class="form-control" />
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Editeur / Realisateur / Producteur</label>
                                                <input type="text" name="editor" class="form-control" />
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label d-block">Consultation payant</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="is_public" id="workPaidNo" value="1" checked>
                                                    <label class="form-check-label" for="workPaidNo">Non</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="is_public" id="workPaidYes" value="0">
                                                    <label class="form-check-label" for="workPaidYes">Oui</label>
                                                </div>
                                            </div>

                                            <div id="workPaidFields" class="row g-2 d-none mb-3">
                                                <div class="col-12">
                                                    <label class="form-label">Prix de la consultation</label>
                                                    <input type="number" name="consultation_price" step="0.01" min="0" class="form-control" />
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Devise</label>
                                                    <select name="currency_id" class="form-select">
                                                        <option value="">Selectionner...</option>
                                                        @foreach (($workForm['currencies'] ?? []) as $currency)
                                                            <option value="{{ $currency['id'] }}">{{ $currency['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label d-block">Type</label>
                                                @foreach (($workForm['types'] ?? []) as $type)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="type_id" id="workType{{ $type['id'] }}" value="{{ $type['id'] }}" required>
                                                        <label class="form-check-label" for="workType{{ $type['id'] }}">{{ $type['label'] }}</label>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label d-block">Categories</label>
                                                @foreach (($workForm['categories'] ?? []) as $category)
                                                    <div class="form-check">
                                                        <input class="form-check-input work-category-check" type="checkbox" name="categories_ids[]" id="workCategory{{ $category['id'] }}" value="{{ $category['id'] }}">
                                                        <label class="form-check-label" for="workCategory{{ $category['id'] }}">{{ $category['label'] }}</label>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Fichiers associes</label>
                                                <input type="file" id="workFilesInput" name="files_urls[]" class="form-control" multiple />
                                                <div id="workFilesPreview" class="mt-2 d-flex flex-column gap-2"></div>
                                            </div>

                                            <input type="hidden" name="status_id" value="{{ $workForm['declassified_status_id'] ?? '' }}">

                                            <div class="mb-3">
                                                <label class="form-label">Publieur</label>
                                                <select name="user_id" class="form-select">
                                                    <option value="">Selectionner...</option>
                                                    @foreach (($workForm['publishers'] ?? []) as $publisher)
                                                        <option value="{{ $publisher['id'] }}">{{ $publisher['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Organisation</label>
                                                <select name="organization_id" class="form-select">
                                                    <option value="">Selectionner...</option>
                                                    @foreach (($workForm['organizations'] ?? []) as $org)
                                                        <option value="{{ $org['id'] }}">{{ $org['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="d-flex justify-content-end align-items-center gap-2">
                                                <span id="adminAjaxSpinner" class="spinner-border spinner-border-sm text-primary d-none" role="status" aria-hidden="true"></span>
                                                <button type="submit" id="adminAjaxSubmit" class="btn btn-primary">{{ $form['submit_label'] ?? __('messages.actions.save') }}</button>
                                            </div>
                                        </form>
                                    @else
                                        <form id="adminAjaxForm" action="{{ $form['action'] }}" method="{{ $form['method'] ?? 'POST' }}">
                                            @csrf
                                            <div id="adminAjaxSuccess" class="alert alert-success d-none"></div>
                                            <div id="adminAjaxErrors" class="alert alert-danger d-none"></div>
                                            @foreach ($form['fields'] as $field)
                                                <div class="mb-3">
                                                    <label class="form-label">{{ $field['label'] }}</label>
                                                    @if (($field['type'] ?? 'text') === 'textarea')
                                                        <textarea name="{{ $field['name'] }}" class="form-control" {{ !empty($field['required']) ? 'required' : '' }}></textarea>
                                                    @else
                                                        <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['name'] }}" class="form-control" {{ !empty($field['step']) ? 'step=' . $field['step'] : '' }} {{ !empty($field['required']) ? 'required' : '' }} />
                                                    @endif
                                                </div>
                                            @endforeach
                                            <div class="d-flex justify-content-end align-items-center gap-2">
                                                <span id="adminAjaxSpinner" class="spinner-border spinner-border-sm text-primary d-none" role="status" aria-hidden="true"></span>
                                                <button type="submit" id="adminAjaxSubmit" class="btn btn-primary">{{ $form['submit_label'] ?? __('messages.actions.save') }}</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <script>
        (function () {
            const roleFilter = document.getElementById('usersRoleFilter');
            const tableBody = document.getElementById('adminTableBody');
            const resultsCount = document.getElementById('adminResultsCount');
            const roleSelectOptions = @json($meta['role_select_options'] ?? []);
            const statusSelectOptions = @json($meta['status_select_options'] ?? []);
            const workStatusOptions = @json($meta['work_status_options'] ?? []);

            const escapeHtml = (value) => String(value ?? '-')
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');
            const fileIconClass = (file) => {
                const name = String(file?.name || '').toLowerCase();
                const type = String(file?.type || '').toLowerCase();
                if (type.startsWith('image/')) return 'feather-image';
                if (type.startsWith('video/')) return 'feather-video';
                if (type.startsWith('audio/')) return 'feather-music';
                if (name.endsWith('.pdf') || name.endsWith('.doc') || name.endsWith('.docx') || name.endsWith('.txt')) return 'feather-file-text';
                return 'feather-file';
            };

            const selectHtml = (cls, userId, options, selected) => {
                const opts = ['<option value="">-</option>'].concat(Object.keys(options).map((k) => `<option value="${k}" ${String(selected ?? '') === String(k) ? 'selected' : ''}>${escapeHtml(options[k])}</option>`)).join('');
                return `<select class="form-select form-select-sm ${cls}" data-id="${userId}">${opts}</select>`;
            };

            const statusDropdownHtml = (kind, rowId, options, selectedId, fallbackLabel, fallbackColor) => {
                const key = String(selectedId ?? '');
                const current = options[key] || { label: fallbackLabel || '-', color: fallbackColor || 'secondary' };
                const items = Object.keys(options).map((statusId) => `<li><a href="#" class="dropdown-item admin-status-option" data-kind="${kind}" data-id="${rowId}" data-status-id="${statusId}">${escapeHtml(options[statusId].label || '-')}</a></li>`).join('');
                return `<div class="dropdown"><button class="badge border-0 dropdown-toggle badge-${escapeHtml(current.color || 'secondary')}" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">${escapeHtml(current.label || '-')}</button><ul class="dropdown-menu">${items}</ul></div>`;
            };

            const renderActions = (row) => {
                const view = row._view_url ? `<a href="${escapeHtml(row._view_url)}" class="link-primary" title="{{ __('messages.actions.view') }}"><i class="feather-eye"></i></a>` : '';
                const del = row._delete_url ? `<a href="#" class="link-danger admin-delete-link" data-url="${escapeHtml(row._delete_url)}" title="{{ __('messages.actions.delete') }}"><i class="feather-trash-2"></i></a>` : '';
                return `<div class="d-flex align-items-center gap-3 fs-5">${view}${del}</div>`;
            };

            const bindStatusEditors = () => {
                document.querySelectorAll('.admin-status-option').forEach((el) => {
                    if (el.dataset.bound === '1') return;
                    el.dataset.bound = '1';
                    el.addEventListener('click', async (e) => {
                        e.preventDefault();
                        const kind = el.getAttribute('data-kind');
                        const entityId = el.getAttribute('data-id');
                        const statusId = el.getAttribute('data-status-id');
                        if (!kind || !entityId || !statusId || typeof Swal === 'undefined') return;

                        const confirmText = kind === 'work' ? '{{ __('messages.works.confirm_status_change') }}' : '{{ __('messages.users.confirm_status_change') }}';
                        const ok = await Swal.fire({ title: '{{ __('messages.delete.title') }}', text: confirmText, icon: 'warning', showCancelButton: true, confirmButtonText: 'OK' });
                        if (!ok.isConfirmed) return;

                        const url = kind === 'work' ? `{{ url('/admin/work') }}/${entityId}/status` : `{{ url('/admin/users') }}/${entityId}/status`;
                        const resp = await fetch(url, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ status_id: statusId }) });
                        const data = await resp.json().catch(() => ({}));
                        if (!resp.ok || !data.success) { await Swal.fire({ icon: 'error', text: '{{ __('messages.errors.generic') }}' }); return; }
                        window.location.reload();
                    });
                });
            };

            const bindStateEditors = () => {
                document.querySelectorAll('.admin-user-role').forEach((el) => {
                    el.addEventListener('change', async () => {
                        const userId = el.getAttribute('data-id');
                        const roleId = el.value;
                        if (!userId || !roleId || typeof Swal === 'undefined') return;
                        const ok = await Swal.fire({ title: '{{ __('messages.delete.title') }}', text: '{{ __('messages.users.confirm_role_change') }}', icon: 'warning', showCancelButton: true, confirmButtonText: 'OK' });
                        if (!ok.isConfirmed) return;
                        await fetch(`{{ url('/admin/users') }}/${userId}/role`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }, body: JSON.stringify({ role_id: roleId }) });
                        window.location.reload();
                    });
                });
            };

            if (roleFilter && tableBody) {
                roleFilter.addEventListener('change', async function () {
                    const role = roleFilter.value;
                    const url = `{{ route('admin.users.filter') }}?role=${encodeURIComponent(role)}`;
                    const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await resp.json();
                    const cols = data.columns || [];
                    const rows = data.rows || [];
                    tableBody.innerHTML = rows.map((row) => {
                        const tds = cols.map((col) => {
                            if (col === 'photo' && row[col]) return `<td><img src="${escapeHtml(row[col])}" class="rounded-circle" width="32" height="32" alt="avatar" /></td>`;
                            if (col === 'photo') return '<td><span class="badge bg-light text-dark">-</span></td>';
                            if (col === 'role') return `<td>${selectHtml('admin-user-role', row.id, roleSelectOptions, row._role_id)}</td>`;
                            if (col === 'etat') return `<td>${statusDropdownHtml('user', row.id, statusSelectOptions, row._status_id, row._status_name, row._status_color)}</td>`;
                            return `<td>${escapeHtml(row[col] ?? '-')}</td>`;
                        }).join('');
                        return `<tr>${tds}<td>${renderActions(row)}</td></tr>`;
                    }).join('');
                    if (resultsCount) resultsCount.textContent = `${rows.length} {{ __('messages.labels.results') }}`;
                    bindDeleteLinks();
                    bindStateEditors();
                    bindStatusEditors();
                });
            }

            const ajaxForm = document.getElementById('adminAjaxForm');
            if (ajaxForm) {
                const errorBox = document.getElementById('adminAjaxErrors');
                const successBox = document.getElementById('adminAjaxSuccess');
                const spinner = document.getElementById('adminAjaxSpinner');
                const submitBtn = document.getElementById('adminAjaxSubmit');
                const paidFields = document.getElementById('workPaidFields');
                const paidYes = document.getElementById('workPaidYes');
                const paidNo = document.getElementById('workPaidNo');
                const mediaLengthInput = document.getElementById('workMediaLength');
                const filesInput = document.getElementById('workFilesInput');
                const filesPreview = document.getElementById('workFilesPreview');
                const categoryChecks = Array.from(document.querySelectorAll('.work-category-check'));

                const togglePaidFields = () => {
                    if (!paidFields) return;
                    const isPaid = paidYes && paidYes.checked;
                    paidFields.classList.toggle('d-none', !isPaid);
                    const priceInput = ajaxForm.querySelector('input[name="consultation_price"]');
                    const currencyInput = ajaxForm.querySelector('select[name="currency_id"]');
                    if (priceInput) priceInput.required = !!isPaid;
                    if (currencyInput) currencyInput.required = !!isPaid;
                };

                if (paidYes && paidNo) {
                    paidYes.addEventListener('change', togglePaidFields);
                    paidNo.addEventListener('change', togglePaidFields);
                    togglePaidFields();
                }

                if (mediaLengthInput) {
                    mediaLengthInput.addEventListener('input', () => {
                        const digits = mediaLengthInput.value.replace(/\D/g, '').slice(0, 6);
                        const p1 = digits.slice(0, 2);
                        const p2 = digits.slice(2, 4);
                        const p3 = digits.slice(4, 6);
                        mediaLengthInput.value = [p1, p2, p3].filter(Boolean).join(':');
                    });
                }

                let selectedWorkFiles = [];
                const syncFileInput = () => {
                    if (!filesInput) return;
                    const dt = new DataTransfer();
                    selectedWorkFiles.forEach((f) => dt.items.add(f));
                    filesInput.files = dt.files;
                };
                const renderFiles = () => {
                    if (!filesPreview) return;
                    filesPreview.innerHTML = selectedWorkFiles.map((file, idx) => `
                        <div class="border border-dark rounded p-2 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2 text-truncate">
                                <i class="${fileIconClass(file)}"></i>
                                <span class="text-truncate" style="max-width: 230px;">${escapeHtml(file.name)}</span>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm work-file-remove" data-index="${idx}">
                                <i class="feather-x"></i>
                            </button>
                        </div>
                    `).join('');
                    filesPreview.querySelectorAll('.work-file-remove').forEach((btn) => {
                        btn.addEventListener('click', () => {
                            const idx = Number(btn.getAttribute('data-index'));
                            if (Number.isNaN(idx)) return;
                            selectedWorkFiles.splice(idx, 1);
                            syncFileInput();
                            renderFiles();
                        });
                    });
                };

                if (filesInput) {
                    filesInput.addEventListener('change', () => {
                        const incoming = Array.from(filesInput.files || []);
                        incoming.forEach((file) => {
                            const duplicate = selectedWorkFiles.some((f) => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified);
                            if (!duplicate) selectedWorkFiles.push(file);
                        });
                        syncFileInput();
                        renderFiles();
                    });
                }

                ajaxForm.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    errorBox.classList.add('d-none');
                    errorBox.innerHTML = '';
                    if (successBox) { successBox.classList.add('d-none'); successBox.innerHTML = ''; }
                    if (spinner) spinner.classList.remove('d-none');
                    if (submitBtn) submitBtn.disabled = true;

                    if (categoryChecks.length > 0) {
                        const hasCategory = categoryChecks.some((c) => c.checked);
                        if (!hasCategory) {
                            errorBox.innerHTML = '<div>Veuillez selectionner au moins une categorie.</div>';
                            errorBox.classList.remove('d-none');
                            if (spinner) spinner.classList.add('d-none');
                            if (submitBtn) submitBtn.disabled = false;
                            return;
                        }
                    }

                    const formData = new FormData(ajaxForm);
                    const resp = await fetch(ajaxForm.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData,
                    });
                    const data = await resp.json().catch(() => ({}));
                    if (!resp.ok || !data.success) {
                        const errors = data.errors || {};
                        const html = Object.keys(errors).map((k) => `<div>${escapeHtml(Array.isArray(errors[k]) ? errors[k][0] : errors[k])}</div>`).join('');
                        errorBox.innerHTML = html || '{{ __('messages.errors.generic') }}';
                        errorBox.classList.remove('d-none');
                        if (spinner) spinner.classList.add('d-none');
                        if (submitBtn) submitBtn.disabled = false;
                        return;
                    }
                    if (successBox) {
                        successBox.innerHTML = escapeHtml(data.message || 'Enregistrement effectue avec succes.');
                        successBox.classList.remove('d-none');
                    }
                    ajaxForm.reset();
                    if (paidYes && paidNo) togglePaidFields();
                    if (filesInput) {
                        selectedWorkFiles = [];
                        syncFileInput();
                        renderFiles();
                    }
                    if (spinner) spinner.classList.add('d-none');
                    if (submitBtn) submitBtn.disabled = false;
                });
            }

            const bindDeleteLinks = () => {
                document.querySelectorAll('.admin-delete-link').forEach((link) => {
                    link.addEventListener('click', async function (e) {
                        e.preventDefault();
                        const url = link.getAttribute('data-url');
                        if (!url || typeof Swal === 'undefined') return;
                        const result = await Swal.fire({ title: '{{ __('messages.delete.title') }}', text: '{{ __('messages.delete.text') }}', icon: 'warning', showCancelButton: true, confirmButtonText: '{{ __('messages.delete.confirm') }}', cancelButtonText: '{{ __('messages.delete.cancel') }}' });
                        if (!result.isConfirmed) return;
                        const resp = await fetch(url, { method: 'DELETE', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
                        const data = await resp.json();
                        if (!resp.ok || !data.success) { await Swal.fire({ icon: 'error', text: '{{ __('messages.errors.generic') }}' }); return; }
                        window.location.reload();
                    });
                });
            };

            bindDeleteLinks();
            bindStateEditors();
            bindStatusEditors();

            @if ($pageKey === 'dashboard' && !empty($meta['dashboard_chart']))
                if (typeof ApexCharts !== 'undefined') {
                    new ApexCharts(document.querySelector('#adminDashboardChart'), {
                        chart: { height: 360, type: 'bar', toolbar: { show: false } },
                        series: [{ name: '{{ __('messages.dashboard.dataset_label') }}', data: @json($meta['dashboard_chart']['series']) }],
                        xaxis: { categories: @json($meta['dashboard_chart']['labels']) },
                        colors: ['#3454d1'],
                        dataLabels: { enabled: false }
                    }).render();
                }
            @endif
        })();
    </script>
@endsection
