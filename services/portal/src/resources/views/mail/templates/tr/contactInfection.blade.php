<p>Sayın {{ $name }},</p>

{{ $customTextPlaceholder }}
@if ($additionalAdvice)
    <p>
    {!! $additionalAdvice !!}
    </p>
@endif

<p>Saygılarımıza,</p>

<p>Enfeksiyon Hastalıklarıyla Mücadele Bölümü<br>
{{ $ggdRegion }}<br>
{{ $ggdPhoneNumber }}</p>

<p>Bu e-posta gizli bilgiler içermektedir. Alıcısı siz değil misiniz ve e-postayı yanlışlıkla mı aldınız? Bunu bize iletmeniz rica olunur. Ayrıca, e-postayı silmenizi ve içeriği görüntülememenizi veya paylaşmamanızı rica ediyoruz.</p>
