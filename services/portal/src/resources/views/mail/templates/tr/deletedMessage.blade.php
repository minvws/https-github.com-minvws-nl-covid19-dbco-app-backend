<p>
    Sayın {{ $name }},
</p>

<p>
    {{ toDate($deletedMessageDate) }} tarihinde saat {{ toTime($deletedMessageDate) }} civarında MijnGGDContact'a size bir mesaj gönderdik. Bu mesajı geri çektik.
    Muhtemelen içeriği (artık) doğru değildi veya size yönelik değildi. Mesajı görüntülemeniz artık mümkün değildir.
</p>

<p>
    Saygılarla,
</p>

<p>
    {{ $ggdRegion }}<br>
    {{ $ggdPhoneNumber }}
</p>
