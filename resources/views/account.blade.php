@extends('admin.layout')

@php
    $pageKey = 'account';
    $title = 'Mon compte';
    $current = $user ?? auth()->user();
    $avatar = $current?->avatar_url;
    if (!empty($avatar) && !str_starts_with($avatar, 'http') && !str_starts_with($avatar, '/storage/') && !str_starts_with($avatar, 'storage/')) {
        $avatar = asset('storage/' . ltrim($avatar, '/'));
    } elseif (!empty($avatar) && str_starts_with($avatar, '/storage/')) {
        $avatar = 'https://boongo7.com' . $avatar;
    } elseif (!empty($avatar) && str_starts_with($avatar, 'storage/')) {
        $avatar = 'https://boongo7.com/' . $avatar;
    }
@endphp

@section('content')
    <link rel="stylesheet" href="{{ asset('assets/addons/custom/cropper/css/cropper.min.css') }}" />

    <div class="page-header mb-3">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="mb-1">Mon compte</h5>
                <p class="fs-12 text-muted mb-0">Parametres du compte administrateur.</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('account') }}">
                @csrf
                <div class="row g-3 align-items-start">
                    <div class="col-lg-4">
                        <label class="form-label d-block">Photo de profil</label>
                        <div class="border rounded p-3 text-center">
                            <img id="accountAvatarPreview" src="{{ $avatar ?: asset('assets/img/user.png') }}" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;" alt="avatar" />
                            <input type="file" id="accountAvatarInput" class="form-control" accept="image/*" />
                            <input type="hidden" name="avatar_crop" id="avatar_crop" />
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Prenom</label>
                                <input type="text" class="form-control" name="firstname" value="{{ old('firstname', $current->firstname) }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" class="form-control" name="lastname" value="{{ old('lastname', $current->lastname) }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $current->email) }}" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ville</label>
                                <input type="text" class="form-control" name="city" value="{{ old('city', $current->city) }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" name="password" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmer mot de passe</label>
                                <input type="password" class="form-control" name="password_confirmation" />
                            </div>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger mt-3 mb-0">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-3"><button type="submit" class="btn btn-primary">Enregistrer</button></div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="accountCropModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Recadrer l image</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <img id="accountCropImage" src="" style="max-width:100%;" alt="crop" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="applyAccountCrop">Appliquer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/addons/custom/cropper/js/cropper.min.js') }}"></script>
    <script>
        (function () {
            const input = document.getElementById('accountAvatarInput');
            const image = document.getElementById('accountCropImage');
            const preview = document.getElementById('accountAvatarPreview');
            const hidden = document.getElementById('avatar_crop');
            const applyBtn = document.getElementById('applyAccountCrop');
            const modalEl = document.getElementById('accountCropModal');
            let cropper = null;
            const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

            if (input && modal && image) {
                input.addEventListener('change', function () {
                    const file = input.files && input.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        image.src = e.target.result;
                        modal.show();
                    };
                    reader.readAsDataURL(file);
                });

                modalEl.addEventListener('shown.bs.modal', function () {
                    cropper = new Cropper(image, { aspectRatio: 1, viewMode: 1, autoCropArea: 1 });
                });

                modalEl.addEventListener('hidden.bs.modal', function () {
                    if (cropper) { cropper.destroy(); cropper = null; }
                });

                applyBtn.addEventListener('click', function () {
                    if (!cropper) return;
                    const canvas = cropper.getCroppedCanvas({ width: 320, height: 320 });
                    const dataUrl = canvas.toDataURL('image/png');
                    preview.src = dataUrl;
                    hidden.value = dataUrl;
                    modal.hide();
                });
            }
        })();
    </script>
@endsection
