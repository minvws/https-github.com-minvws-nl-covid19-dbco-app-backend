<p>Sayın {{ $name }},</p>

{{ $customTextPlaceholder }}
@if ($additionalAdvice)
    <p>
    {!! $additionalAdvice !!}
    </p>
@endif

<p>Koronavirüse yakalanmış olduğunuzda hayatın kuralları hakkında daha fazla bilgiyi <a href="https://mijnvraagovercorona.nl/">https://mijnvraagovercorona.nl/</a> adresinde buabilirsiniz.</p>

<p>Geçmiş olsun diyor en kısa zamanda iyileşmenizi diliyoruz.</p>

<p>Saygılarımıza,</p>

<p>Enfeksiyon Hastalıklarıyla Mücadele Bölümü<br>
{{ $ggdRegion }}<br>
{{ $ggdPhoneNumber }}</p>

<p>Bizimle iletişime geçtiğinizde dosya numaranızı hazır bulundurun: <strong>{{ $caseNumber }}</strong></p>

<p>Bu e-posta gizli bilgiler içermektedir. Alıcısı siz değil misiniz ve e-postayı yanlışlıkla mı aldınız? Bunu bize iletmeniz rica olunur. Ayrıca, e-postayı silmenizi ve içeriği görüntülememenizi veya paylaşmamanızı rica ediyoruz.</p>
