
        @if ($lastPage > 1)
                        <nav aria-label="Page navigation" class="text-center">
                            <ul class="pagination">
                                <li class="page-item">
                                    <a role="button" class="page-link{{ !request()->has('page') || request()->get('page') == 1 ? ' d-none' : '' }}" onclick="event.preventDefault(); window.location.replace('{{ !empty($entity) ? route(\Request::route()->getName(), ['entity' => $entity]) . (request()->has('type') ? (request()->has('page') && request()->get('page') != '1' ? '?type = ' . request()->get('type') . '&page=' . (request()->get('page') - 1) : '') : (request()->has('page') && request()->get('page') != '1' ? '?page=' . (request()->get('page') - 1) : '')) : route(\Request::route()->getName()) . (request()->has('type') ? (request()->has('page') && request()->get('page') != '1' ? '?type=' . request()->get('type') . '&page=' . request()->get('page') - 1 : '') : (request()->has('page') && request()->get('page') != '1' ? '?page=' . request()->get('page') - 1 : '')) }}');">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </a>
                                </li>
            @if ($lastPage > 5)
                @if (!request()->has('page') || request()->get('page') == 1)
                    @for ($i = (request()->has('page') ? request()->get('page') : 1); $i <= (request()->has('page') ? request()->get('page') + 1 : 2); $i++)
                                <li class="page-item"><a class="page-link{{ request()->get('page') == $i ? ' active disabled' : ($i == 1  && !request()->has('page') ? ' active disabled' : '') }}" href="{{ request()->has('type') ? '?type=' . request()->get('type') . '&page=' . $i : '?page=' . $i }}">{{ $i }}</a></li>
                    @endfor
                                <li class="page-item"><i class="bi bi-three-dots mx-2 fs-2 align-middle text-muted"></i></li>
                                <li class="page-item"><a class="page-link" href="?page={{ $lastPage }}">{{ $lastPage }}</a></li>
                @else
                                <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                                <li class="page-item"><i class="bi bi-three-dots{{ request()->get('page') > 2 ? '' : ' d-none' }} mx-2 fs-2 align-middle text-muted"></i></li>
                    @for ($i = (request()->has('page') ? (request()->get('page') >= $lastPage - 2 ? $lastPage - 2 : request()->get('page')) : 1); $i <= (request()->has('page') ? (request()->get('page') == $lastPage ? $lastPage : request()->get('page') + 1) : 2); $i++)
                                <li class="page-item"><a class="page-link{{ request()->get('page') == $i ? ' active disabled' : ($i == 1  && !request()->has('page') ? ' active disabled' : '') }}" href="{{ request()->has('type') ? '?type=' . request()->get('type') . '&page=' . $i : '?page=' . $i }}">{{ $i }}</a></li>
                    @endfor
                                <li class="page-item{{ request()->get('page') == $lastPage ? ' d-none' : (request()->get('page') >= $lastPage - 2 ? ' d-none' : '') }}"><i class="bi bi-three-dots mx-2 fs-2 align-middle text-muted"></i></li>
                                <li class="page-item{{ request()->get('page') == $lastPage ? ' d-none' : (request()->get('page') >= $lastPage - 1 ? ' d-none' : '') }}"><a class="page-link" href="?page={{ $lastPage }}">{{ $lastPage }}</a></li>
                @endif
            @else
                @for ($i = 1; $i <= $lastPage; $i++)
                                <li class="page-item"><a class="page-link{{ request()->get('page') == $i ? ' active disabled' : ($i == 1  && !request()->has('page') ? ' active disabled' : '') }}" href="{{ request()->has('type') ? '?type=' . request()->get('type') . '&page=' . $i : '?page=' . $i }}">{{ $i }}</a></li>
                @endfor
            @endif
                                <li class="page-item">
                                    <a role="button" class="page-link{{ request()->get('page') == $lastPage ? ' d-none' : '' }}" onclick="event.preventDefault(); window.location.replace('{{ request()->has('type') ? (!empty($entity) ? route(\Request::route()->getName(), ['entity' => $entity]) . '?type=' . request()->get('type') . '&page=' . (request()->has('page') ? request()->get('page') + 1 : request()->get('page') + 2) : route(\Request::route()->getName()) . '?type=' . request()->get('type') . '&page=' . (request()->has('page') ? request()->get('page') + 1 : request()->get('page') + 2)) : (!empty($entity) ? route(\Request::route()->getName(), ['entity' => $entity]) . '?page=' . (request()->has('page') ? request()->get('page') + 1 : request()->get('page') + 2) : route(\Request::route()->getName()) . '?page=' . (request()->has('page') ? request()->get('page') + 1 : request()->get('page') + 2)) }}');">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
        @endif
