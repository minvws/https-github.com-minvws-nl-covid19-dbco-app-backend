<p>
    Do: {{ $name }}<br>
    Szanowni Państwo,
</p>

<p>
    w dniu {{ toDate($deletedMessageDate) }} o godzinie {{ toTime($deletedMessageDate) }} umieściliśmy dla Państwa wiadomość w skrzynce MijnGGDContact. Wiadomość ta została przez nas anulowana.
    Być może była nieprawidłowa (bądź nieaktualna) lub też nie była przeznaczona dla Państwa. Nie mogą więc już Państwo otworzyć tej wiadomości.
</p>

<p>
    Z poważaniem,
</p>

<p>
    {{ $ggdRegion }}<br>
    {{ $ggdPhoneNumber }}
</p>
