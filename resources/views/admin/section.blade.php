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
                    @if (!empty($form))
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adminAddModal">{{ __('messages.actions.add') }}</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
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
                                                @elseif ($column === 'role_edit')
                                                    <select class="form-select form-select-sm admin-user-role" data-id="{{ $row['id'] }}">
                                                        <option value="">-</option>
                                                        @foreach ($roleSelectOptions as $roleId => $roleName)
                                                            <option value="{{ $roleId }}" {{ (string) ($row['_role_id'] ?? '') === (string) $roleId ? 'selected' : '' }}>{{ $roleName }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif ($column === 'etat_edit' && str_starts_with($pageKey, 'users'))
                                                    <select class="form-select form-select-sm admin-user-status" data-id="{{ $row['id'] }}">
                                                        <option value="">-</option>
                                                        @foreach ($statusSelectOptions as $statusId => $statusName)
                                                            <option value="{{ $statusId }}" {{ (string) ($row['_status_id'] ?? '') === (string) $statusId ? 'selected' : '' }}>{{ $statusName }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif ($column === 'etat_edit' && $pageKey === 'work')
                                                    <select class="form-select form-select-sm admin-work-status" data-id="{{ $row['id'] }}">
                                                        <option value="">-</option>
                                                        @foreach ($workStatusOptions as $statusId => $statusName)
                                                            <option value="{{ $statusId }}" {{ (string) ($row['_work_status_id'] ?? '') === (string) $statusId ? 'selected' : '' }}>{{ $statusName }}</option>
                                                        @endforeach
                                                    </select>
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
        </div>
    @endif

    @if (!empty($form))
        <div class="modal fade" id="adminAddModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $form['title'] }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="adminAjaxForm" action="{{ $form['action'] }}" method="{{ $form['method'] ?? 'POST' }}">
                        @csrf
                        <div class="modal-body">
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
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-brand" data-bs-dismiss="modal">{{ __('messages.actions.cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ $form['submit_label'] ?? __('messages.actions.save') }}</button>
                        </div>
                    </form>
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

            const escapeHtml = (value) => String(value ?? '-')
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');

            const selectHtml = (cls, userId, options, selected) => {
                const opts = ['<option value="">-</option>'].concat(Object.keys(options).map((k) => `<option value="${k}" ${String(selected ?? '') === String(k) ? 'selected' : ''}>${escapeHtml(options[k])}</option>`)).join('');
                return `<select class="form-select form-select-sm ${cls}" data-id="${userId}">${opts}</select>`;
            };

            const renderActions = (row) => {
                const view = row._view_url ? `<a href="${escapeHtml(row._view_url)}" class="link-primary" title="{{ __('messages.actions.view') }}"><i class="feather-eye"></i></a>` : '';
                const del = row._delete_url ? `<a href="#" class="link-danger admin-delete-link" data-url="${escapeHtml(row._delete_url)}" title="{{ __('messages.actions.delete') }}"><i class="feather-trash-2"></i></a>` : '';
                return `<div class="d-flex align-items-center gap-3 fs-5">${view}${del}</div>`;
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

                document.querySelectorAll('.admin-user-status').forEach((el) => {
                    el.addEventListener('change', async () => {
                        const userId = el.getAttribute('data-id');
                        const statusId = el.value;
                        if (!userId || !statusId || typeof Swal === 'undefined') return;
                        const ok = await Swal.fire({ title: '{{ __('messages.delete.title') }}', text: '{{ __('messages.users.confirm_status_change') }}', icon: 'warning', showCancelButton: true, confirmButtonText: 'OK' });
                        if (!ok.isConfirmed) return;
                        await fetch(`{{ url('/admin/users') }}/${userId}/status`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }, body: JSON.stringify({ status_id: statusId }) });
                        window.location.reload();
                    });
                });

                document.querySelectorAll('.admin-work-status').forEach((el) => {
                    el.addEventListener('change', async () => {
                        const workId = el.getAttribute('data-id');
                        const statusId = el.value;
                        if (!workId || !statusId || typeof Swal === 'undefined') return;
                        const ok = await Swal.fire({ title: '{{ __('messages.delete.title') }}', text: '{{ __('messages.works.confirm_status_change') }}', icon: 'warning', showCancelButton: true, confirmButtonText: 'OK' });
                        if (!ok.isConfirmed) return;
                        await fetch(`{{ url('/admin/work') }}/${workId}/status`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }, body: JSON.stringify({ status_id: statusId }) });
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
                            if (col === 'role_edit') return `<td>${selectHtml('admin-user-role', row.id, roleSelectOptions, row._role_id)}</td>`;
                            if (col === 'etat_edit') return `<td>${selectHtml('admin-user-status', row.id, statusSelectOptions, row._status_id)}</td>`;
                            return `<td>${escapeHtml(row[col] ?? '-')}</td>`;
                        }).join('');
                        return `<tr>${tds}<td>${renderActions(row)}</td></tr>`;
                    }).join('');
                    if (resultsCount) resultsCount.textContent = `${rows.length} {{ __('messages.labels.results') }}`;
                    bindDeleteLinks();
                    bindStateEditors();
                });
            }

            const ajaxForm = document.getElementById('adminAjaxForm');
            if (ajaxForm) {
                const errorBox = document.getElementById('adminAjaxErrors');
                ajaxForm.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    errorBox.classList.add('d-none');
                    errorBox.innerHTML = '';
                    const formData = new FormData(ajaxForm);
                    const resp = await fetch(ajaxForm.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData,
                    });
                    const data = await resp.json();
                    if (!resp.ok || !data.success) {
                        const errors = data.errors || {};
                        const html = Object.keys(errors).map((k) => `<div>${escapeHtml(Array.isArray(errors[k]) ? errors[k][0] : errors[k])}</div>`).join('');
                        errorBox.innerHTML = html || '{{ __('messages.errors.generic') }}';
                        errorBox.classList.remove('d-none');
                        return;
                    }
                    window.location.reload();
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
