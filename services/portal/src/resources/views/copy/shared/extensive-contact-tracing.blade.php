<?php

declare(strict_types=1);

use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV2Up;

/** @var ExtensiveContactTracingV2Up $extensiveContactTracing */
?>
<tr>
    <td>Type BCO</td>
    <td>
        @if ( $extensiveContactTracing->receivesExtensiveContactTracing === \MinVWS\DBCO\Enum\Models\BCOType::extensive() )
            Uitgebreid<br>
            @if ( $extensiveContactTracing instanceof \App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV2 )
                {{ $extensiveContactTracing->notes ? 'Toelichting: ' . $extensiveContactTracing->notes : '' }}
            @endif
        @elseif ( $extensiveContactTracing->receivesExtensiveContactTracing === \MinVWS\DBCO\Enum\Models\BCOType::standard() )
            Standaard
        @elseif ( $extensiveContactTracing->receivesExtensiveContactTracing === \MinVWS\DBCO\Enum\Models\BCOType::other() )
            Anders<br>
            {{ $extensiveContactTracing->otherDescription }}
        @else
            Onbekend
        @endif
    </td>
</tr>
