<p>
    Dear {{ $name }},
</p>

<p>
    We sent you a message in MijnGGDContact on {{ toDate($deletedMessageDate) }} at {{ toTime($deletedMessageDate) }}. We have now withdrawn this message.
    It may be that the content of this message is not or no longer correct or that the message was not intended for you. You can no longer view the message.
</p>

<p>
    Kinds regards,
</p>

<p>
    {{ $ggdRegion }}<br>
    {{ $ggdPhoneNumber }}
</p>
