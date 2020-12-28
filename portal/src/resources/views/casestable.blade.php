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
        <tr role="button" class="custom-link clickable-row" data-href="{{ $case->editCommand ?? '' }}">
            <th scope="row">{{ Str::limit($case->name, 30, '...') }}</th>
            <td>{{ Str::limit($case->caseId, 30, '...') }}</td>
            <td>{{ $case->dateOfSymptomOnset != NULL ? $case->dateOfSymptomOnset->format('l j M') : '' }}</td>
            <td>
                                        <span class="icon text-center">
                                            <img src="{{ asset("/images/status_".$case->caseStatus().".svg") }}">
                                        </span>
                <span>{{ \App\Models\CovidCase::statusLabel($case->caseStatus()) }}</span>
            </td>
            <td>{{ $case->updatedAt->diffForHumans() }}</td>
            @if ($isPlanner)
                <td>{{ $case->assignedName ?? '' }}</td>
            @endif
        </tr>
    @endforeach
</table>
<!-- End of table component -->
{{ $cases->links() }}
