@php
    $options = $meta['work_form_options'] ?? [];
    $selectedWork = !empty($payload['selectedId']) ? \App\Models\Work::with(['files.type', 'categories'])->findOrFail($payload['selectedId']) : null;
    $values = $selectedWork ? $selectedWork->only(['work_title', 'work_content', 'work_url', 'author', 'editor', 'is_public', 'consultation_price', 'currency_id', 'type_id', 'status_id', 'user_id', 'organization_id']) : ['is_public' => 1, 'status_id' => $options['declassified_status_id'] ?? ''];
    $values['categories_ids'] = $selectedWork ? $selectedWork->categories->pluck('id')->all() : [];
    $seconds = (int) ($selectedWork?->media_length ?? 0);
    $values['media_length'] = $seconds ? sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60) : '';
    $editorFiles = $selectedWork ? $selectedWork->files->sortBy(fn ($file) => sprintf('%010d-%010d', $file->position ?? PHP_INT_MAX, $file->id))->values()->map(fn ($file) => [
        'id' => $file->id, 'name' => $file->file_name, 'url' => $file->file_url, 'title' => $file->part_title,
        'cover' => $file->part_cover_url, 'position' => $file->position, 'duration' => $file->media_length,
        'typeId' => $file->type_id, 'typeLabel' => $file->type?->getTranslation('type_name', app()->getLocale()),
    ])->all() : [];
    $fileTypes = \App\Models\Type::whereHas('group', fn ($q) => $q->where('group_name', 'Type de fichier'))->get()->map(fn ($type) => [
        'id' => $type->id, 'label' => $type->getTranslation('type_name', app()->getLocale()), 'fr' => $type->getTranslation('type_name', 'fr'),
    ])->all();
    $editorConfig = ['values' => $values, 'files' => $editorFiles, 'options' => $options, 'types' => $fileTypes,
        'statuses' => $meta['work_status_options'] ?? [], 'id' => $selectedWork?->id, 'base' => url('/admin/work'),
        'fallback' => asset('assets/img/cover.png'), 'csrf' => csrf_token(), 'labels' => trans('work_editor')];
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/work-editor.css') }}">
<div id="workEditorRoot">
    <div class="d-flex justify-content-end mb-3"><button type="button" id="workAdd" class="btn btn-primary">+ {{ __('work_editor.add') }}</button></div>
    @if ($selectedWork)
        <section class="card"><div class="card-body">
            <div id="workDetails" class="row g-3"></div>
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3"><h6>{{ __('work_editor.step2') }}</h6><button type="button" id="workAppend" class="btn btn-outline-primary">+ {{ __('work_editor.files') }}</button></div>
            <p class="text-muted">{{ __('work_editor.order_hint') }}</p>
            <div id="workExistingFiles"></div>
        </div></section>
    @endif
    <dialog id="workWizard" class="work-dialog" aria-labelledby="workWizardTitle">
        <div class="work-dialog-heading"><h5 id="workWizardTitle">{{ __('work_editor.add') }}</h5><button type="button" class="btn btn-light" data-close="workWizard" aria-label="{{ __('work_editor.close') }}">×</button></div>
        <ol class="work-steps"><li>{{ __('work_editor.step1') }}</li><li>{{ __('work_editor.step2') }}</li><li>{{ __('work_editor.step3') }}</li></ol>
        <div id="workWizardError" class="alert alert-danger d-none" role="alert"></div>
        <form id="workWizardForm" novalidate>
            <div data-step="1"><div id="workCreateFields" class="row g-3"></div><div class="work-drop mt-3"><label class="btn btn-outline-primary">+ {{ __('work_editor.files') }}<input id="workInitialFiles" type="file" multiple class="visually-hidden" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime,.avi,.mkv,.pdf,.doc,.docx,.txt,audio/*"></label><span id="workInitialCount" class="ms-2"></span></div></div>
            <div data-step="2" hidden><p class="text-muted">{{ __('work_editor.order_hint') }}</p><label class="btn btn-outline-primary mb-3">+ {{ __('work_editor.files') }}<input id="workMoreFiles" type="file" multiple class="visually-hidden" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime,.avi,.mkv,.pdf,.doc,.docx,.txt,audio/*"></label><div id="workDraftFiles"></div></div>
            <div data-step="3" hidden><div id="workSummary" class="mb-4"></div><div id="workPreviewFiles"></div></div>
            <div class="d-flex justify-content-end gap-2 mt-4"><button type="button" id="workPrevious" class="btn btn-light">{{ __('work_editor.back') }}</button><button type="button" id="workNext" class="btn btn-primary">{{ __('work_editor.next') }}</button><button type="submit" id="workPublish" class="btn btn-primary" hidden>{{ __('work_editor.save') }}</button></div>
        </form>
    </dialog>
    <dialog id="workEdit" class="work-dialog work-dialog-small" aria-labelledby="workEditTitle"><div class="work-dialog-heading"><h5 id="workEditTitle">{{ __('work_editor.edit') }}</h5><button type="button" class="btn btn-light" data-close="workEdit" aria-label="{{ __('work_editor.close') }}">×</button></div><form id="workEditForm"><div id="workEditFields" class="row g-3"></div><div id="workEditError" class="alert alert-danger d-none mt-3" role="alert"></div><button class="btn btn-primary mt-3" type="submit">{{ __('work_editor.save') }}</button></form></dialog>
    <dialog id="workViewer" class="work-dialog" aria-labelledby="workViewerTitle"><div class="work-dialog-heading"><h5 id="workViewerTitle">{{ __('work_editor.preview') }}</h5><button type="button" class="btn btn-light" data-close="workViewer" aria-label="{{ __('work_editor.close') }}">×</button></div><div id="workPlayer"></div></dialog>
    <div id="workPageError" class="alert alert-danger d-none" role="alert"></div>
</div>
<script id="workEditorConfig" type="application/json">{!! json_encode($editorConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('assets/js/work-editor.js') }}" defer></script>
