<table class="table  table-rounded  table-hover  table-ggd">
    <colgroup>
        @for ($i = 0; $i < ($isPlanner ? 6 : 5); $i++)
            <col class="w-{{ $isPlanner ? 16 : 20 }}">
        @endfor
    </colgroup>
    <thead>
    <tr>
        <th scope="col">Naam</th>
        <th scope="col">Casenr.</th>
        <th scope="col">Eerste ziektedag</th>
        <th scope="col">Status</th>
        <th scope="col">Laatst bewerkt</th>
        @if ($isPlanner)
            <th scope="col">Toegewezen aan</th>
        @endif
    </tr>
    </thead>
    <tbody>
    @foreach($cases as $case)
        <tr role="button" class="custom-link" data-href="{{ $case->editCommand ?? '#' }}">
            <th class="clickable-cell" scope="row">{{ Str::limit($case->name, 30, '...') }}</th>
            <td class="clickable-cell">{{ Str::limit($case->caseId, 30, '...') }}</td>
            <td class="clickable-cell">{{ $case->dateOfSymptomOnset != NULL ? $case->dateOfSymptomOnset->format('l j M') : '' }}</td>
            <td class="clickable-cell">
                                        <span class="icon text-center">
                                            <img src="{{ asset("/images/status_".$case->caseStatus().".svg") }}">
                                        </span>
                <span>{{ \App\Models\CovidCase::statusLabel($case->caseStatus()) }}</span>
            </td>
            <td class="clickable-cell">{{ $case->updatedAt->diffForHumans() }}</td>
            @if ($isPlanner)
                <td>
                    <div class="assignee dropdown" data-case="{{ $case->uuid }}">
                        <a type="link" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                            {{ $case->assignedName ?? '' }}
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdown-users">
                            <form class="px-4 py-2" autocomplete="off">
                                <input type="search" class="form-control search-user" placeholder="Zoeken.." autofocus="autofocus">
                            </form>
                        </div>
                    </div>
                </td>
            @endif
        </tr>
    @endforeach
</table>
<!-- End of table component -->
{{ $cases->links() }}
