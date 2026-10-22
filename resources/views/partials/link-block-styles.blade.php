.lp-block {
    color: var(--lp-text);
    background-color: color-mix(in srgb, var(--lp-bg) var(--lp-bg-o), transparent);
    border: var(--lp-bw) solid color-mix(in srgb, var(--lp-border) var(--lp-border-o), transparent);
    border-radius: var(--lp-radius);
    backdrop-filter: blur(var(--lp-blur));
    -webkit-backdrop-filter: blur(var(--lp-blur));
    overflow: hidden;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, color 0.3s ease;
}
.lp-block .lp-sub { color: var(--lp-sub); transition: color 0.3s ease; }
:is(a, button).lp-block:hover {
    color: var(--lp-hover-text);
    background-color: color-mix(in srgb, var(--lp-hover-bg) var(--lp-hover-bg-o), transparent);
    border-color: var(--lp-hover-border);
}
:is(a, button).lp-hover-lift:hover { transform: translateY(-3px); box-shadow: 0 12px 30px -10px var(--lp-glow); }
:is(a, button).lp-hover-scale:hover { transform: scale(1.02); box-shadow: 0 12px 30px -12px var(--lp-glow); }
:is(a, button).lp-hover-glow:hover { box-shadow: 0 0 22px var(--lp-glow), inset 0 0 14px color-mix(in srgb, var(--lp-glow) 35%, transparent); }
.lp-block img.lp-thumb { transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
a.lp-block:hover img.lp-thumb { transform: scale(1.05); }
