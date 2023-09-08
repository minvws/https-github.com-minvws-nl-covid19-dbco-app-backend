<p>
@if ($hasSymptoms)
    Koronavirüse yakalandınız. Virüsü başkalarına bulaştırmanızı önlemek için GGD sizden şu tavsiyelere uymanızı istemektedir:
@else
    Koronavirüse yakalandınız. Kendinizi hasta hissetmeseniz bile, virüsü kolayca başkalarına bulaştırabilirsiniz. Bu nedenle GGD, şu tavsiyelere uymanızı istemektedir:
@endif
</p>

<ul>
    @if ($hasSymptoms)
        @if ($contagiousPeriodEndDate == $longestIsolationPeriodEndDate)
            <li><strong>{{ @toDate($longestIsolationPeriodEndDate) }} tarihine kadar evde kalın ve ziyaretçi kabul etmeyin.</strong>
                {{ @toDate($longestIsolationPeriodEndDate, '+1') }} tarihinde tekrar dışarıya çıkabilirsiniz.</li>
        @else
            <li><strong>{{ @toDate($contagiousPeriodEndDate) }} tarihine kadar evde kalın ve ziyaretçi kabul etmeyin.</strong>
                En az 24 saat boyunca şikâyetiniz olmadıktan sonra ancak dışarı çıkın. Şikâyetler ateş, öksürük, boğaz ağrısı ve burun akıntısıdır.
                Şikâyetler geçmediğinde {{ @toDate($longestIsolationPeriodEndDate, '+1') }} tarihinde dışarı çıkabilirsiniz.</li>
        @endif
    @else
        <li><strong>{{ @toDate($contagiousPeriodEndDate) }} tarihine kadar evde kalın ve ziyaretçi kabul etmeyin.</strong></li>
    @endif
    @if ($hasHouseMates)
        <li>Ev arkadaşlarınızla temastan kaçının. Özel bir odada kalıp orada yatın. Ev arkadaşlarınızın bu odaya girmesine izin vermeyin.</li>
    @endif
    <li>{{ @toDate($longestIsolationPeriodEndDate) }} tarihine kadar hassas kişilerden uzak durun.</li>
    @if (!$receivesExtensiveContactTracing)
        <li>{{ @toDate($contagiousPeriodStartDate) }} tarihi ile izolasyona girdiğiniz tarih arasında temasta olmuş olduğunuz kişilere uyarıda bulunun. Bu kişileri <a href="https://mijnvraagovercorona.nl/">mijnvraagovercorona.nl</a> adresine yönlendirin.</li>
    @endif
    @if ($isEduDaycare)
        <li>Size virüs bulaşmış olduğu konusunu kreşe, ilkokula veya ortaokula bildirin.</li>
    @endif
    @if ($isHealthProfessional)
        <li>Sağlık sektöründe çalışıyorsunuz. Çalışıp çalışamayacağınız konusunu, şirket doktorunuza veya işvereninize danışın.</li>
    @endif
    <li>Ellerinizi düzenli olarak su ve sabun ile yıkayın, dirseğinizin içine doğru öksürürn ve hapşırın.</li>
</ul>
@if (!$hasSymptoms)
    <p><strong>Şu an şikâyetiniz yok. Sonradan yine de şikâyetiniz oluşmaya başlıyor mu?</strong></p>
    <ul>
        <li>Bu durumda evde kalın ve şikâyetlerin başlamasının üzerinden 5 gün geçtikten ve şikâyetler en az 24 saat boyunca geçmiş olduktan sonra ancak tekrar dışarı çıkın.
            Şikâyetler ateş, öksürük, boğaz ağrısı ve burun akıntısıdır.
            5 gün sonrasında hâlâ şikâyetleriniz var mıdır? O halde en az 24 saat boyunca artık şikâyetiniz olmayana kadar ve şikâyetlerinizin başlamasından en fazla 10 gün sonrasına kadar evde kalın.</li>
        <li>Şikâyetleriniz başladıktan sonra 10 güne kadar hassas kişilerden uzak durun.</li>
    </ul>
@endif
