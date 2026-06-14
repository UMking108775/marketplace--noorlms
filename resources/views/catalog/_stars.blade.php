@php $r = (float) ($rating ?? 0); $full = floor($r); $half = ($r - $full) >= 0.5; @endphp
<span class="inline-flex items-center gap-0.5 text-amber-400 text-xs">
    @for ($i = 1; $i <= 5; $i++)
        @if ($i <= $full)
            <i class="fas fa-star"></i>
        @elseif ($i == $full + 1 && $half)
            <i class="fas fa-star-half-stroke"></i>
        @else
            <i class="far fa-star text-gray-300"></i>
        @endif
    @endfor
</span>
